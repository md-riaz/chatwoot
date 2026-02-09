#!/bin/bash
cd /mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui
pnpm run check 2>&1 | head -150
