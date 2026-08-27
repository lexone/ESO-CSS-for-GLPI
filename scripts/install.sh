#!/usr/bin/env bash
set -euo pipefail

GLPI_ROOT="${1:-/var/www/html}"
WEB_USER="${WEB_USER:-www-data}"
PLUGIN_DIR="${GLPI_ROOT}/plugins/esocss"

if [[ ! -f "${GLPI_ROOT}/bin/console" ]]; then
  echo "ERRO: GLPI não encontrado em ${GLPI_ROOT}" >&2
  exit 1
fi

if [[ ! -f "${PLUGIN_DIR}/setup.php" ]]; then
  echo "ERRO: plugin não encontrado em ${PLUGIN_DIR}. Clone o repositório usando o nome esocss." >&2
  exit 1
fi

if [[ "$(id -u)" -ne 0 ]]; then
  echo "ERRO: execute este script como root para ajustar permissões." >&2
  exit 1
fi

echo "[1/5] Ajustando permissões..."
chown -R "${WEB_USER}:${WEB_USER}" "${PLUGIN_DIR}"
find "${PLUGIN_DIR}" -type d -exec chmod 755 {} \;
find "${PLUGIN_DIR}" -type f -exec chmod 644 {} \;
chmod 755 "${PLUGIN_DIR}/scripts/"*.sh 2>/dev/null || true

echo "[2/5] Instalando/atualizando plugin..."
if sudo -u "${WEB_USER}" php "${GLPI_ROOT}/bin/console" glpi:plugin:list | grep -qE '^\|[[:space:]]*esocss[[:space:]]*\|'; then
  sudo -u "${WEB_USER}" php "${GLPI_ROOT}/bin/console" glpi:plugin:install --force esocss
else
  sudo -u "${WEB_USER}" php "${GLPI_ROOT}/bin/console" glpi:plugin:install esocss
fi

echo "[3/5] Ativando plugin..."
sudo -u "${WEB_USER}" php "${GLPI_ROOT}/bin/console" glpi:plugin:activate esocss

echo "[4/5] Limpando cache..."
sudo -u "${WEB_USER}" php "${GLPI_ROOT}/bin/console" cache:clear

echo "[5/5] Estado final:"
sudo -u "${WEB_USER}" php "${GLPI_ROOT}/bin/console" glpi:plugin:list | grep -E 'esocss|singlesignon' || true

echo
echo "Concluído. Abra Configurar > Plugins > ESO CSS for GLPI para personalizar."
