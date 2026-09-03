#!/usr/bin/env bash
# Mirror vuja-portal → vuja-portal-api (run AFTER creating the empty GitHub repo).
set -euo pipefail

API_REPO="${API_REPO:-https://github.com/musabgaili/vuja-portal-api.git}"
SOURCE_BRANCH="${SOURCE_BRANCH:-cursor/sprint-1-auth-fcm-deeplinks-f4af}"
TARGET_BRANCH="${TARGET_BRANCH:-main}"

echo "→ Pushing ${SOURCE_BRANCH} to ${API_REPO} (${TARGET_BRANCH})"
git remote add api "$API_REPO" 2>/dev/null || git remote set-url api "$API_REPO"
git push api "${SOURCE_BRANCH}:${TARGET_BRANCH}" -u
git push api --tags 2>/dev/null || true

echo "✓ Done. vuja-portal-api should now contain the full codebase + API work."
