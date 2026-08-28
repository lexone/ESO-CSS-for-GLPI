<?php

/**
 * ESO CSS for GLPI - GLPI 11
 *
 * @author Everton Silva de Oliveira
 * @license GPLv3
 */

use Glpi\Plugin\Hooks;
use Glpi\Http\Firewall;
use Glpi\Http\SessionManager;
use GlpiPlugin\EsoCss\Settings;

require_once __DIR__ . '/src/Settings.php';
require_once __DIR__ . '/src/Updater.php';

define('PLUGIN_ESOCSS_VERSION', '1.9.3');
define('PLUGIN_ESOCSS_MIN_GLPI', '11.0.0');
define('PLUGIN_ESOCSS_MAX_GLPI', '11.0.99');

function plugin_esocss_boot(): void
{
    Firewall::addPluginStrategyForLegacyScripts(
        'esocss',
        '#^/media/index\.php$#',
        Firewall::STRATEGY_NO_CHECK,
    );
    SessionManager::registerPluginStatelessPath('esocss', '#^/media/index\.php$#');
}

function plugin_init_esocss(): void
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS[Hooks::ADD_CSS]['esocss'] = 'css/eso-modern.css';
    $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT]['esocss'] = 'js/eso-theme.js';
    $PLUGIN_HOOKS[Hooks::ADD_CSS_ANONYMOUS_PAGE]['esocss'] = 'css/eso-modern.css';
    $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT_ANONYMOUS_PAGE]['esocss'] = 'js/eso-theme.js';
    $PLUGIN_HOOKS['csrf_compliant']['esocss'] = true;

    $settings = Settings::get();
    if (
        ($settings['login_enabled'] ?? '0') === '1'
        || ($settings['brand_favicon_image'] ?? '') !== ''
    ) {
        $PLUGIN_HOOKS[Hooks::DISPLAY_LOGIN]['esocss'] = 'plugin_esocss_display_login';
    }

    if (Session::getLoginUserID() && Session::haveRight('config', UPDATE)) {
        require_once __DIR__ . '/src/Menu.php';

        $PLUGIN_HOOKS[Hooks::CONFIG_PAGE]['esocss'] = 'front/settings.php';
        $PLUGIN_HOOKS[Hooks::MENU_TOADD]['esocss'] = [
            'config' => \GlpiPlugin\EsoCss\Menu::class,
        ];
    }
}

function plugin_version_esocss(): array
{
    return [
        'name'         => 'ESO CSS for GLPI',
        'version'      => PLUGIN_ESOCSS_VERSION,
        'author'       => 'Everton Silva de Oliveira',
        'license'      => 'GPLv3',
        'homepage'     => 'https://github.com/lexone/ESO-CSS-for-GLPI',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_ESOCSS_MIN_GLPI,
                'max' => PLUGIN_ESOCSS_MAX_GLPI,
            ],
        ],
    ];
}

function plugin_esocss_check_prerequisites(): bool
{
    return version_compare(GLPI_VERSION, PLUGIN_ESOCSS_MIN_GLPI, '>=')
        && version_compare(GLPI_VERSION, PLUGIN_ESOCSS_MAX_GLPI, '<=');
}

function plugin_esocss_check_config(bool $verbose = false): bool
{
    return true;
}
