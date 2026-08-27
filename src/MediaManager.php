<?php

namespace GlpiPlugin\EsoCss;

use RuntimeException;

final class MediaManager
{
    public const MAX_UPLOAD_BYTES = 5 * 1024 * 1024;

    private const SLOTS = [
        'home_background' => [
            'config_key' => 'home_background_image',
            'input_name' => 'home_background_file',
            'prefix'     => 'home-background',
        ],
        'home_logo' => [
            'config_key' => 'home_logo_image',
            'input_name' => 'home_logo_file',
            'prefix'     => 'home-logo',
        ],
        'login_background' => [
            'config_key' => 'login_background_image',
            'input_name' => 'login_background_file',
            'prefix'     => 'login-background',
        ],
        'login_logo' => [
            'config_key' => 'login_logo_image',
            'input_name' => 'login_logo_file',
            'prefix'     => 'login-logo',
        ],
        'brand_sidebar_logo' => [
            'config_key' => 'brand_sidebar_logo_image',
            'input_name' => 'brand_sidebar_logo_file',
            'prefix'     => 'brand-sidebar-logo',
        ],
        'brand_sidebar_compact_logo' => [
            'config_key' => 'brand_sidebar_compact_logo_image',
            'input_name' => 'brand_sidebar_compact_logo_file',
            'prefix'     => 'brand-sidebar-compact-logo',
        ],
        'brand_header_logo' => [
            'config_key' => 'brand_header_logo_image',
            'input_name' => 'brand_header_logo_file',
            'prefix'     => 'brand-header-logo',
        ],
        'brand_favicon' => [
            'config_key' => 'brand_favicon_image',
            'input_name' => 'brand_favicon_file',
            'prefix'     => 'brand-favicon',
        ],
    ];

    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * @return array{
     *     home_background_image: string,
     *     home_logo_image: string,
     *     login_background_image: string,
     *     login_logo_image: string,
     *     brand_sidebar_logo_image: string,
     *     brand_sidebar_compact_logo_image: string,
     *     brand_header_logo_image: string,
     *     brand_favicon_image: string
     * }
     */
    public static function processSettings(array $current, array $post, array $files): array
    {
        $result = [];

        foreach (self::SLOTS as $slot => $definition) {
            $configKey = $definition['config_key'];
            $filename = self::isManagedFilename((string) ($current[$configKey] ?? ''))
                ? (string) $current[$configKey]
                : '';

            if (isset($post['remove_' . $slot]) && (string) $post['remove_' . $slot] === '1') {
                self::removeSlot($slot);
                $filename = '';
            }

            $file = $files[$definition['input_name']] ?? null;
            if (is_array($file) && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $filename = self::storeUpload($slot, $file);
            }

            $result[$configKey] = $filename;
        }

        return $result;
    }

    public static function clearAll(): void
    {
        foreach (array_keys(self::SLOTS) as $slot) {
            self::removeSlot($slot);
        }

        $directory = self::storageDirectory();
        if (is_dir($directory)) {
            @rmdir($directory);
        }
    }

    public static function publicUrl(string $filename): string
    {
        global $CFG_GLPI;

        if (!self::isManagedFilename($filename) || !defined('GLPI_PLUGIN_DOC_DIR')) {
            return '';
        }

        $path = self::storageDirectory() . '/' . $filename;
        if (!is_file($path)) {
            return '';
        }

        $rootDoc = rtrim((string) ($CFG_GLPI['root_doc'] ?? ''), '/');
        $version = (string) (filemtime($path) ?: 0);

        return $rootDoc . '/front/pluginimage.send.php?plugin=esocss&name=' . rawurlencode($filename)
            . '&v=' . rawurlencode($version);
    }

    public static function isManagedFilename(string $filename): bool
    {
        return preg_match(
            '/^(?:home-background|home-logo|login-background|login-logo|brand-sidebar-logo|brand-sidebar-compact-logo|brand-header-logo|brand-favicon)(?:-[a-f0-9]{12})?\.(?:jpg|png|webp)$/',
            $filename
        ) === 1;
    }

    /**
     * @param array<string, mixed> $file
     */
    private static function storeUpload(string $slot, array $file): string
    {
        $definition = self::slotDefinition($slot);
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('O upload da imagem não foi concluído (código ' . $error . ').');
        }

        $size = (int) ($file['size'] ?? 0);
        $temporaryPath = (string) ($file['tmp_name'] ?? '');
        if ($size < 1 || $size > self::MAX_UPLOAD_BYTES || !is_uploaded_file($temporaryPath)) {
            throw new RuntimeException('A imagem deve ter no máximo 5 MB e vir de um upload válido.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($temporaryPath);
        $imageInfo = @getimagesize($temporaryPath);
        if (!isset(self::MIME_EXTENSIONS[$mime]) || !is_array($imageInfo)) {
            throw new RuntimeException('Use uma imagem JPG, PNG ou WebP válida.');
        }

        $width = (int) ($imageInfo[0] ?? 0);
        $height = (int) ($imageInfo[1] ?? 0);
        $detectedMime = (string) ($imageInfo['mime'] ?? '');
        if (
            $width < 1
            || $height < 1
            || $width > 8000
            || $height > 8000
            || $width * $height > 40_000_000
            || $detectedMime !== $mime
        ) {
            throw new RuntimeException('As dimensões ou o formato interno da imagem não são permitidos.');
        }

        $directory = self::storageDirectory();
        if (!is_dir($directory) && !@mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new RuntimeException('Não foi possível criar a pasta de imagens do ESO CSS.');
        }
        if (!is_writable($directory)) {
            throw new RuntimeException('A pasta de imagens do ESO CSS não permite gravação.');
        }

        $extension = self::MIME_EXTENSIONS[$mime];
        $filename = $definition['prefix'] . '-' . bin2hex(random_bytes(6)) . '.' . $extension;
        $temporaryTarget = $directory . '/.' . $definition['prefix'] . '-' . bin2hex(random_bytes(6)) . '.tmp';

        if (!move_uploaded_file($temporaryPath, $temporaryTarget)) {
            throw new RuntimeException('Não foi possível mover a imagem enviada para a pasta segura.');
        }

        @chmod($temporaryTarget, 0640);

        if (!@rename($temporaryTarget, $directory . '/' . $filename)) {
            @unlink($temporaryTarget);
            throw new RuntimeException('Não foi possível ativar a nova imagem.');
        }

        self::removeSlot($slot, $filename);

        return $filename;
    }

    private static function removeSlot(string $slot, string $preserveFilename = ''): void
    {
        $definition = self::slotDefinition($slot);
        $directory = self::storageDirectory();

        if (!is_dir($directory)) {
            return;
        }

        $pattern = '/^' . preg_quote($definition['prefix'], '/') . '(?:-[a-f0-9]{12})?\.(?:jpg|png|webp)$/';
        $iterator = new \FilesystemIterator($directory, \FilesystemIterator::SKIP_DOTS);
        foreach ($iterator as $item) {
            $filename = $item->getFilename();
            if (
                $filename !== $preserveFilename
                && $item->isFile()
                && !$item->isLink()
                && preg_match($pattern, $filename) === 1
                && !@unlink($item->getPathname())
            ) {
                throw new RuntimeException('Não foi possível remover a imagem anterior do ESO CSS.');
            }
        }
    }

    /**
     * @return array{config_key: string, input_name: string, prefix: string}
     */
    private static function slotDefinition(string $slot): array
    {
        if (!isset(self::SLOTS[$slot])) {
            throw new RuntimeException('O tipo de imagem solicitado é inválido.');
        }

        return self::SLOTS[$slot];
    }

    private static function storageDirectory(): string
    {
        if (!defined('GLPI_PLUGIN_DOC_DIR')) {
            throw new RuntimeException('A pasta de documentos de plugins do GLPI não está disponível.');
        }

        return rtrim(GLPI_PLUGIN_DOC_DIR, '/\\') . '/esocss';
    }
}
