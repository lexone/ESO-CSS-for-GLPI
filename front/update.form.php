<?php

include __DIR__ . '/../../../inc/includes.php';
require_once __DIR__ . '/../src/Settings.php';
require_once __DIR__ . '/../src/Updater.php';

use GlpiPlugin\EsoCss\Updater;

Session::checkRight('config', UPDATE);
Plugin::load('esocss');

$rootDoc = $CFG_GLPI['root_doc'] ?? '';
$redirectUrl = $rootDoc . '/plugins/esocss/front/settings.php';

try {
    if (isset($_POST['check_update'])) {
        $state = Updater::checkForUpdates();
        if ($state['available']) {
            Session::addMessageAfterRedirect(
                sprintf('A versão %s está disponível.', $state['latest_version']),
                true,
                INFO
            );
        } else {
            Session::addMessageAfterRedirect('O ESO CSS já está atualizado.', true, INFO);
        }
    } elseif (isset($_POST['install_update'])) {
        $result = Updater::installLatest();
        Session::addMessageAfterRedirect(
            sprintf(
                'ESO CSS atualizado para a versão %s usando %s.',
                $result['version'],
                $result['method'] === 'git' ? 'o repositório Git oficial' : 'o pacote verificado'
            ),
            true,
            INFO
        );
    } else {
        Session::addMessageAfterRedirect('Ação de atualização inválida.', true, ERROR);
    }
} catch (Throwable $exception) {
    Toolbox::logDebug('ESO CSS automatic update failed: ' . $exception->getMessage());
    Session::addMessageAfterRedirect(
        'Falha na atualização automática. Consulte files/_log/php-errors.log para obter detalhes.',
        true,
        ERROR
    );
}

Html::redirect($redirectUrl);
