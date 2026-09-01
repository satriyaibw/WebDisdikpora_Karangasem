#!/bin/bash
set -e
# Best practice Cloudflare Quick Tunnel launcher with auto APP_URL update (Laravel + Docker)
# Usage: ./run-tunnel.sh  -> creates trycloudflare URL and patches .env + clears config cache
PROJECT_DIR="/run/media/prakomdisdik/5DE961F17E8EA8CD/WebDisdikpora_Karangasem"
LOG_FILE="/tmp/cloudflared.log"
URL_FILE="/tmp/cloudflared-url.txt"

pkill -f "cloudflared tunnel" || true
rm -f "$LOG_FILE" "$URL_FILE"
sleep 1

echo "[1/3] Starting cloudflared quick tunnel to http://localhost:80 ..."
setsid cloudflared tunnel --url http://localhost:80 --no-autoupdate > "$LOG_FILE" 2>&1 < /dev/null &

echo "[2/3] Waiting for tunnel URL (max 30s)..."
for i in $(seq 1 30); do
  if grep -q "trycloudflare.com" "$LOG_FILE" 2>/dev/null; then
    TUNNEL_URL=$(grep -o "https://.*trycloudflare.com" "$LOG_FILE" | head -n1)
    echo "$TUNNEL_URL" > "$URL_FILE"
    echo " -> Tunnel ready: $TUNNEL_URL"
    break
  fi
  sleep 1
done

if [ ! -f "$URL_FILE" ]; then
  echo " ! Failed to get tunnel URL. Log tail:"
  cat "$LOG_FILE" || true
  exit 1
fi

TUNNEL_URL=$(cat "$URL_FILE")
echo "[3/3] Patching APP_URL in .env and clearing Laravel cache..."
cd "$PROJECT_DIR"
if grep -q "^APP_URL=" .env; then
  sed -i "s|^APP_URL=.*|APP_URL=${TUNNEL_URL}|" .env
else
  echo "APP_URL=${TUNNEL_URL}" >> .env
fi
grep APP_URL .env
echo "pratyaksa" | sudo -S docker compose exec -T app sh -c 'php artisan config:clear && php artisan cache:clear && php artisan optimize' 2>&1 | tail -n 20
echo "pratyaksa" | sudo -S docker compose restart queue-worker 2>&1 | tail -n 5
echo ""
echo "Done! Local: http://localhost:80 | Public: $TUNNEL_URL"
echo "Logs: tail -f $LOG_FILE"
echo "Stop: pkill -f cloudflared"
