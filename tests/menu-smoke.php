<?php

const UPDATE = 4;

function __(string $message, string $domain = 'glpi'): string
{
    return $message;
}

class CommonGLPI
{
}

final class Session
{
    public static bool $allowed = false;

    public static function haveRight(string $right, int $level): bool
    {
        return self::$allowed && $right === 'config' && $level === UPDATE;
    }
}

require_once __DIR__ . '/../src/Menu.php';

use GlpiPlugin\EsoCss\Menu;

function assertMenuValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(
            STDERR,
            sprintf("%s\nEsperado: %s\nRecebido: %s\n", $message, var_export($expected, true), var_export($actual, true))
        );
        exit(1);
    }
}

assertMenuValue(false, Menu::getMenuContent(), 'O menu não deve aparecer sem permissão de configuração.');

Session::$allowed = true;
$menu = Menu::getMenuContent();
assertMenuValue('ESO CSS for GLPI', $menu['title'] ?? '', 'O atalho deve exibir o nome do plugin.');
assertMenuValue(
    '/plugins/esocss/front/settings.php',
    $menu['page'] ?? '',
    'O atalho deve abrir diretamente a página de configuração.'
);
assertMenuValue('ti ti-palette', $menu['icon'] ?? '', 'O atalho deve usar o ícone de paleta.');

fwrite(STDOUT, "Menu smoke test: OK\n");
