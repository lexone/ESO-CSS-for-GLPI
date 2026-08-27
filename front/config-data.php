<?php

include __DIR__ . '/../../../inc/includes.php';
require_once __DIR__ . '/../src/Settings.php';

use GlpiPlugin\EsoCss\Settings;

if (!Session::getLoginUserID()) {
    http_response_code(401);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

echo json_encode(
    Settings::publicPayload(),
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
);
