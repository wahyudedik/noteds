# Plugin API Guide

## Overview
- Plugins extend the system with web modules or Android integrations.
- Each plugin ships a `plugin.json` manifest describing metadata, dependencies and permissions.
- Admins can upload, install, configure, activate/deactivate and rollback versions.

## Manifest
Required fields:
- name: string
- slug: string (unique)
- version: semver (e.g., 1.2.0)
- type: web | android | hybrid

Optional:
- author, android_package_name, description
- permissions: array of capabilities requested
- dependencies: { slug: versionConstraint }

Example:
```json
{
  "name": "Content Insights",
  "slug": "content-insights",
  "version": "1.0.0",
  "type": "web",
  "author": "Example Dev",
  "description": "Insights and utilities for content moderation and analytics",
  "permissions": ["routes", "notifications"],
  "dependencies": { "core-analytics": "^1.0" }
}
```

## Lifecycle
1. Upload ZIP via Admin UI
2. Install: validates manifest, checks dependencies, extracts to `storage/app/plugins/{slug}/{version}`
3. Configure: edit permissions/manifest via API
4. Activate: marks plugin enabled
5. Rollback: switch to a previous version if needed

## Admin Endpoints
- POST /admin/plugins/upload (file: archive.zip)
- POST /admin/plugins/install { archive_path }
- POST /admin/plugins/{id}/activate
- POST /admin/plugins/{id}/deactivate
- GET  /admin/plugins
- GET  /admin/plugins/{id}
- POST /admin/plugins/{id}/rollback { to_version }
- PUT  /admin/plugins/{id}/config { manifest, permissions }

## Best Practices
- Use semver versions and constraints (`^1.0`, `~1.2`)
- Keep manifest minimal and explicit; avoid unnecessary permissions
- Validate inputs and sanitize any user-provided data
- For web routes, namespace under `/plugins/{slug}` to avoid conflicts
- Log operations and measure performance using provided APIs

## Example Code (Server)
Install from archive:
```php
$plugin = app(\App\Services\PluginManager::class)->installFromArchive($archivePath);
app(\App\Services\PluginManager::class)->activate($plugin);
```

Measure operation:
```php
\App\Helpers\PluginMetric::measure($plugin, 'sync-data', function () {
    // your operation
});
```

## Security
- Manifest validation enforces required fields and allowed types
- Dependency checks ensure compatible versions
- Activation requires successful validation; permissions are reviewed
- Logs capture errors and performance for monitoring
