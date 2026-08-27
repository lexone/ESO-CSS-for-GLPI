<?php

include __DIR__ . '/../../../inc/includes.php';

Session::checkRight('config', READ);

$rootDoc = $CFG_GLPI['root_doc'] ?? '';
Html::redirect($rootDoc . '/plugins/esocss/front/settings.php');
