#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${1:-http://127.0.0.1:8081}"
CURL=(curl -fsS --connect-timeout 5 --max-time 20)
TMP_DIR="$(mktemp -d)"
trap 'rm -rf "${TMP_DIR}"' EXIT

fetch_snippet() {
  local label="$1"
  local url="$2"
  local output_file="${TMP_DIR}/${label}.body"

  echo "-- ${label} --"
  echo "GET ${url}"
  "${CURL[@]}" "${url}" -o "${output_file}"
  head -c 500 "${output_file}"
  echo
}

echo "== Smoke test against ${BASE_URL} =="

echo "-- Backend login page --"
echo "HEAD ${BASE_URL}/System/Login/index"
"${CURL[@]}" -I "${BASE_URL}/System/Login/index" | sed -n '1,8p'

fetch_snippet "Wxapi banner endpoint" "${BASE_URL}/Wxapi/getbanner?wz=1"
fetch_snippet "System Wxapi banner endpoint" "${BASE_URL}/System/Wxapi/getbanner?wz=1"

echo "Smoke test completed."
