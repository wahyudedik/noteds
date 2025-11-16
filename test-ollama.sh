#!/bin/bash

# Script untuk test Ollama API
# Usage: bash test-ollama.sh
# NOTE: Script ini untuk digunakan di VPS, bukan di local development

echo "=========================================="
echo "Ollama API Test Script"
echo "=========================================="
echo ""

# Test 1: Check Ollama service status
echo "1. Checking Ollama service status..."
systemctl is-active ollama > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "   ✅ Ollama service is running"
else
    echo "   ❌ Ollama service is not running"
    exit 1
fi
echo ""

# Test 2: Check available models
echo "2. Checking available models..."
MODELS_RESPONSE=$(curl -s http://localhost:11434/api/tags)
if [ $? -eq 0 ]; then
    echo "   ✅ Ollama API is accessible"
    echo "   Available models:"
    echo "$MODELS_RESPONSE" | grep -o '"name":"[^"]*"' | sed 's/"name":"\([^"]*\)"/     - \1/' | head -10
else
    echo "   ❌ Cannot connect to Ollama API"
    exit 1
fi
echo ""

# Test 3: Check if llama3.2 model exists
echo "3. Checking if llama3.2 model is available..."
if echo "$MODELS_RESPONSE" | grep -q "llama3.2"; then
    echo "   ✅ llama3.2 model is available"
else
    echo "   ⚠️  llama3.2 model is not found"
    echo "   Available models:"
    echo "$MODELS_RESPONSE" | grep -o '"name":"[^"]*"' | sed 's/"name":"\([^"]*\)"/     - \1/'
    echo ""
    echo "   To install llama3.2, run: ollama pull llama3.2"
fi
echo ""

# Test 4: Test API generate endpoint
echo "4. Testing API generate endpoint..."
GENERATE_RESPONSE=$(curl -s -X POST http://localhost:11434/api/generate \
  -H "Content-Type: application/json" \
  -d '{
    "model": "llama3.2",
    "prompt": "Hello, how are you?",
    "stream": false
  }')

if [ $? -eq 0 ]; then
    # Check if response contains "response" field
    if echo "$GENERATE_RESPONSE" | grep -q '"response"'; then
        echo "   ✅ API generate endpoint is working"
        echo "   Response preview:"
        echo "$GENERATE_RESPONSE" | grep -o '"response":"[^"]*' | head -c 100
        echo "..."
    else
        echo "   ⚠️  API generate endpoint returned unexpected response:"
        echo "$GENERATE_RESPONSE" | head -5
    fi
else
    echo "   ❌ API generate endpoint failed"
    exit 1
fi
echo ""

# Test 5: Check Ollama configuration
echo "5. Checking Ollama configuration..."
if [ -f "/etc/systemd/system/ollama.service" ]; then
    echo "   ✅ Ollama service file exists"
    echo "   Service file location: /etc/systemd/system/ollama.service"
else
    echo "   ⚠️  Ollama service file not found"
fi
echo ""

# Test 6: Check Ollama process
echo "6. Checking Ollama process..."
OLLAMA_PID=$(pgrep -f "ollama serve")
if [ -n "$OLLAMA_PID" ]; then
    echo "   ✅ Ollama process is running (PID: $OLLAMA_PID)"
    echo "   Memory usage:"
    ps -p $OLLAMA_PID -o rss= | awk '{printf "     %.2f MB\n", $1/1024}'
else
    echo "   ❌ Ollama process is not running"
fi
echo ""

echo "=========================================="
echo "Test completed!"
echo "=========================================="

