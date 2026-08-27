<?php

final class Config
{
    public static array $values = [];

    public static function getConfigurationValues(string $context): array
    {
        return self::$values[$context] ?? [];
    }
}

define('PLUGIN_ESOCSS_VERSION', '1.7.3');

require_once __DIR__ . '/../src/Settings.php';
require_once __DIR__ . '/../src/Updater.php';

use GlpiPlugin\EsoCss\Settings;
use GlpiPlugin\EsoCss\Updater;

function assertUpdaterValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(
            STDERR,
            sprintf("%s\nEsperado: %s\nRecebido: %s\n", $message, var_export($expected, true), var_export($actual, true))
        );
        exit(1);
    }
}

assertUpdaterValue('1.7.3', Updater::currentVersion(), 'A versão atual deve vir da constante do plugin.');

Config::$values[Settings::CONTEXT] = [
    'update_latest_version' => '1.8.0',
    'update_checked_at'     => '2026-08-26 12:00:00',
    'update_release_url'    => 'https://github.com/lexone/ESO-CSS-for-GLPI/releases/tag/v1.8.0',
];

$state = Updater::getCachedState();
assertUpdaterValue(true, $state['checked'], 'Uma consulta válida deve ser reconhecida.');
assertUpdaterValue(true, $state['available'], 'Uma versão superior deve aparecer como disponível.');
assertUpdaterValue('1.8.0', $state['latest_version'], 'A versão remota deve ser preservada.');

Config::$values[Settings::CONTEXT]['update_release_url'] = 'https://example.com/falso';
$state = Updater::getCachedState();
assertUpdaterValue('', $state['release_url'], 'URLs externas ao repositório oficial devem ser rejeitadas.');

fwrite(STDOUT, "Updater smoke test: OK\n");
