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
                    'value' => $setting['value'] ?? null,
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
        $defaultSettings = config('contento.settings.default_records', []);

        return is_array($defaultSettings) ? $defaultSettings : [];
    }
};
