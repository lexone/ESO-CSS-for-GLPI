<?php

$mediaRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'esocss-media-' . bin2hex(random_bytes(6));
define('GLPI_PLUGIN_DOC_DIR', $mediaRoot);

$CFG_GLPI = ['root_doc' => '/helpdesk'];

require_once __DIR__ . '/../src/MediaManager.php';

use GlpiPlugin\EsoCss\MediaManager;

function assertMediaValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(
            STDERR,
            sprintf("%s\nEsperado: %s\nRecebido: %s\n", $message, var_export($expected, true), var_export($actual, true))
        );
        exit(1);
    }
}

$directory = $mediaRoot . DIRECTORY_SEPARATOR . 'esocss';
mkdir($directory, 0750, true);

$filename = 'login-background-a1b2c3d4e5f6.png';
$path = $directory . DIRECTORY_SEPARATOR . $filename;
$png = base64_decode(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
    true
);
if ($png === false) {
    fwrite(STDERR, "Não foi possível preparar a imagem de teste.\n");
    exit(1);
}
file_put_contents($path, $png);

$expectedUrl = '/helpdesk/plugins/esocss/media/?name=' . $filename . '&v=' . filemtime($path);
assertMediaValue($expectedUrl, MediaManager::publicUrl($filename), 'A URL pública deve usar a rota anônima sem extensão PHP.');

$file = MediaManager::publicFile($filename);
assertMediaValue(true, is_array($file), 'Uma imagem gerenciada válida deve ser resolvida.');
assertMediaValue(realpath($path), $file['path'] ?? null, 'A imagem precisa permanecer dentro da pasta privada do plugin.');
assertMediaValue('image/png', $file['mime'] ?? null, 'O tipo real da imagem deve ser preservado.');
assertMediaValue(strlen($png), $file['size'] ?? null, 'O tamanho público deve corresponder ao arquivo validado.');

assertMediaValue(null, MediaManager::publicFile('../config_db.php'), 'Caminhos arbitrários devem ser rejeitados.');
assertMediaValue(null, MediaManager::publicFile('login-background-a1b2c3d4e5f6.php'), 'Extensões executáveis devem ser rejeitadas.');

$invalidFilename = 'home-logo-a1b2c3d4e5f6.png';
file_put_contents($directory . DIRECTORY_SEPARATOR . $invalidFilename, 'not an image');
assertMediaValue(null, MediaManager::publicFile($invalidFilename), 'O conteúdo real precisa ser uma imagem válida.');

MediaManager::clearAll();
@rmdir($mediaRoot);

fwrite(STDOUT, "Media smoke test: OK\n");
