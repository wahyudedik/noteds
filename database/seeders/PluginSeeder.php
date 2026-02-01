<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plugin;
use App\Models\PluginVersion;
use App\Models\PluginLog;

class PluginSeeder extends Seeder
{
    public function run(): void
    {
        // Web plugin example
        $webManifest = [
            'name' => 'Stock Signals',
            'slug' => 'stock-signals',
            'version' => '1.0.0',
            'type' => 'web',
            'author' => 'Noteds',
            'description' => 'Signals and alerts for stocks',
            'permissions' => ['routes', 'notifications'],
            'dependencies' => ['core-market' => '^1.0'],
        ];

        $webPlugin = Plugin::firstOrCreate(
            ['slug' => $webManifest['slug']],
            [
                'name' => $webManifest['name'],
                'version' => $webManifest['version'],
                'type' => $webManifest['type'],
                'author' => $webManifest['author'],
                'description' => $webManifest['description'],
                'manifest' => $webManifest,
                'dependencies' => $webManifest['dependencies'],
                'permissions' => $webManifest['permissions'],
                'enabled' => true,
                'installed_at' => now(),
                'activated_at' => now(),
            ]
        );

        $webVersion = PluginVersion::firstOrCreate(
            ['plugin_id' => $webPlugin->id, 'version' => $webManifest['version']],
            [
                'manifest' => $webManifest,
                'archive_path' => null,
                'storage_path' => null,
                'checksum' => hash('sha256', json_encode($webManifest)),
                'migration_status' => 'applied',
                'installed_at' => now(),
            ]
        );

        PluginLog::create([
            'plugin_id' => $webPlugin->id,
            'level' => 'info',
            'message' => 'Seeded web plugin: Stock Signals',
            'context' => ['version' => $webManifest['version']],
            'duration_ms' => 0.500,
        ]);

        // Android plugin example
        $androidManifest = [
            'name' => 'Android Share Bridge',
            'slug' => 'android-share-bridge',
            'version' => '1.0.0',
            'type' => 'android',
            'author' => 'Noteds',
            'android_package_name' => 'com.example.noteds',
            'description' => 'Android bridge for deep links and OAuth',
            'permissions' => ['deep-link', 'oauth'],
            'dependencies' => ['core-mobile-api' => '^1.0'],
        ];

        $androidPlugin = Plugin::firstOrCreate(
            ['slug' => $androidManifest['slug']],
            [
                'name' => $androidManifest['name'],
                'version' => $androidManifest['version'],
                'type' => $androidManifest['type'],
                'author' => $androidManifest['author'],
                'android_package_name' => $androidManifest['android_package_name'],
                'description' => $androidManifest['description'],
                'manifest' => $androidManifest,
                'dependencies' => $androidManifest['dependencies'],
                'permissions' => $androidManifest['permissions'],
                'enabled' => false,
                'installed_at' => now(),
            ]
        );

        PluginVersion::firstOrCreate(
            ['plugin_id' => $androidPlugin->id, 'version' => $androidManifest['version']],
            [
                'manifest' => $androidManifest,
                'archive_path' => null,
                'storage_path' => null,
                'checksum' => hash('sha256', json_encode($androidManifest)),
                'migration_status' => 'none',
                'installed_at' => now(),
            ]
        );

        PluginLog::create([
            'plugin_id' => $androidPlugin->id,
            'level' => 'info',
            'message' => 'Seeded android plugin: Android Share Bridge',
            'context' => ['version' => $androidManifest['version']],
            'duration_ms' => 0.420,
        ]);
    }
}
