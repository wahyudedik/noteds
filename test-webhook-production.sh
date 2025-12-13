#!/bin/bash
# Test webhook connectivity dan signature verification

WEBHOOK_URL="https://noteds.com/wallet/webhook"
ORDER_ID="topup-test-$(date +%s)"
AMOUNT="50000"
SERVER_KEY="YOUR_SERVER_KEY"  # Ganti dengan server key yang benar

# Generate signature
SIGNATURE=$(php -r "echo hash('sha512', '$ORDER_ID' . '200' . '$AMOUNT' . '$SERVER_KEY');")

echo "=== WEBHOOK CONNECTIVITY TEST ==="
echo "URL: $WEBHOOK_URL"
echo "Order ID: $ORDER_ID"
echo "Amount: $AMOUNT"
echo "Signature: $SIGNATURE"
echo ""

# Test with curl
echo "Testing webhook endpoint..."
curl -v -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d "{
    \"order_id\": \"$ORDER_ID\",
    \"status_code\": \"200\",
    \"gross_amount\": \"$AMOUNT\",
    \"transaction_status\": \"settlement\",
    \"fraud_status\": \"accept\",
    \"signature_key\": \"$SIGNATURE\"
  }"

echo ""
echo "=== TEST COMPLETE ==="
