#!/bin/bash

# Build SvelteKit Frontend for Laravel Integration
set -e

log() {
    echo -e "\033[0;32m[$(date +'%Y-%m-%d %H:%M:%S')]\033[0m $1"
}

error() {
    echo -e "\033[0;31m[$(date +'%Y-%m-%d %H:%M:%S')] ERROR:\033[0m $1"
    exit 1
}

# Get script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LARAVEL_DIR="$(dirname "$SCRIPT_DIR")"
SVELTE_DIR="$(dirname "$LARAVEL_DIR")/svelte-ui"
BUILD_TARGET="$LARAVEL_DIR/public/app"

log "Building SvelteKit frontend..."

# Check if SvelteKit directory exists
if [ ! -d "$SVELTE_DIR" ]; then
    error "SvelteKit directory not found at: $SVELTE_DIR"
fi

cd "$SVELTE_DIR"

# Check if package.json exists
if [ ! -f "package.json" ]; then
    error "package.json not found in SvelteKit directory"
fi

# Install dependencies (prefer pnpm for better performance)
log "Installing SvelteKit dependencies..."
if command -v pnpm >/dev/null 2>&1; then
    pnpm install
elif command -v npm >/dev/null 2>&1; then
    npm install
else
    error "Neither pnpm nor npm found. Please install Node.js and pnpm/npm"
fi

# Build the SvelteKit app (prefer pnpm)
log "Building SvelteKit application..."
if command -v pnpm >/dev/null 2>&1; then
    pnpm run build
else
    npm run build
fi

# Check if build directory exists
if [ ! -d "build" ]; then
    error "Build failed - build directory not found"
fi

# Create target directory in Laravel public
log "Copying built assets to Laravel public directory..."
mkdir -p "$BUILD_TARGET"

# Copy built files to Laravel public/app
cp -r build/* "$BUILD_TARGET/"

# Set proper permissions
if [ -d "$BUILD_TARGET" ]; then
    chmod -R 755 "$BUILD_TARGET"
    log "✓ SvelteKit frontend built and copied to: $BUILD_TARGET"
else
    error "Failed to copy build files to Laravel public directory"
fi

log "Frontend build completed successfully!"