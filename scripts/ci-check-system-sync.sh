#!/usr/bin/env bash
set -euo pipefail

BASE_REF="${1:-}"
if [[ -z "$BASE_REF" ]]; then
  echo "Usage: $0 <base-ref>"
  exit 2
fi

RANGE="origin/${BASE_REF}...HEAD"
CHANGED_FILES="$(git diff --name-only "${RANGE}")"

echo "Checking diff range: ${RANGE}"

SYSTEM_CHANGED=0
SUBMODULE_CHANGED=0

if echo "${CHANGED_FILES}" | grep -qE '^system/'; then
  SYSTEM_CHANGED=1
fi

if echo "${CHANGED_FILES}" | grep -qE '^core/AitiCore$'; then
  SUBMODULE_CHANGED=1
fi

if [[ "${SYSTEM_CHANGED}" -eq 1 && "${SUBMODULE_CHANGED}" -eq 0 ]]; then
  echo ""
  echo "ERROR: Perubahan di system/ terdeteksi tanpa update submodule core/AitiCore."
  echo "Gunakan workflow resmi:"
  echo "1) sync via scripts/core-sync.ps1"
  echo "2) commit pointer submodule core/AitiCore + hasil sync system/"
  echo "Dokumen: docs/CORE_SUBMODULE_WORKFLOW.md"
  exit 1
fi

if [[ "${SYSTEM_CHANGED}" -eq 0 && "${SUBMODULE_CHANGED}" -eq 1 ]]; then
  echo ""
  echo "ERROR: Pointer submodule core/AitiCore berubah tanpa perubahan system/."
  echo "Jalankan sync lalu commit hasil sinkronisasi system/."
  echo "Dokumen: docs/CORE_SUBMODULE_WORKFLOW.md"
  exit 1
fi

echo "OK: system/submodule sync check passed."
