<?php

namespace GlpiPlugin\EsoCss;

use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

final class Updater
{
    private const REPOSITORY = 'lexone/ESO-CSS-for-GLPI';
    private const RELEASE_API = 'https://api.github.com/repos/' . self::REPOSITORY . '/releases/latest';
    private const RELEASE_URL_PREFIX = 'https://github.com/' . self::REPOSITORY . '/releases/';
    private const OFFICIAL_REMOTES = [
        'https://github.com/lexone/ESO-CSS-for-GLPI',
        'https://github.com/lexone/ESO-CSS-for-GLPI.git',
    ];

    public static function currentVersion(): string
    {
        return defined('PLUGIN_ESOCSS_VERSION') ? PLUGIN_ESOCSS_VERSION : '1.9.2';
    }

    /**
     * @return array{checked: bool, available: bool, latest_version: string, checked_at: string, release_url: string}
     */
    public static function getCachedState(): array
    {
        $config = \Config::getConfigurationValues(Settings::CONTEXT);
        $latest = self::filterVersion((string) ($config['update_latest_version'] ?? ''));
        $checkedAt = trim((string) ($config['update_checked_at'] ?? ''));
        $releaseUrl = self::filterReleaseUrl((string) ($config['update_release_url'] ?? ''));

        return [
            'checked'        => $latest !== '' && $checkedAt !== '',
            'available'      => $latest !== '' && version_compare($latest, self::currentVersion(), '>'),
            'latest_version' => $latest,
            'checked_at'     => $checkedAt,
            'release_url'    => $releaseUrl,
        ];
    }

    /**
     * @return array{checked: bool, available: bool, latest_version: string, checked_at: string, release_url: string}
     */
    public static function checkForUpdates(): array
    {
        $release = self::fetchLatestRelease();
        $checkedAt = date('Y-m-d H:i:s');

        \Config::setConfigurationValues(Settings::CONTEXT, [
            'update_latest_version' => $release['version'],
            'update_checked_at'     => $checkedAt,
            'update_release_url'    => $release['release_url'],
        ]);

        return [
            'checked'        => true,
            'available'      => version_compare($release['version'], self::currentVersion(), '>'),
            'latest_version' => $release['version'],
            'checked_at'     => $checkedAt,
            'release_url'    => $release['release_url'],
        ];
    }

    /**
     * @return array{version: string, method: string}
     */
    public static function installLatest(): array
    {
        $release = self::fetchLatestRelease();
        $version = $release['version'];

        if (!version_compare($version, self::currentVersion(), '>')) {
            throw new RuntimeException('O ESO CSS já está atualizado.');
        }

        $pluginDir = dirname(__DIR__);
        if (!is_dir($pluginDir) || !is_writable($pluginDir)) {
            throw new RuntimeException('A pasta do plugin não permite gravação pelo servidor web.');
        }

        if (is_dir($pluginDir . '/.git') && class_exists(Process::class)) {
            self::installWithGit($pluginDir, $version);
            $method = 'git';
        } else {
            self::installFromPackage($pluginDir, $release);
            $method = 'package';
        }

        self::assertInstalledVersion($pluginDir, $version);
        self::invalidateOpcache($pluginDir);
        self::recordInstalledVersion($version, $release['release_url']);

        return ['version' => $version, 'method' => $method];
    }

    /**
     * @return array{version: string, release_url: string, package_url: string, checksum_url: string}
     */
    private static function fetchLatestRelease(): array
    {
        try {
            $client = \Toolbox::getGuzzleClient([
                'connect_timeout' => 5,
                'timeout'         => 15,
                'allow_redirects' => [
                    'max'       => 5,
                    'strict'    => true,
                    'protocols' => ['https'],
                ],
            ]);
            $response = $client->request('GET', self::RELEASE_API, [
                'headers' => [
                    'Accept'               => 'application/vnd.github+json',
                    'User-Agent'           => 'ESO-CSS-for-GLPI/' . self::currentVersion(),
                    'X-GitHub-Api-Version' => '2022-11-28',
                ],
                'http_errors' => false,
            ]);
        } catch (Throwable $exception) {
            throw new RuntimeException('Não foi possível consultar o GitHub.', 0, $exception);
        }

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('O GitHub respondeu com HTTP ' . $response->getStatusCode() . '.');
        }

        try {
            $payload = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            throw new RuntimeException('A resposta de versão do GitHub é inválida.', 0, $exception);
        }

        if (!is_array($payload) || ($payload['draft'] ?? true) || ($payload['prerelease'] ?? true)) {
            throw new RuntimeException('O lançamento mais recente não é uma versão estável.');
        }

        $version = self::filterVersion((string) ($payload['tag_name'] ?? ''));
        if ($version === '') {
            throw new RuntimeException('A tag do lançamento não possui uma versão válida.');
        }

        $releaseUrl = self::filterReleaseUrl((string) ($payload['html_url'] ?? ''));
        if ($releaseUrl === '') {
            throw new RuntimeException('O lançamento não pertence ao repositório oficial.');
        }

        $packageName = 'esocss-' . $version . '.zip';
        $checksumName = $packageName . '.sha256';
        $packageUrl = '';
        $checksumUrl = '';

        foreach (($payload['assets'] ?? []) as $asset) {
            if (!is_array($asset)) {
                continue;
            }
            $name = (string) ($asset['name'] ?? '');
            $url = self::filterAssetUrl((string) ($asset['browser_download_url'] ?? ''), $version);
            if ($name === $packageName) {
                $packageUrl = $url;
            } elseif ($name === $checksumName) {
                $checksumUrl = $url;
            }
        }

        if ($packageUrl === '' || $checksumUrl === '') {
            throw new RuntimeException('O lançamento não contém o pacote e o checksum esperados.');
        }

        return [
            'version'      => $version,
            'release_url'  => $releaseUrl,
            'package_url'  => $packageUrl,
            'checksum_url' => $checksumUrl,
        ];
    }

    private static function installWithGit(string $pluginDir, string $version): void
    {
        if (!class_exists(Process::class)) {
            throw new RuntimeException('O componente necessário para executar o Git não está disponível.');
        }

        $status = trim(self::runGit($pluginDir, ['status', '--porcelain', '--untracked-files=all']));
        if ($status !== '') {
            throw new RuntimeException('A pasta Git possui alterações locais. Atualização automática cancelada.');
        }

        $remote = rtrim(trim(self::runGit($pluginDir, ['config', '--get', 'remote.origin.url'])), '/');
        if (!in_array($remote, self::OFFICIAL_REMOTES, true)) {
            throw new RuntimeException('O remote origin não aponta para o repositório oficial do ESO CSS.');
        }

        $tag = 'v' . $version;

        self::runGit(
            $pluginDir,
            ['fetch', '--force', 'origin', 'refs/tags/' . $tag . ':refs/tags/' . $tag],
            180
        );
        $targetCommit = trim(self::runGit($pluginDir, ['rev-parse', 'refs/tags/' . $tag . '^{commit}']));
        $targetSetup = self::runGit($pluginDir, ['show', $targetCommit . ':setup.php']);
        self::assertVersionContents($targetSetup, $version);

        $branch = trim(self::runGit($pluginDir, ['rev-parse', '--abbrev-ref', 'HEAD']));
        if ($branch === 'HEAD') {
            self::runGit($pluginDir, ['checkout', '--detach', $targetCommit], 120);
        } else {
            self::runGit($pluginDir, ['merge', '--ff-only', $targetCommit], 120);
        }
    }

    /**
     * @param array{version: string, release_url: string, package_url: string, checksum_url: string} $release
     */
    private static function installFromPackage(string $pluginDir, array $release): void
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('A extensão PHP Zip é necessária para a atualização automática.');
        }

        $pluginParent = dirname($pluginDir);
        if (!is_writable($pluginParent)) {
            throw new RuntimeException('A pasta de plugins não permite a troca segura de arquivos.');
        }

        $token = bin2hex(random_bytes(6));
        $archivePath = GLPI_TMP_DIR . '/esocss-update-' . $token . '.zip';
        $stageDir = $pluginParent . '/.esocss-update-' . $token;
        $backupDir = $pluginParent . '/.esocss-backup-' . $token;

        try {
            $expectedHash = self::downloadChecksum($release['checksum_url']);
            self::downloadFile($release['package_url'], $archivePath);
            $actualHash = hash_file('sha256', $archivePath);

            if (!is_string($actualHash) || !hash_equals($expectedHash, strtolower($actualHash))) {
                throw new RuntimeException('O checksum SHA-256 do pacote não confere.');
            }

            if (!@mkdir($stageDir, 0755, true) && !is_dir($stageDir)) {
                throw new RuntimeException('Não foi possível criar a pasta temporária da atualização.');
            }

            self::extractVerifiedArchive($archivePath, $stageDir);
            $newPluginDir = $stageDir . '/esocss';
            self::assertInstalledVersion($newPluginDir, $release['version']);

            if (!@rename($pluginDir, $backupDir)) {
                throw new RuntimeException('Não foi possível criar o backup temporário do plugin.');
            }

            if (!@rename($newPluginDir, $pluginDir)) {
                @rename($backupDir, $pluginDir);
                throw new RuntimeException('Não foi possível ativar os novos arquivos; o backup foi restaurado.');
            }

            try {
                self::assertInstalledVersion($pluginDir, $release['version']);
            } catch (Throwable $exception) {
                self::deleteTree($pluginDir);
                @rename($backupDir, $pluginDir);
                throw new RuntimeException('A validação final falhou; o backup foi restaurado.', 0, $exception);
            }

            self::deleteTree($backupDir);
        } finally {
            if (is_file($archivePath)) {
                @unlink($archivePath);
            }
            if (is_dir($stageDir)) {
                self::deleteTree($stageDir);
            }
        }
    }

    private static function downloadChecksum(string $url): string
    {
        $response = self::httpClient()->request('GET', $url, ['http_errors' => false]);
        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('Não foi possível baixar o checksum do pacote.');
        }

        $body = trim((string) $response->getBody());
        if (strlen($body) > 1024 || preg_match('/\b([a-f0-9]{64})\b/i', $body, $matches) !== 1) {
            throw new RuntimeException('O arquivo de checksum é inválido.');
        }

        return strtolower($matches[1]);
    }

    private static function downloadFile(string $url, string $destination): void
    {
        try {
            $response = self::httpClient()->request('GET', $url, [
                'http_errors' => false,
                'sink'        => $destination,
            ]);
        } catch (Throwable $exception) {
            throw new RuntimeException('Não foi possível baixar o pacote de atualização.', 0, $exception);
        }

        if ($response->getStatusCode() !== 200 || !is_file($destination)) {
            throw new RuntimeException('O download do pacote não foi concluído.');
        }

        $size = filesize($destination);
        if (!is_int($size) || $size < 1 || $size > 20 * 1024 * 1024) {
            @unlink($destination);
            throw new RuntimeException('O tamanho do pacote de atualização não é permitido.');
        }
    }

    private static function httpClient(): object
    {
        return \Toolbox::getGuzzleClient([
            'connect_timeout' => 10,
            'timeout'         => 120,
            'allow_redirects' => [
                'max'       => 5,
                'strict'    => true,
                'protocols' => ['https'],
            ],
            'headers' => [
                'Accept'     => 'application/octet-stream',
                'User-Agent' => 'ESO-CSS-for-GLPI/' . self::currentVersion(),
            ],
        ]);
    }

    private static function extractVerifiedArchive(string $archivePath, string $stageDir): void
    {
        $archive = new ZipArchive();
        if ($archive->open($archivePath) !== true) {
            throw new RuntimeException('O pacote ZIP não pôde ser aberto.');
        }

        try {
            if ($archive->numFiles < 1 || $archive->numFiles > 2000) {
                throw new RuntimeException('A quantidade de arquivos do pacote ZIP não é permitida.');
            }

            $uncompressedSize = 0;
            for ($index = 0; $index < $archive->numFiles; $index++) {
                $rawName = (string) $archive->getNameIndex($index);
                $name = str_replace('\\', '/', $rawName);
                $stat = $archive->statIndex($index);
                $uncompressedSize += is_array($stat) ? (int) ($stat['size'] ?? 0) : 0;
                $operatingSystem = 0;
                $externalAttributes = 0;
                $isSymlink = $archive->getExternalAttributesIndex(
                    $index,
                    $operatingSystem,
                    $externalAttributes
                ) && (($externalAttributes >> 16) & 0170000) === 0120000;
                if (
                    $name === ''
                    || str_contains($rawName, '\\')
                    || str_starts_with($name, '/')
                    || str_contains($name, "\0")
                    || preg_match('#(^|/)\.\.(/|$)#', $name) === 1
                    || ($name !== 'esocss' && !str_starts_with($name, 'esocss/'))
                    || $isSymlink
                    || $uncompressedSize > 50 * 1024 * 1024
                ) {
                    throw new RuntimeException('O pacote contém um caminho não permitido.');
                }
            }

            if (!$archive->extractTo($stageDir)) {
                throw new RuntimeException('Não foi possível extrair o pacote de atualização.');
            }
        } finally {
            $archive->close();
        }
    }

    private static function deleteTree(string $path): void
    {
        if (is_link($path) || is_file($path)) {
            if (!@unlink($path) && file_exists($path)) {
                throw new RuntimeException('Não foi possível remover um arquivo temporário da atualização.');
            }
            return;
        }

        if (!is_dir($path)) {
            return;
        }

        $iterator = new \FilesystemIterator($path, \FilesystemIterator::SKIP_DOTS);
        foreach ($iterator as $item) {
            self::deleteTree($item->getPathname());
        }

        if (!@rmdir($path) && is_dir($path)) {
            throw new RuntimeException('Não foi possível remover uma pasta temporária da atualização.');
        }
    }

    private static function assertInstalledVersion(string $pluginDir, string $expectedVersion): void
    {
        $setupPath = $pluginDir . '/setup.php';
        if (!is_file($setupPath)) {
            throw new RuntimeException('O pacote não contém setup.php.');
        }

        $contents = file_get_contents($setupPath);
        if (!is_string($contents)) {
            throw new RuntimeException('Não foi possível ler o setup.php do pacote.');
        }

        self::assertVersionContents($contents, $expectedVersion);
    }

    private static function assertVersionContents(string $contents, string $expectedVersion): void
    {
        if (
            preg_match(
                "/define\\(\\s*['\"]PLUGIN_ESOCSS_VERSION['\"]\\s*,\\s*['\"]([^'\"]+)['\"]\\s*\\)/",
                $contents,
                $matches
            ) !== 1
            || self::filterVersion($matches[1]) !== $expectedVersion
        ) {
            throw new RuntimeException('A versão declarada no pacote não corresponde ao lançamento.');
        }
    }

    private static function recordInstalledVersion(string $version, string $releaseUrl): void
    {
        \Config::setConfigurationValues(Settings::CONTEXT, [
            'version'               => $version,
            'update_latest_version' => $version,
            'update_checked_at'     => date('Y-m-d H:i:s'),
            'update_release_url'    => $releaseUrl,
        ]);

        $plugin = new \Plugin();
        if ($plugin->getFromDBbyDir('esocss')) {
            $plugin->update([
                'id'      => $plugin->fields['id'],
                'version' => $version,
                'state'   => $plugin->fields['state'],
            ]);
        }
    }

    private static function invalidateOpcache(string $pluginDir): void
    {
        if (!function_exists('opcache_invalidate')) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($pluginDir, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
                opcache_invalidate($file->getPathname(), true);
            }
        }
    }

    /**
     * @param list<string> $arguments
     */
    private static function runGit(string $pluginDir, array $arguments, int $timeout = 60): string
    {
        $process = new Process(array_merge(['git', '-C', $pluginDir], $arguments));
        $process->setTimeout($timeout);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException('O Git não conseguiu concluir a atualização.');
        }

        return $process->getOutput();
    }

    private static function filterVersion(string $version): string
    {
        $version = ltrim(trim($version), 'vV');
        return preg_match('/^\d+\.\d+\.\d+$/', $version) === 1 ? $version : '';
    }

    private static function filterReleaseUrl(string $url): string
    {
        return str_starts_with($url, self::RELEASE_URL_PREFIX) ? $url : '';
    }

    private static function filterAssetUrl(string $url, string $version): string
    {
        $prefix = self::RELEASE_URL_PREFIX . 'download/v' . $version . '/';
        return str_starts_with($url, $prefix) ? $url : '';
    }
}
