#!/usr/bin/env python3
import os
import json

# Views found in controllers
views_from_controllers = [
    '40-shared/templates/create',
    '40-shared/premium/insights',
    '40-shared/support-tickets/create',
    'workspaces.create',
    '40-shared/webhooks/create',
    '40-shared/simulators/index',
    '40-shared/setup-username/create',
    'studio.orders.create',
    '40-shared/series/create',
    '40-shared/mynoteds/ask',
    'admin.points-pricing.create',
    '40-shared/ecosystem/index',
    '40-shared/ecosystem/audio',
    '40-shared/ecosystem/code',
    '40-shared/ecosystem/graphics',
    '40-shared/ecosystem/photos',
    '40-shared/ecosystem/themes',
    '40-shared/ecosystem/videos',
    'admin.faqs.create',
    'admin.exchange-rates.create',
    'contests.create',
    'contact.index',
    'buyer.collections.create',
    'admin.contests.create',
    'admin.commission-tiers.create',
    'admin.cms-pages.create',
    'admin.certifications.create',
    'admin.badges.create',
    '00-auth.auth.forgot-password',
    '00-auth.auth.verify-email',
    '00-auth.auth.confirm-password',
    '00-auth.auth.login'
]

# Get all existing blade files
base_path = 'd:/PROJECT/LARAVEL/noteds/resources/views'
existing_views = set()

for root, dirs, files in os.walk(base_path):
    for file in files:
        if file.endswith('.blade.php'):
            rel_path = os.path.relpath(os.path.join(root, file), base_path)
            rel_path = rel_path.replace(os.sep, '/').replace('.blade.php', '')
            existing_views.add(rel_path)

# Find missing views
missing = []
for view in views_from_controllers:
    converted_path = view.replace('.', '/')
    if converted_path not in existing_views:
        missing.append(view)

missing.sort()
result = {
    'missing_views': missing,
    'total_missing': len(missing)
}

print(json.dumps(result, indent=2))
