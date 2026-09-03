#!/usr/bin/env bash
# Mirror this codebase → https://github.com/musabgaili/vuja-portal-api
set -euo pipefail

API_REPO="${API_REPO:-https://github.com/musabgaili/vuja-portal-api.git}"
SOURCE_BRANCH="${SOURCE_BRANCH:-$(git branch --show-current)}"
TARGET_BRANCH="${TARGET_BRANCH:-main}"

echo "→ Pushing ${SOURCE_BRANCH} to ${API_REPO} (${TARGET_BRANCH})"

if ! git remote get-url api &>/dev/null; then
  git remote add api "$API_REPO"
else
  git remote set-url api "$API_REPO"
fi

if ! git push api "${SOURCE_BRANCH}:${TARGET_BRANCH}" -u; then
  cat <<'EOF'

Push failed — the Cloud Agent token cannot see vuja-portal-api yet.

Fix (one time):
1. Cursor → Cloud Agents → your Environment → Repositories
2. Add: github.com/musabgaili/vuja-portal-api
3. Re-run: ./scripts/mirror-to-vuja-portal-api.sh

Or push manually from your machine:
  git remote add api https://github.com/musabgaili/vuja-portal-api.git
  git push api cursor/sprint-3-4-projects-chat-f4af:main
EOF
  exit 1
fi

git push api --tags 2>/dev/null || true
echo "✓ vuja-portal-api is up to date on ${TARGET_BRANCH}."
