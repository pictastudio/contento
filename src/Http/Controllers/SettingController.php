<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Routing\Controller;
use PictaStudio\Contento\Models\Setting;
use PictaStudio\Contento\Http\Resources\SettingResource;
use PictaStudio\Contento\Http\Requests\SaveSettingRequest;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all();
        return SettingResource::collection($settings);
    }

    public function store(SaveSettingRequest $request)
    {
        $setting = Setting::updateOrCreate(
            [
                'group' => $request->group,
                'name' => $request->name,
            ],
            ['value' => $request->value]
        );
        return new SettingResource($setting);
    }

    public function destroy($id)
    {
        $setting = Setting::findOrFail($id);
        $setting->delete();
        return response()->noContent();
    }
}
