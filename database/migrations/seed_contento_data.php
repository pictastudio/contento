<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaultSettings = $this->defaultSettings();
        if ($defaultSettings === []) {
            return;
        }

        $tableName = (string) config('contento.table_names.settings', 'settings');
        $timestamp = now();

        foreach ($defaultSettings as $setting) {
            DB::table($tableName)->updateOrInsert(
                [
                    'group' => $setting['group'],
                    'name' => $setting['name'],
                ],
                [
                    'value' => null,
                    'updated_at' => $timestamp,
                    'created_at' => $timestamp,
                ]
            );
        }
    }

    public function down(): void
    {
        $defaultSettings = $this->defaultSettings();
        if ($defaultSettings === []) {
            return;
        }

        $tableName = (string) config('contento.table_names.settings', 'settings');

        foreach ($defaultSettings as $setting) {
            DB::table($tableName)
                ->where('group', $setting['group'])
                ->where('name', $setting['name'])
                ->delete();
        }
    }

    private function defaultSettings(): array
    {
        return [
            // General settings
            [
                'group' => 'general',
                'name' => 'website_name',
                'value' => config('app.name'),
            ],
            [
                'group' => 'general',
                'name' => 'website_footer',
                'value' => null,
            ],
            [
                'group' => 'general',
                'name' => 'bottom_text',
                'value' => null,
            ],

            // Company settings
            [
                'group' => 'company',
                'name' => 'email',
                'value' => null,
            ],
            [
                'group' => 'company',
                'name' => 'address',
                'value' => null,
            ],
            [
                'group' => 'company',
                'name' => 'city',
                'value' => null,
            ],
            [
                'group' => 'company',
                'name' => 'zip',
                'value' => null,
            ],
            [
                'group' => 'company',
                'name' => 'province',
                'value' => null,
            ],
            [
                'group' => 'company',
                'name' => 'country',
                'value' => null,
            ],
            [
                'group' => 'company',
                'name' => 'vat',
                'value' => null,
            ],
            [
                'group' => 'company',
                'name' => 'pec',
                'value' => null,
            ],
            [
                'group' => 'company',
                'name' => 'fiscal_code',
                'value' => null,
            ],
            [
                'group' => 'company',
                'name' => 'sdi',
                'value' => null,
            ],
            [
                'group' => 'company',
                'name' => 'iban',
                'value' => null,
            ],

            // Social settings
            [
                'group' => 'social',
                'name' => 'facebook',
                'value' => null,
            ],
            [
                'group' => 'social',
                'name' => 'instagram',
                'value' => null,
            ],
            [
                'group' => 'social',
                'name' => 'linkedin',
                'value' => null,
            ],
            [
                'group' => 'social',
                'name' => 'x',
                'value' => null,
            ],
            [
                'group' => 'social',
                'name' => 'youtube',
                'value' => null,
            ],
            [
                'group' => 'social',
                'name' => 'tiktok',
                'value' => null,
            ],

            // Analytics settings
            [
                'group' => 'analytics',
                'name' => 'facebook_pixel_id',
                'value' => null,
            ],
            [
                'group' => 'analytics',
                'name' => 'google_analytics_key',
                'value' => null,
            ],
            [
                'group' => 'analytics',
                'name' => 'google_analytics_snippet',
                'value' => null,
            ],

            // Metadata settings
            [
                'group' => 'metadata',
                'name' => 'title',
                'value' => null,
            ],
            [
                'group' => 'metadata',
                'name' => 'author',
                'value' => null,
            ],
            [
                'group' => 'metadata',
                'name' => 'description',
                'value' => null,
            ],
        ];
    }
};
