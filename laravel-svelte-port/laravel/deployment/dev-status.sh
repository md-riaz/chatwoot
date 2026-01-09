#!/bin/bash

# ClearLine Development Status Script
# Shows status of all development servers

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}ClearLine Development Status${NC}"
echo "================================"

# Check Laravel development server
if [ -f "/tmp/laravel-serve.pid" ] && kill -0 $(cat /tmp/laravel-serve.pid) 2>/dev/null; then
    echo -e "${GREEN}✓ Laravel Server${NC}        http://localhost:8000"
else
    echo -e "${RED}✗ Laravel Server${NC}        Not running"
fi

# Check Laravel Reverb WebSocket server
if [ -f "/tmp/laravel-reverb.pid" ] && kill -0 $(cat /tmp/laravel-reverb.pid) 2>/dev/null; then
    echo -e "${GREEN}✓ WebSocket Server${NC}      ws://localhost:8080"
else
    echo -e "${RED}✗ WebSocket Server${NC}      Not running"
fi

# Check Laravel Horizon queue manager
if [ -f "/tmp/laravel-horizon.pid" ] && kill -0 $(cat /tmp/laravel-horizon.pid) 2>/dev/null; then
    echo -e "${GREEN}✓ Horizon Queues${NC}        Running"
else
    echo -e "${RED}✗ Horizon Queues${NC}        Not running"
fi

# Check SvelteKit development server
if [ -f "/tmp/svelte-dev.pid" ] && kill -0 $(cat /tmp/svelte-dev.pid) 2>/dev/null; then
    echo -e "${GREEN}✓ SvelteKit Server${NC}      http://localhost:5173"
else
    echo -e "${RED}✗ SvelteKit Server${NC}      Not running"
fi

echo ""

# Test HTTP endpoints
echo "Testing HTTP endpoints..."

if curl -f -s http://localhost:8000 >/dev/null 2>&1; then
    echo -e "${GREEN}✓ Laravel HTTP${NC}          Responding"
else
    echo -e "${RED}✗ Laravel HTTP${NC}          Not responding"
fi

if curl -f -s http://localhost:5173 >/dev/null 2>&1; then
    echo -e "${GREEN}✓ SvelteKit HTTP${NC}        Responding"
else
    echo -e "${RED}✗ SvelteKit HTTP${NC}        Not responding"
fi

if nc -z localhost 8080 2>/dev/null; then
    echo -e "${GREEN}✓ WebSocket Port${NC}        Open"
else
    echo -e "${RED}✗ WebSocket Port${NC}        Closed"
fi

echo ""

# Show log file locations
echo "Log files:"
echo "  Laravel:    /tmp/laravel-serve.log"
echo "  WebSocket:  /tmp/laravel-reverb.log"
echo "  Horizon:    /tmp/laravel-horizon.log"
echo "  SvelteKit:  /tmp/svelte-dev.log"
echo "  Combined:   /tmp/clearline-dev.log"

echo ""

# Show useful commands
echo "Commands:"
echo "  Start:      ./deployment/dev-start.sh"
echo "  Stop:       ./deployment/dev-stop.sh"
echo "  Status:     ./deployment/dev-status.sh"
echo "  Logs:       tail -f /tmp/clearline-dev.log"