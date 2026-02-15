<?php

namespace App\Services;

use App\Models\Plugin;
use App\Models\PluginLog;
use App\Models\PluginVersion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PluginManager
{
    public function validateManifest(array $manifest): array
    {
        $errors = [];
        $required = ['name', 'slug', 'version', 'type'];
        foreach ($required as $key) {
            if (!array_key_exists($key, $manifest) || empty($manifest[$key])) {
                $errors[] = "Missing required manifest key: {$key}";
            }
        }
        if (isset($manifest['permissions']) && !is_array($manifest['permissions'])) {
            $errors[] = 'permissions must be an array';
        }
        if (isset($manifest['dependencies']) && !is_array($manifest['dependencies'])) {
            $errors[] = 'dependencies must be an array';
        }
        if (isset($manifest['type']) && !in_array($manifest['type'], ['web', 'android', 'hybrid'])) {
            $errors[] = 'type must be one of: web, android, hybrid';
        }
        return $errors;
    }

    public function checkDependencies(array $manifest): array
    {
        $issues = [];
        $deps = $manifest['dependencies'] ?? [];
        foreach ($deps as $depSlug => $constraint) {
            $dep = Plugin::where('slug', $depSlug)->first();
            if (!$dep) {
                $issues[] = "Dependency missing: {$depSlug}";
                continue;
            }
            if (!$this->versionSatisfies($dep->version, $constraint)) {
                $issues[] = "Dependency version mismatch: {$depSlug} requires {$constraint}, found {$dep->version}";
            }
        }
        return $issues;
    }

    protected function versionSatisfies(string $version, string $constraint): bool
    {
        if (str_starts_with($constraint, '^')) {
            $major = explode('.', ltrim($constraint, '^'))[0] ?? '0';
            return str_starts_with($version, $major . '.');
        }
        if (str_starts_with($constraint, '~')) {
            $parts = explode('.', ltrim($constraint, '~'));
            $prefix = $parts[0] . '.' . ($parts[1] ?? '0') . '.';
            return str_starts_with($version, $prefix);
        }
        return $version === $constraint;
    }

    public function upload(UploadedFile $file): string
    {
        $path = $file->store('plugins/uploads');
        return $path;
    }

    public function installFromArchive(string $archivePath): Plugin
    {
        // $content = Storage::get($archivePath); // Removed to avoid memory issues with large files
        $tmpDir = storage_path('app/plugins/tmp/' . Str::uuid());
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }

        $zip = new \ZipArchive();
        $fullPath = storage_path('app/' . $archivePath);
        if ($zip->open($fullPath) !== true) {
            throw new \RuntimeException('Failed to open plugin archive');
        }
        $zip->extractTo($tmpDir);
        $zip->close();

        $manifestFile = $tmpDir . '/plugin.json';
        if (!file_exists($manifestFile)) {
            throw new \RuntimeException('Manifest plugin.json not found in archive');
        }
        $manifest = json_decode(file_get_contents($manifestFile), true) ?? [];
        $errors = $this->validateManifest($manifest);
        if (!empty($errors)) {
            throw new \InvalidArgumentException('Manifest invalid: ' . implode('; ', $errors));
        }
        $depIssues = $this->checkDependencies($manifest);
        if (!empty($depIssues)) {
            throw new \InvalidArgumentException('Dependency issues: ' . implode('; ', $depIssues));
        }

        return DB::transaction(function () use ($manifest, $archivePath, $tmpDir) {
            $plugin = Plugin::updateOrCreate(
                ['slug' => $manifest['slug']],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $manifest['name'],
                    'version' => $manifest['version'],
                    'type' => $manifest['type'],
                    'author' => $manifest['author'] ?? null,
                    'android_package_name' => $manifest['android_package_name'] ?? null,
                    'description' => $manifest['description'] ?? null,
                    'manifest' => $manifest,
                    'dependencies' => $manifest['dependencies'] ?? [],
                    'permissions' => $manifest['permissions'] ?? [],
                    'enabled' => false,
                    'checksum' => hash('sha256', json_encode($manifest)),
                    'storage_path' => null,
                    'installed_at' => now(),
                ]
            );

            $targetDir = storage_path('app/plugins/' . $plugin->slug . '/' . $plugin->version);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0775, true);
            }
            $this->recursiveCopy($tmpDir, $targetDir);
            $plugin->storage_path = str_replace(storage_path('app') . '/', '', $targetDir);
            $plugin->save();

            PluginVersion::create([
                'id' => (string) Str::uuid(),
                'plugin_id' => $plugin->id,
                'version' => $plugin->version,
                'manifest' => $manifest,
                'archive_path' => $archivePath,
                'storage_path' => $plugin->storage_path,
                'checksum' => $plugin->checksum,
                'migration_status' => 'none',
                'installed_at' => now(),
            ]);

            return $plugin;
        });
    }

    public function activate(Plugin $plugin): void
    {
        $plugin->enabled = true;
        $plugin->activated_at = now();
        $plugin->save();
        $this->log($plugin, 'info', 'Plugin activated');
    }

    public function deactivate(Plugin $plugin): void
    {
        $plugin->enabled = false;
        $plugin->save();
        $this->log($plugin, 'info', 'Plugin deactivated');
    }

    public function rollback(Plugin $plugin, string $toVersion): void
    {
        $version = $plugin->versions()->where('version', $toVersion)->first();
        if (!$version) {
            throw new \InvalidArgumentException('Target version not found for rollback');
        }
        $plugin->version = $version->version;
        $plugin->manifest = $version->manifest;
        $plugin->storage_path = $version->storage_path;
        $plugin->checksum = $version->checksum;
        $plugin->save();
        $this->log($plugin, 'warning', 'Plugin rolled back', ['to' => $toVersion]);
    }

    protected function recursiveCopy(string $src, string $dst): void
    {
        $dir = opendir($src);
        @mkdir($dst, 0775, true);
        while (false !== ($file = readdir($dir))) {
            if (($file !== '.') && ($file !== '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recursiveCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public function log(Plugin $plugin, string $level, string $message, array $context = [], ?float $durationMs = null): void
    {
        PluginLog::create([
            'id' => (string) Str::uuid(),
            'plugin_id' => $plugin->id,
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'duration_ms' => $durationMs,
        ]);
    }
}

