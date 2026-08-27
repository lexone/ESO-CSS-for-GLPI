<?php

final class Config
{
    public static array $values = [];

    public static function getConfigurationValues(string $context): array
    {
        return self::$values[$context] ?? [];
    }
}

require_once __DIR__ . '/../src/Settings.php';

use GlpiPlugin\EsoCss\Settings;
use GlpiPlugin\EsoCss\MediaManager;

function assertSameValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(
            STDERR,
            sprintf("%s\nEsperado: %s\nRecebido: %s\n", $message, var_export($expected, true), var_export($actual, true))
        );
        exit(1);
    }
}

$defaults = Settings::defaults();
$settingsPage = file_get_contents(__DIR__ . '/../front/settings.php');
assertSameValue(true, str_contains($settingsPage, 'name="login_style"'), 'A interface deve exibir o seletor de estilo do login.');
assertSameValue(true, str_contains($settingsPage, 'Vidro central'), 'A interface deve oferecer o modelo Vidro central.');
assertSameValue(true, str_contains($settingsPage, 'Portal lateral'), 'A interface deve oferecer o modelo Portal lateral.');
assertSameValue('#FFFFFF', $defaults['menu_background'], 'O menu precisa de uma cor de fundo padrão.');
assertSameValue('#374151', $defaults['menu_text_color'], 'O menu precisa de texto legível por padrão.');
assertSameValue('#FFFFFF', $defaults['button_text_color'], 'Botões principais precisam de texto legível por padrão.');
assertSameValue('720', $defaults['login_panel_width'], 'O painel interno do login precisa de uma largura segura.');
assertSameValue('classic', $defaults['login_style'], 'O estilo atual deve ser preservado por padrão.');
assertSameValue('panel', $defaults['login_image_mode'], 'Novas instalações devem usar a imagem em painel separado.');
assertSameValue('center', $defaults['login_layout'], 'O login deve começar centralizado.');
assertSameValue('65', $defaults['login_media_width'], 'O painel de imagem precisa de uma largura equilibrada.');
assertSameValue('0', $defaults['home_enabled'], 'A página inicial deve preservar o visual nativo por padrão.');
assertSameValue('0', $defaults['login_enabled'], 'A tela de login deve preservar o visual nativo por padrão.');
assertSameValue('44', $defaults['brand_sidebar_logo_height'], 'A logo lateral deve ter uma altura segura por padrão.');
assertSameValue(true, MediaManager::isManagedFilename('home-background-a1b2c3d4e5f6.webp'), 'Uma imagem gerenciada válida deve ser aceita.');
assertSameValue(true, MediaManager::isManagedFilename('login-logo-a1b2c3d4e5f6.png'), 'Um logotipo de login gerenciado deve ser aceito.');
assertSameValue(true, MediaManager::isManagedFilename('brand-sidebar-compact-logo-a1b2c3d4e5f6.png'), 'A logo compacta gerenciada deve ser aceita.');
assertSameValue(true, MediaManager::isManagedFilename('brand-favicon-a1b2c3d4e5f6.webp'), 'O ícone gerenciado deve ser aceito.');
assertSameValue(false, MediaManager::isManagedFilename('../imagem.php'), 'Caminhos externos devem ser rejeitados.');

Config::$values[Settings::CONTEXT] = [
    'primary_color' => '#123456',
    'primary_hover' => '#654321',
];
$legacy = Settings::get();
assertSameValue('#123456', $legacy['button_background'], 'Atualizações devem preservar a cor principal anterior dos botões.');
assertSameValue('#654321', $legacy['button_hover_background'], 'Atualizações devem preservar o hover anterior dos botões.');
assertSameValue('background', $legacy['login_image_mode'], 'Atualizações devem preservar a imagem de fundo anterior.');

$sanitized = Settings::sanitize([
    'theme_enabled'         => '1',
    'primary_color'         => '#abcdef',
    'button_background'     => '#123abc',
    'button_text_color'     => 'white',
    'button_hover_background' => '#456def',
    'button_hover_text_color' => '#101010',
    'menu_background'       => 'white',
    'border_radius'         => '99',
    'shadow_strength'       => 'inválido',
    'bar_radius'            => '-5',
    'bar_max_width'         => '4',
    'home_enabled'          => '1',
    'home_overlay_opacity'  => '120',
    'home_banner_height'    => '100',
    'home_title_size'       => '80',
    'home_title'            => '<b>Central</b> de Ajuda',
    'home_subtitle'         => "  Atendimento\nInstitucional  ",
    'home_background_position' => 'diagonal',
    'login_enabled'          => '1',
    'login_overlay_opacity'  => '99',
    'login_card_opacity'     => '20',
    'login_card_width'       => '2000',
    'login_panel_width'      => '20',
    'login_media_width'      => '90',
    'login_card_radius'      => '-2',
    'login_logo_max_height'  => '300',
    'brand_sidebar_logo_height' => '200',
    'brand_sidebar_compact_size' => '10',
    'brand_header_logo_height' => 'inválido',
    'login_title'            => '<script>erro</script> Logon Único',
    'login_subtitle'         => "  Acesso\nInstitucional  ",
    'login_sso_button_text'  => '<b>Entrar</b> com conta corporativa',
    'login_user_placeholder' => "  usuario\ncorporativo  ",
    'login_footer_text'      => '<a>Central</a> de Serviços',
    'login_background_position' => 'diagonal',
    'login_style'            => 'portal',
    'login_image_mode'       => 'iframe',
    'login_layout'           => 'diagonal',
    'login_card_color'       => '#fefefe',
    'custom_css'            => '.card { opacity: .99; }',
]);

assertSameValue('1', $sanitized['theme_enabled'], 'O tema deveria permanecer ativado.');
assertSameValue('0', $sanitized['chart_enabled'], 'Checkboxes ausentes devem ser desativados.');
assertSameValue('#ABCDEF', $sanitized['primary_color'], 'Cores válidas devem ser normalizadas.');
assertSameValue('#123ABC', $sanitized['button_background'], 'O fundo do botão deve aceitar uma cor válida.');
assertSameValue('#FFFFFF', $sanitized['button_text_color'], 'Texto inválido do botão deve usar o padrão legível.');
assertSameValue('#456DEF', $sanitized['button_hover_background'], 'O hover do botão deve aceitar uma cor válida.');
assertSameValue('#101010', $sanitized['button_hover_text_color'], 'O texto em hover deve aceitar uma cor válida.');
assertSameValue('#FFFFFF', $sanitized['menu_background'], 'Cores inválidas devem voltar ao padrão.');
assertSameValue('30', $sanitized['border_radius'], 'Valores numéricos devem respeitar o limite máximo.');
assertSameValue('8', $sanitized['shadow_strength'], 'Valores não numéricos devem usar o padrão.');
assertSameValue('0', $sanitized['bar_radius'], 'Valores numéricos devem respeitar o limite mínimo.');
assertSameValue('8', $sanitized['bar_max_width'], 'A largura de barra deve respeitar o limite mínimo.');
assertSameValue('1', $sanitized['home_enabled'], 'A personalização da página inicial deveria permanecer ativada.');
assertSameValue('90', $sanitized['home_overlay_opacity'], 'A opacidade deve respeitar o limite máximo.');
assertSameValue('240', $sanitized['home_banner_height'], 'A altura do banner deve respeitar o limite mínimo.');
assertSameValue('72', $sanitized['home_title_size'], 'O título deve respeitar o tamanho máximo.');
assertSameValue('Central de Ajuda', $sanitized['home_title'], 'O título não deve aceitar HTML.');
assertSameValue('Atendimento Institucional', $sanitized['home_subtitle'], 'Espaços do subtítulo devem ser normalizados.');
assertSameValue('center', $sanitized['home_background_position'], 'Posições inválidas devem usar o centro.');
assertSameValue('1', $sanitized['login_enabled'], 'A personalização do login deveria permanecer ativada.');
assertSameValue('90', $sanitized['login_overlay_opacity'], 'A sobreposição do login deve respeitar o limite máximo.');
assertSameValue('70', $sanitized['login_card_opacity'], 'A opacidade do cartão deve respeitar o limite mínimo.');
assertSameValue('1200', $sanitized['login_card_width'], 'A largura do login deve respeitar o limite máximo.');
assertSameValue('320', $sanitized['login_panel_width'], 'O painel interno deve respeitar o limite mínimo.');
assertSameValue('75', $sanitized['login_media_width'], 'O painel de imagem deve respeitar o limite máximo.');
assertSameValue('0', $sanitized['login_card_radius'], 'O raio do login deve respeitar o limite mínimo.');
assertSameValue('180', $sanitized['login_logo_max_height'], 'O logotipo do login deve respeitar o limite máximo.');
assertSameValue('96', $sanitized['brand_sidebar_logo_height'], 'A logo lateral deve respeitar o limite máximo.');
assertSameValue('24', $sanitized['brand_sidebar_compact_size'], 'A logo compacta deve respeitar o limite mínimo.');
assertSameValue('36', $sanitized['brand_header_logo_height'], 'A altura inválida do cabeçalho deve voltar ao padrão.');
assertSameValue('erro Logon Único', $sanitized['login_title'], 'O título do login não deve aceitar HTML.');
assertSameValue('Acesso Institucional', $sanitized['login_subtitle'], 'O subtítulo do login deve ser normalizado.');
assertSameValue('Entrar com conta corporativa', $sanitized['login_sso_button_text'], 'O texto do SSO não deve aceitar HTML.');
assertSameValue('usuario corporativo', $sanitized['login_user_placeholder'], 'O exemplo de usuário deve ser normalizado.');
assertSameValue('Central de Serviços', $sanitized['login_footer_text'], 'O rodapé não deve aceitar HTML.');
assertSameValue('center', $sanitized['login_background_position'], 'A posição inválida do login deve usar o centro.');
assertSameValue('portal', $sanitized['login_style'], 'O modelo lateral deve ser aceito.');
assertSameValue('panel', $sanitized['login_image_mode'], 'Modos de imagem inválidos devem usar o painel separado.');
assertSameValue('center', $sanitized['login_layout'], 'Posições inválidas do login devem usar o centro.');
assertSameValue('#FEFEFE', $sanitized['login_card_color'], 'As cores do login devem ser normalizadas.');

Config::$values[Settings::CONTEXT] = array_merge($defaults, $sanitized);
$payload = Settings::publicPayload();
assertSameValue(true, $payload['home']['enabled'], 'A configuração pública deve ativar a página inicial.');
assertSameValue('Central de Ajuda', $payload['home']['title'], 'A configuração pública deve expor o título sanitizado.');
assertSameValue('', $payload['home']['background_url'], 'Sem arquivo gerenciado não deve existir URL pública de imagem.');
assertSameValue('', $payload['branding']['sidebar_url'], 'Sem arquivo não deve existir URL pública da logo lateral.');
assertSameValue(96, $payload['branding']['sidebar_height'], 'A altura pública da logo lateral deve ser numérica.');
assertSameValue('#123ABC', $payload['colors']['button_background'], 'A interface deve receber a cor configurada do botão.');
assertSameValue('#FFFFFF', $payload['colors']['button_text'], 'A interface deve receber a cor legível do texto do botão.');

$loginPayload = Settings::publicLoginPayload();
assertSameValue(true, $loginPayload['enabled'], 'A configuração pública deve ativar a tela de login.');
assertSameValue('erro Logon Único', $loginPayload['title'], 'O login deve expor apenas o título sanitizado.');
assertSameValue('Entrar com conta corporativa', $loginPayload['texts']['sso_button'], 'O login deve expor o texto SSO sanitizado.');
assertSameValue('Central de Serviços', $loginPayload['texts']['footer'], 'O login deve expor o rodapé sanitizado.');
assertSameValue(1200, $loginPayload['card_width'], 'A largura pública do login deve ser numérica.');
assertSameValue(320, $loginPayload['panel_width'], 'A largura pública do painel interno deve ser numérica.');
assertSameValue(75, $loginPayload['media_width'], 'A largura pública do painel de imagem deve ser numérica.');
assertSameValue('panel', $loginPayload['image_mode'], 'O modo público da imagem deve ser sanitizado.');
assertSameValue('portal', $loginPayload['style'], 'O modelo público do login deve ser sanitizado.');
assertSameValue('center', $loginPayload['layout'], 'A posição pública do login deve ser sanitizada.');
assertSameValue('', $loginPayload['logo_url'], 'Sem arquivo gerenciado não deve existir URL pública do logotipo.');
assertSameValue('', $loginPayload['favicon_url'], 'Sem arquivo gerenciado não deve existir favicon público.');
assertSameValue(false, array_key_exists('custom_css', $loginPayload), 'CSS administrativo não pode ser exposto no login anônimo.');

$invalidStyle = Settings::sanitize(['login_style' => 'iframe']);
assertSameValue('classic', $invalidStyle['login_style'], 'Modelos de login inválidos devem usar o estilo compatível.');

fwrite(STDOUT, "Settings smoke test: OK\n");
