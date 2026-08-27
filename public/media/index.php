<?php

declare(strict_types=1);

/**
 * Public, read-only endpoint for images managed by ESO CSS.
 *
 * The filename whitelist and the storage boundary are enforced by MediaManager.
 * No arbitrary document path can be requested through this endpoint.
 */

include __DIR__ . '/../../../../inc/includes.php';
require_once __DIR__ . '/../../src/MediaManager.php';

use GlpiPlugin\EsoCss\MediaManager;

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
if (!in_array($method, ['GET', 'HEAD'], true)) {
    http_response_code(405);
    header('Allow: GET, HEAD');
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Method not allowed.';
    exit;
}

$filename = (string) ($_GET['name'] ?? '');
$file = MediaManager::publicFile($filename);
if ($file === null) {
    http_response_code(404);
    header('Cache-Control: no-store');
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Image not found.';
    exit;
}

while (ob_get_level() > 0) {
    ob_end_clean();
}

$etag = '"' . hash('sha256', $filename . '|' . $file['modified'] . '|' . $file['size']) . '"';
header('Cache-Control: public, max-age=31536000, immutable');
header('Content-Type: ' . $file['mime']);
header('Content-Length: ' . $file['size']);
header('Content-Disposition: inline; filename="' . $filename . '"');
header('ETag: ' . $etag);
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $file['modified']) . ' GMT');
header('X-Content-Type-Options: nosniff');
header('Cross-Origin-Resource-Policy: same-origin');

if (trim((string) ($_SERVER['HTTP_IF_NONE_MATCH'] ?? '')) === $etag) {
    http_response_code(304);
    exit;
}

if ($method !== 'HEAD') {
    readfile($file['path']);
}

exit;
