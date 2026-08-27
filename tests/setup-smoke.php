<?php

namespace Glpi\Plugin {
    final class Hooks
    {
        public const ADD_CSS = 'add_css';
        public const ADD_JAVASCRIPT = 'add_javascript';
        public const ADD_CSS_ANONYMOUS_PAGE = 'add_css_anonymous_page';
        public const ADD_JAVASCRIPT_ANONYMOUS_PAGE = 'add_javascript_anonymous_page';
        public const DISPLAY_LOGIN = 'display_login';
        public const CONFIG_PAGE = 'config_page';
        public const MENU_TOADD = 'menu_toadd';
    }
}

namespace {
    const UPDATE = 4;

    final class Config
    {
        public static function getConfigurationValues(string $context): array
        {
            return ['login_enabled' => '1'];
        }
    }

    final class Session
    {
        public static function getLoginUserID(): int
        {
            return 0;
        }

        public static function haveRight(string $right, int $level): bool
        {
            return false;
        }
    }

    function assertSetupValue(mixed $expected, mixed $actual, string $message): void
    {
        if ($expected !== $actual) {
            fwrite(
                STDERR,
                sprintf("%s\nEsperado: %s\nRecebido: %s\n", $message, var_export($expected, true), var_export($actual, true))
            );
            exit(1);
        }
    }

    require_once __DIR__ . '/../setup.php';

    $PLUGIN_HOOKS = [];
    plugin_init_esocss();

    assertSetupValue(
        'css/eso-modern.css',
        $PLUGIN_HOOKS[\Glpi\Plugin\Hooks::ADD_CSS_ANONYMOUS_PAGE]['esocss'] ?? null,
        'O CSS do login precisa ser carregado nas páginas anônimas do GLPI 11.'
    );
    assertSetupValue(
        'js/eso-theme.js',
        $PLUGIN_HOOKS[\Glpi\Plugin\Hooks::ADD_JAVASCRIPT_ANONYMOUS_PAGE]['esocss'] ?? null,
        'O JavaScript do login precisa ser carregado nas páginas anônimas do GLPI 11.'
    );
    assertSetupValue(
        'plugin_esocss_display_login',
        $PLUGIN_HOOKS[\Glpi\Plugin\Hooks::DISPLAY_LOGIN]['esocss'] ?? null,
        'A configuração ativa do login precisa registrar o gancho de exibição.'
    );

    fwrite(STDOUT, "Setup smoke test: OK\n");
}
