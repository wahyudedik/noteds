<?php

namespace App\Helpers;

use App\Models\Plugin;
use App\Services\PluginManager;

class PluginMetric
{
    public static function measure(Plugin $plugin, string $operation, callable $fn): mixed
    {
        $start = microtime(true);
        try {
            $result = $fn();
            $duration = (microtime(true) - $start) * 1000.0;
            app(PluginManager::class)->log($plugin, 'info', $operation . ' succeeded', [], $duration);
            return $result;
        } catch (\Throwable $e) {
            $duration = (microtime(true) - $start) * 1000.0;
            app(PluginManager::class)->log($plugin, 'error', $operation . ' failed: ' . $e->getMessage(), ['exception' => get_class($e)], $duration);
            throw $e;
        }
    }
}

