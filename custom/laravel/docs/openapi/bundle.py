#!/usr/bin/env python3
"""
OpenAPI Bundle Script
Merges all path files into a single bundled OpenAPI specification
"""

import yaml
import os
from pathlib import Path

# Base directory
BASE_DIR = Path(__file__).parent

# Load main OpenAPI file
with open(BASE_DIR / 'openapi.yaml', 'r') as f:
    spec = yaml.safe_load(f)

# Remove the $ref for paths since we'll merge them directly
if 'paths' in spec and isinstance(spec['paths'], dict) and '$ref' in spec['paths']:
    spec['paths'] = {}

# Path files to merge
path_files = [
    'authentication.yaml',
    'profile.yaml',
    'conversations.yaml',
    'messages.yaml',
    'contacts.yaml',
    'inboxes.yaml',
    'teams_labels_webhooks.yaml',
    'reports_sla.yaml',
    'notifications_canned_campaigns.yaml',
    'widget.yaml',
    'platform.yaml',
    'integrations.yaml',
    'super_admin.yaml',
    'channels.yaml',
    'advanced_features.yaml',
]

# Merge all path files
for path_file in path_files:
    file_path = BASE_DIR / 'paths' / path_file
    if file_path.exists():
        print(f"Merging {path_file}...")
        with open(file_path, 'r') as f:
            path_content = yaml.safe_load(f)
            if path_content and 'paths' in path_content:
                spec['paths'].update(path_content['paths'])
    else:
        print(f"Warning: {path_file} not found, skipping...")

# Write bundled file
output_file = BASE_DIR / 'openapi.bundled.yaml'
with open(output_file, 'w') as f:
    yaml.dump(spec, f, default_flow_style=False, sort_keys=False, allow_unicode=True)

print(f"\n✅ Bundle created successfully: {output_file}")
print(f"Total endpoints: {len(spec.get('paths', {}))}")
