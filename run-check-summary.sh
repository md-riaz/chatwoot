#!/bin/bash
cd /mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui
echo "Running svelte-check..."
pnpm run check 2>&1 | tee /tmp/check-output.txt
echo ""
echo "=== SUMMARY ==="
grep -E "(Error:|Warn:|found [0-9]+ error)" /tmp/check-output.txt | tail -20
