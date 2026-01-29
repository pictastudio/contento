<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use PictaStudio\Contento\Http\Requests\SaveSettingRequest;
use PictaStudio\Contento\Http\Resources\SettingResource;
use PictaStudio\Contento\Models\Setting;

class SettingController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Setting::class);

        $settings = Setting::all();

        return SettingResource::collection($settings);
    }

    public function store(SaveSettingRequest $request): JsonResource
    {
        Gate::authorize('create', Setting::class);

        $setting = Setting::updateOrCreate(
            [
                'group' => $request->group,
                'name' => $request->name,
            ],
            ['value' => $request->value]
        );

        return new SettingResource($setting);
    }

    public function destroy(Setting $setting): Response
    {
        Gate::authorize('delete', $setting);

        $setting->delete();

        return response()->noContent();
    }
}
