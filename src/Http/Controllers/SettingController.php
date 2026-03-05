<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use PictaStudio\Contento\Http\Requests\{BulkUpdateSettingRequest, IndexSettingRequest, StoreSettingRequest};
use PictaStudio\Contento\Http\Resources\SettingResource;
use PictaStudio\Contento\Models\Setting;

use function PictaStudio\Contento\Helpers\Functions\{query, resolve_model};

class SettingController extends BaseController
{
    public function index(IndexSettingRequest $request): AnonymousResourceCollection
    {
        $this->authorizeIfConfigured('viewAny', resolve_model('setting'));

        $validated = $request->validated();
        $settings = query('setting');

        $this->applyArrayFilters($settings, $validated, [
            'id' => 'id',
        ]);
        $this->applyExactFilters($settings, $validated, [
            'group' => 'group',
            'name' => 'name',
        ]);
        $this->applyDateRangeFilters($settings, $validated, [
            'created_at' => ['start' => 'created_at_start', 'end' => 'created_at_end'],
            'updated_at' => ['start' => 'updated_at_start', 'end' => 'updated_at_end'],
        ]);
        $this->applySorting($settings, $validated);

        return SettingResource::collection(
            $settings->paginate($this->resolvePerPage($validated))
                ->appends($request->query())
        );
    }

    public function store(StoreSettingRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('create', resolve_model('setting'));

        $setting = query('setting')->updateOrCreate(
            [
                'group' => $request->group,
                'name' => $request->name,
            ],
            ['value' => $request->value]
        );

        return SettingResource::make($setting);
    }

    public function bulkUpdate(BulkUpdateSettingRequest $request): AnonymousResourceCollection
    {
        $settings = DB::transaction(function () use ($request) {
            return collect($request->validated('settings'))
                ->map(function (array $payload) {
                    if (array_key_exists('id', $payload) && $payload['id'] !== null) {
                        $setting = query('setting')->findOrFail($payload['id']);

                        $this->authorizeIfConfigured('update', $setting);

                        $setting->update([
                            'value' => $payload['value'],
                        ]);

                        return $setting->refresh();
                    }

                    $existingSetting = query('setting')
                        ->where('group', $payload['group'])
                        ->where('name', $payload['name'])
                        ->first();

                    if ($existingSetting instanceof Setting) {
                        $this->authorizeIfConfigured('update', $existingSetting);

                        $existingSetting->update([
                            'value' => $payload['value'],
                        ]);

                        return $existingSetting->refresh();
                    }

                    $this->authorizeIfConfigured('create', resolve_model('setting'));

                    return query('setting')->create([
                        'group' => $payload['group'],
                        'name' => $payload['name'],
                        'value' => $payload['value'],
                    ]);
                });
        });

        return SettingResource::collection($settings);
    }

    public function destroy(Setting $setting): Response
    {
        $this->authorizeIfConfigured('delete', $setting);

        $setting->delete();

        return response()->noContent();
    }
}
