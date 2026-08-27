#!/usr/bin/env bash
set -euo pipefail

GLPI_ROOT="${1:-/var/www/html}"
WEB_USER="${WEB_USER:-www-data}"
PLUGIN_DIR="${GLPI_ROOT}/plugins/esocss"

if [[ "$(id -u)" -ne 0 ]]; then
  echo "ERRO: execute como root." >&2
  exit 1
fi

cd "${PLUGIN_DIR}"
git pull --ff-only
chown -R "${WEB_USER}:${WEB_USER}" "${PLUGIN_DIR}"
find "${PLUGIN_DIR}" -type d -exec chmod 755 {} \;
find "${PLUGIN_DIR}" -type f -exec chmod 644 {} \;
chmod 755 "${PLUGIN_DIR}/scripts/"*.sh 2>/dev/null || true

sudo -u "${WEB_USER}" php "${GLPI_ROOT}/bin/console" glpi:plugin:install --force esocss
sudo -u "${WEB_USER}" php "${GLPI_ROOT}/bin/console" glpi:plugin:activate esocss
sudo -u "${WEB_USER}" php "${GLPI_ROOT}/bin/console" cache:clear

echo "Plugin atualizado."
