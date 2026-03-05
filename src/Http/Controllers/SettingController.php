<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use PictaStudio\Contento\Http\Requests\{BulkUpdateSettingRequest, IndexSettingRequest, StoreSettingRequest};
use PictaStudio\Contento\Http\Resources\SettingResource;
use PictaStudio\Contento\Models\Setting;

class SettingController extends BaseController
{
    public function index(IndexSettingRequest $request): AnonymousResourceCollection
    {
        $this->authorizeIfConfigured('viewAny', Setting::class);

        $validated = $request->validated();
        $settings = Setting::query();

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
        $this->authorizeIfConfigured('create', Setting::class);

        $setting = Setting::updateOrCreate(
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
                ->map(function (array $payload): Setting {
                    if (array_key_exists('id', $payload) && $payload['id'] !== null) {
                        $setting = Setting::query()->findOrFail($payload['id']);

                        $this->authorizeIfConfigured('update', $setting);

                        $setting->update([
                            'value' => $payload['value'],
                        ]);

                        return $setting->refresh();
                    }

                    $existingSetting = Setting::query()
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

                    $this->authorizeIfConfigured('create', Setting::class);

                    return Setting::query()->create([
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
