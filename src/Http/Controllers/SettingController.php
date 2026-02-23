<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use PictaStudio\Contento\Http\Requests\StoreSettingRequest;
use PictaStudio\Contento\Http\Resources\SettingResource;
use PictaStudio\Contento\Models\Setting;

class SettingController extends BaseController
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorizeIfConfigured('viewAny', Setting::class);

        $settings = Setting::all();

        return SettingResource::collection($settings);
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

    public function destroy(Setting $setting): Response
    {
        $this->authorizeIfConfigured('delete', $setting);

        $setting->delete();

        return response()->noContent();
    }
}
