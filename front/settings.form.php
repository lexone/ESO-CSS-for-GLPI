<?php

include __DIR__ . '/../../../inc/includes.php';
require_once __DIR__ . '/../src/Settings.php';

use GlpiPlugin\EsoCss\MediaManager;
use GlpiPlugin\EsoCss\Settings;

Session::checkRight('config', UPDATE);
Plugin::load('esocss');

$rootDoc = $CFG_GLPI['root_doc'] ?? '';
$redirectUrl = $rootDoc . '/plugins/esocss/front/settings.php';

try {
    if (isset($_POST['reset'])) {
        MediaManager::clearAll();
        $values = Settings::defaults();
        $values['version'] = defined('PLUGIN_ESOCSS_VERSION') ? PLUGIN_ESOCSS_VERSION : '1.8.0';
        Config::setConfigurationValues(Settings::CONTEXT, $values);
        Session::addMessageAfterRedirect('Tema ESO CSS restaurado para os valores padrão.', true, INFO);
    } elseif (isset($_POST['update'])) {
        $values = Settings::sanitize($_POST);
        $values = array_merge(
            $values,
            MediaManager::processSettings(Settings::get(), $_POST, $_FILES)
        );
        $values['version'] = defined('PLUGIN_ESOCSS_VERSION') ? PLUGIN_ESOCSS_VERSION : '1.8.0';
        Config::setConfigurationValues(Settings::CONTEXT, $values);
        Session::addMessageAfterRedirect('Configuração visual salva com sucesso.', true, INFO);
    } else {
        Session::addMessageAfterRedirect('Ação de configuração inválida.', true, ERROR);
    }
} catch (RuntimeException $exception) {
    Toolbox::logDebug('ESO CSS settings validation failed: ' . $exception->getMessage());
    Session::addMessageAfterRedirect(
        'Não foi possível salvar: ' . $exception->getMessage(),
        true,
        ERROR
    );
} catch (Throwable $exception) {
    Toolbox::logDebug('ESO CSS settings save failed: ' . $exception->getMessage());
    Session::addMessageAfterRedirect(
        'Não foi possível salvar a configuração. Consulte files/_log/php-errors.log.',
        true,
        ERROR
    );
}

Html::redirect($redirectUrl);
