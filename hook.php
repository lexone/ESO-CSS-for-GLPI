<?php

/**
 * Install/uninstall hooks for ESO CSS for GLPI.
 */

require_once __DIR__ . '/src/Settings.php';

use GlpiPlugin\EsoCss\MediaManager;
use GlpiPlugin\EsoCss\Settings;

function plugin_esocss_install(): bool
{
    $current = Config::getConfigurationValues(Settings::CONTEXT);
    $defaults = Settings::defaults();

    // Preserve existing values on upgrades and only seed missing defaults.
    $values = array_merge($defaults, $current);
    if (!array_key_exists('button_background', $current)) {
        $values['button_background'] = $values['primary_color'];
    }
    if (!array_key_exists('button_hover_background', $current)) {
        $values['button_hover_background'] = $values['primary_hover'];
    }
    $values['version'] = PLUGIN_ESOCSS_VERSION;

    Config::setConfigurationValues(Settings::CONTEXT, $values);

    return true;
}

function plugin_esocss_uninstall(): bool
{
    MediaManager::clearAll();

    $config = new Config();
    $keys = array_keys(Settings::defaults());
    $keys[] = 'version';
    $config->deleteConfigurationValues(Settings::CONTEXT, $keys);

    return true;
}

/**
 * Exposes only sanitized branding values on the anonymous login page.
 * Authentication controls remain owned by GLPI and other authentication plugins.
 */
function plugin_esocss_display_login(): void
{
    $payload = Settings::publicLoginPayload();
    if (!$payload['enabled'] && $payload['favicon_url'] === '') {
        return;
    }

    $json = json_encode(
        $payload,
        JSON_UNESCAPED_SLASHES
        | JSON_UNESCAPED_UNICODE
        | JSON_HEX_TAG
        | JSON_HEX_AMP
        | JSON_HEX_APOS
        | JSON_HEX_QUOT
    );

    if ($json === false) {
        return;
    }

    echo '<script type="application/json" id="esocss-login-config">' . $json . '</script>';
}
