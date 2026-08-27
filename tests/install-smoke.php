<?php

define('PLUGIN_ESOCSS_VERSION', '1.9.2');

final class Config
{
    public static array $current = [
        'primary_color' => '#123456',
        'primary_hover' => '#654321',
    ];
    public static array $saved = [];

    public static function getConfigurationValues(string $context): array
    {
        return self::$current;
    }

    public static function setConfigurationValues(string $context, array $values): void
    {
        self::$saved = $values;
    }
}

function assertInstallValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(
            STDERR,
            sprintf("%s\nEsperado: %s\nRecebido: %s\n", $message, var_export($expected, true), var_export($actual, true))
        );
        exit(1);
    }
}

require_once __DIR__ . '/../hook.php';

assertInstallValue(true, plugin_esocss_install(), 'A instalação deve concluir com sucesso.');
assertInstallValue(
    '#123456',
    Config::$saved['button_background'] ?? null,
    'A atualização deve migrar a antiga cor primária para o fundo do botão.'
);
assertInstallValue(
    '#654321',
    Config::$saved['button_hover_background'] ?? null,
    'A atualização deve migrar o antigo hover primário para o botão.'
);
assertInstallValue('1.9.2', Config::$saved['version'] ?? null, 'A versão instalada deve ser atualizada.');

fwrite(STDOUT, "Install smoke test: OK\n");
