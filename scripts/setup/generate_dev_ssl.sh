#!/bin/bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CERTS_DIR="${SCRIPT_DIR}/../../modulo_web/nginx/certs"

echo "Generating local dev SSL certificate for localhost..."

mkdir -p "$CERTS_DIR"

if [ -f "$CERTS_DIR/dev.crt" ] && [ -f "$CERTS_DIR/dev.key" ]; then
    echo "Certificates already exist at $CERTS_DIR/dev.crt"
    exit 0
fi

# Create OpenSSL config with SANs
cat > /tmp/san.cnf <<EOF
[req]
default_bits       = 2048
distinguished_name = req_distinguished_name
req_extensions     = req_ext
x509_extensions    = v3_req
prompt             = no

[req_distinguished_name]
C  = BR
ST = State
L  = City
O  = CattleRFID
OU = Dev
CN = localhost

[req_ext]
subjectAltName = @alt_names

[v3_req]
subjectAltName = @alt_names

[alt_names]
DNS.1 = localhost
IP.1  = 127.0.0.1
EOF

# Generate self-signed cert
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout "$CERTS_DIR/dev.key" \
    -out "$CERTS_DIR/dev.crt" \
    -config /tmp/san.cnf \
    -extensions v3_req

chmod 644 "$CERTS_DIR/dev.crt"
chmod 600 "$CERTS_DIR/dev.key"

echo "✅ Generated dev.crt and dev.key in $CERTS_DIR"
rm -f /tmp/san.cnf
