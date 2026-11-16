#!/usr/bin/env bash
# Deploy to the live Hostinger site. Usage: GGT_SSH_PASSWORD='...' bash scripts/deploy.sh
# Server details come from .deploy.env (git-ignored). The password is never stored.
set -euo pipefail
cd "$(dirname "$0")/.."
source .deploy.env   # GGT_SSH_HOST, GGT_SSH_PORT, GGT_SSH_USER, GGT_REMOTE_APP, GGT_URL
: "${GGT_SSH_PASSWORD:?Set GGT_SSH_PASSWORD}"

ASK="$(mktemp)"; trap 'rm -f "$ASK"' EXIT
printf '#!/bin/sh\necho "$GGT_SSH_PASSWORD"\n' > "$ASK"; chmod +x "$ASK"
export SSH_ASKPASS="$ASK" SSH_ASKPASS_REQUIRE=force DISPLAY=x GGT_SSH_PASSWORD
SSH=(ssh -p "$GGT_SSH_PORT" -o PubkeyAuthentication=no "$GGT_SSH_USER@$GGT_SSH_HOST")
PHP=/opt/alt/php84/usr/bin/php

echo "== build assets"; npm run build >/dev/null
if [ -n "$(git status --porcelain)" ]; then git add -A && git commit -q -m "${1:-Update site}

Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"; fi
echo "== push"; git push -q origin main
echo "== upload build + pull + migrate"
tar czf - public/build | "${SSH[@]}" "set -e; cd $GGT_REMOTE_APP && git pull -q && tar xzf - && $PHP \$(which composer) install --no-dev -o -n -q && $PHP artisan migrate --force && $PHP artisan optimize:clear >/dev/null"
echo "== live check"; curl -s -o /dev/null -w "%{http_code}\n" "$GGT_URL"
