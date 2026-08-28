<?php

include __DIR__ . '/../../../inc/includes.php';
require_once __DIR__ . '/../src/Settings.php';
require_once __DIR__ . '/../src/Updater.php';

use GlpiPlugin\EsoCss\Settings;
use GlpiPlugin\EsoCss\MediaManager;
use GlpiPlugin\EsoCss\Updater;

Session::checkRight('config', UPDATE);
Plugin::load('esocss');

$config = Settings::get();
$homeBackgroundUrl = MediaManager::publicUrl($config['home_background_image']);
$homeLogoUrl = MediaManager::publicUrl($config['home_logo_image']);
$loginBackgroundUrl = MediaManager::publicUrl($config['login_background_image']);
$loginLogoUrl = MediaManager::publicUrl($config['login_logo_image']);
$brandSidebarLogoUrl = MediaManager::publicUrl($config['brand_sidebar_logo_image']);
$brandSidebarCompactLogoUrl = MediaManager::publicUrl($config['brand_sidebar_compact_logo_image']);
$brandHeaderLogoUrl = MediaManager::publicUrl($config['brand_header_logo_image']);
$brandFaviconUrl = MediaManager::publicUrl($config['brand_favicon_image']);
$updateState = Updater::getCachedState();
$rootDoc = $CFG_GLPI['root_doc'] ?? '';
$settingsFormUrl = $rootDoc . '/plugins/esocss/front/settings.form.php';
$updateFormUrl = $rootDoc . '/plugins/esocss/front/update.form.php';

Html::header('ESO CSS for GLPI', $_SERVER['PHP_SELF'], 'config', 'plugins');

function esocss_color_field(string $name, string $label, string $value): void
{
    $safeName  = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $safeValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

    echo <<<HTML
    <div class="col-md-6 col-xl-4 mb-3 eso-color-field">
        <label class="form-label fw-semibold" for="{$safeName}">{$safeLabel}</label>
        <div class="input-group">
            <span class="input-group-text p-1">
                <input type="color" class="form-control form-control-color border-0 eso-color-picker"
                       data-target="{$safeName}" value="{$safeValue}" title="{$safeLabel}">
            </span>
            <input type="text" class="form-control eso-color-text" id="{$safeName}" name="{$safeName}"
                   value="{$safeValue}" maxlength="7" pattern="#[0-9A-Fa-f]{6}">
        </div>
    </div>
    HTML;
}

function esocss_toggle(string $name, string $label, bool $checked, string $help = ''): void
{
    $safeName  = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $checkedAttr = $checked ? 'checked' : '';
    $helpHtml = $help !== '' ? '<div class="form-hint">' . htmlspecialchars($help, ENT_QUOTES, 'UTF-8') . '</div>' : '';

    echo <<<HTML
    <div class="col-md-6 mb-3">
        <label class="form-check form-switch">
            <input type="hidden" name="{$safeName}" value="0">
            <input class="form-check-input" type="checkbox" name="{$safeName}" value="1" {$checkedAttr}>
            <span class="form-check-label fw-semibold">{$safeLabel}</span>
        </label>
        {$helpHtml}
    </div>
    HTML;
}

function esocss_text_field(
    string $name,
    string $label,
    string $value,
    string $placeholder = '',
    int $maxlength = 160,
    string $columns = 'col-md-6'
): void {
    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $safeValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    $safePlaceholder = htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8');
    $safeColumns = htmlspecialchars($columns, ENT_QUOTES, 'UTF-8');

    echo <<<HTML
    <div class="{$safeColumns} mb-3">
        <label class="form-label fw-semibold" for="{$safeName}">{$safeLabel}</label>
        <input class="form-control eso-login-text-input" type="text" maxlength="{$maxlength}"
               id="{$safeName}" name="{$safeName}" value="{$safeValue}" placeholder="{$safePlaceholder}">
    </div>
    HTML;
}

function esocss_image_field(string $slot, string $label, string $url, string $help): void
{
    $safeSlot = htmlspecialchars($slot, ENT_QUOTES, 'UTF-8');
    $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $safeHelp = htmlspecialchars($help, ENT_QUOTES, 'UTF-8');
    $remove = 'remove_' . $safeSlot;
    $file = $safeSlot . '_file';
    $hasImage = $url !== '';

    echo '<div class="col-md-6 mb-3">';
    echo '<label class="form-label fw-semibold" for="' . $file . '">' . $safeLabel . '</label>';
    echo '<input class="form-control" type="file" accept="image/jpeg,image/png,image/webp" id="' . $file
        . '" name="' . $file . '">';
    echo '<div class="form-hint">' . $safeHelp . ' JPG, PNG ou WebP, até 5 MB.</div>';
    if ($hasImage) {
        echo '<label class="form-check mt-2">';
        echo '<input class="form-check-input" type="checkbox" id="' . $remove . '" name="' . $remove . '" value="1">';
        echo '<span class="form-check-label">Remover imagem atual</span>';
        echo '</label>';
    }
    echo '</div>';
}
?>

<div class="container-xl py-3" id="esocss-config-page">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h1 class="h2 mb-1"><i class="ti ti-palette me-2"></i>ESO CSS for GLPI</h1>
            <p class="text-muted mb-0">Personalize cores, cards e gráficos ECharts do GLPI 11 sem editar o core.</p>
        </div>
        <span class="badge bg-blue-lt">v<?= htmlspecialchars(defined('PLUGIN_ESOCSS_VERSION') ? PLUGIN_ESOCSS_VERSION : '1.9.3') ?></span>
    </div>

    <form method="post" action="<?= htmlspecialchars($settingsFormUrl, ENT_QUOTES, 'UTF-8') ?>" autocomplete="off" enctype="multipart/form-data">
        <?= Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]) ?>

        <div class="row g-3">
            <div class="col-12 col-xl-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Comportamento</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php
                            esocss_toggle('theme_enabled', 'Ativar tema visual', $config['theme_enabled'] === '1');
                            esocss_toggle('chart_enabled', 'Modernizar gráficos ECharts', $config['chart_enabled'] === '1', 'Aplica a paleta abaixo aos gráficos de pizza, donut, barras e linhas.');
                            esocss_toggle('card_hover', 'Animação nos cards', $config['card_hover'] === '1');
                            esocss_toggle('header_dark', 'Cabeçalho escuro', $config['header_dark'] === '1');
                            ?>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title">Temas rápidos</h3></div>
                    <div class="card-body">
                        <p class="text-muted mb-3">Use um ponto de partida e ajuste cada cor abaixo antes de salvar.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-primary eso-preset" data-preset="eso">ESO Azul</button>
                            <button type="button" class="btn btn-outline-secondary eso-preset" data-preset="oceano">Oceano</button>
                            <button type="button" class="btn btn-outline-secondary eso-preset" data-preset="grafite">Grafite</button>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-brand-tabler me-2"></i>Logotipos e identidade global</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Troque as imagens de marca do GLPI sem alterar o núcleo. Se um campo ficar vazio, o logotipo
                            nativo permanece. A logo específica da página inicial ou do login continua tendo prioridade
                            nesses dois locais.
                        </p>

                        <div class="row">
                            <?php
                            esocss_image_field(
                                'brand_sidebar_logo',
                                'Logo do menu lateral aberto',
                                $brandSidebarLogoUrl,
                                'Recomendado: imagem horizontal transparente, aproximadamente 260 × 64 px.'
                            );
                            esocss_image_field(
                                'brand_sidebar_compact_logo',
                                'Logo do menu lateral recolhido',
                                $brandSidebarCompactLogoUrl,
                                'Recomendado: símbolo quadrado transparente, aproximadamente 64 × 64 px. Vazio reutiliza a logo lateral.'
                            );
                            esocss_image_field(
                                'brand_header_logo',
                                'Logo do cabeçalho e Helpdesk',
                                $brandHeaderLogoUrl,
                                'Recomendado: imagem horizontal transparente, aproximadamente 260 × 64 px.'
                            );
                            esocss_image_field(
                                'brand_favicon',
                                'Ícone da aba do navegador',
                                $brandFaviconUrl,
                                'Recomendado: imagem quadrada PNG ou WebP, 64 × 64 px.'
                            );
                            ?>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="brand_sidebar_logo_height">Altura no menu aberto</label>
                                <div class="input-group">
                                    <input class="form-control" type="number" min="24" max="96" id="brand_sidebar_logo_height"
                                           name="brand_sidebar_logo_height" value="<?= (int) $config['brand_sidebar_logo_height'] ?>">
                                    <span class="input-group-text">px</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="brand_sidebar_compact_size">Tamanho no menu recolhido</label>
                                <div class="input-group">
                                    <input class="form-control" type="number" min="24" max="64" id="brand_sidebar_compact_size"
                                           name="brand_sidebar_compact_size" value="<?= (int) $config['brand_sidebar_compact_size'] ?>">
                                    <span class="input-group-text">px</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="brand_header_logo_height">Altura no cabeçalho</label>
                                <div class="input-group">
                                    <input class="form-control" type="number" min="24" max="72" id="brand_header_logo_height"
                                           name="brand_header_logo_height" value="<?= (int) $config['brand_header_logo_height'] ?>">
                                    <span class="input-group-text">px</span>
                                </div>
                            </div>
                        </div>

                        <div id="eso-brand-preview" class="eso-brand-preview"
                             data-sidebar-url="<?= htmlspecialchars($brandSidebarLogoUrl, ENT_QUOTES, 'UTF-8') ?>"
                             data-compact-url="<?= htmlspecialchars($brandSidebarCompactLogoUrl, ENT_QUOTES, 'UTF-8') ?>"
                             data-header-url="<?= htmlspecialchars($brandHeaderLogoUrl, ENT_QUOTES, 'UTF-8') ?>"
                             data-favicon-url="<?= htmlspecialchars($brandFaviconUrl, ENT_QUOTES, 'UTF-8') ?>">
                            <div class="eso-brand-preview-header">
                                <span class="eso-brand-preview-favicon-wrap">
                                    <img class="eso-brand-preview-favicon d-none" alt="Prévia do ícone da aba">
                                    <i class="ti ti-world eso-brand-preview-favicon-fallback"></i>
                                </span>
                                <span class="text-truncate">helpdesk.exemplo.org</span>
                                <span class="eso-brand-preview-header-logo-wrap ms-auto">
                                    <img class="eso-brand-preview-header-logo d-none" alt="Prévia da logo do cabeçalho">
                                    <strong class="eso-brand-preview-header-fallback">GLPI</strong>
                                </span>
                            </div>
                            <div class="eso-brand-preview-layout">
                                <aside class="eso-brand-preview-sidebar-open">
                                    <img class="eso-brand-preview-sidebar-logo d-none" alt="Prévia da logo lateral">
                                    <strong class="eso-brand-preview-sidebar-fallback">GLPI</strong>
                                    <span></span><span></span><span></span>
                                </aside>
                                <aside class="eso-brand-preview-sidebar-compact">
                                    <img class="eso-brand-preview-compact-logo d-none" alt="Prévia da logo compacta">
                                    <strong class="eso-brand-preview-compact-fallback">G</strong>
                                    <i></i><i></i><i></i>
                                </aside>
                                <div class="eso-brand-preview-content">
                                    <strong>Prévia da identidade</strong>
                                    <small>Menu aberto, menu recolhido, cabeçalho e aba do navegador.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-home-edit me-2"></i>Página inicial do Helpdesk</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Personaliza o banner da tela de autosserviço sem editar o core. As duas ilustrações laterais
                            continuam disponíveis na configuração nativa da entidade do GLPI.
                        </p>

                        <div class="row">
                            <?php
                            esocss_toggle('home_enabled', 'Ativar personalização da página inicial', $config['home_enabled'] === '1');
                            esocss_toggle('home_hide_scenes', 'Ocultar ilustrações laterais do GLPI', $config['home_hide_scenes'] === '1');
                            ?>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="home_title">Título personalizado</label>
                                <input class="form-control" type="text" maxlength="160" id="home_title" name="home_title"
                                       value="<?= htmlspecialchars($config['home_title'], ENT_QUOTES, 'UTF-8') ?>"
                                       placeholder="Deixe vazio para usar o título configurado na entidade">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="home_subtitle">Subtítulo</label>
                                <input class="form-control" type="text" maxlength="280" id="home_subtitle" name="home_subtitle"
                                       value="<?= htmlspecialchars($config['home_subtitle'], ENT_QUOTES, 'UTF-8') ?>"
                                       placeholder="Ex.: Encontre respostas ou abra uma solicitação">
                            </div>
                        </div>

                        <div class="row">
                            <?php
                            esocss_color_field('home_overlay_color', 'Cor da sobreposição', $config['home_overlay_color']);
                            esocss_color_field('home_title_color', 'Cor do título', $config['home_title_color']);
                            ?>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold" for="home_overlay_opacity">Sobreposição (%)</label>
                                <input class="form-control" type="number" min="0" max="90" id="home_overlay_opacity"
                                       name="home_overlay_opacity" value="<?= (int) $config['home_overlay_opacity'] ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold" for="home_banner_height">Altura do banner</label>
                                <input class="form-control" type="number" min="240" max="720" id="home_banner_height"
                                       name="home_banner_height" value="<?= (int) $config['home_banner_height'] ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold" for="home_title_size">Tamanho do título</label>
                                <input class="form-control" type="number" min="24" max="72" id="home_title_size"
                                       name="home_title_size" value="<?= (int) $config['home_title_size'] ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold" for="home_logo_max_height">Altura do logotipo</label>
                                <input class="form-control" type="number" min="20" max="96" id="home_logo_max_height"
                                       name="home_logo_max_height" value="<?= (int) $config['home_logo_max_height'] ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="home_background_position">Posição da imagem</label>
                                <select class="form-select" id="home_background_position" name="home_background_position">
                                    <?php
                                    $positions = [
                                        'center' => 'Centro', 'top' => 'Topo', 'bottom' => 'Base',
                                        'left' => 'Esquerda', 'right' => 'Direita',
                                    ];
                                    foreach ($positions as $value => $label):
                                    ?>
                                        <option value="<?= $value ?>" <?= $config['home_background_position'] === $value ? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="home_background_file">Imagem de fundo</label>
                                <input class="form-control" type="file" id="home_background_file" name="home_background_file"
                                       accept="image/jpeg,image/png,image/webp">
                                <div class="form-hint">JPG, PNG ou WebP, até 5 MB. Recomendado: 1920 × 700 px.</div>
                                <?php if ($homeBackgroundUrl !== ''): ?>
                                    <label class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="remove_home_background"
                                               name="remove_home_background" value="1">
                                        <span class="form-check-label">Remover imagem de fundo atual</span>
                                    </label>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="home_logo_file">Logotipo do cabeçalho</label>
                                <input class="form-control" type="file" id="home_logo_file" name="home_logo_file"
                                       accept="image/jpeg,image/png,image/webp">
                                <div class="form-hint">Prefira PNG ou WebP transparente, até 5 MB.</div>
                                <?php if ($homeLogoUrl !== ''): ?>
                                    <label class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="remove_home_logo"
                                               name="remove_home_logo" value="1">
                                        <span class="form-check-label">Remover logotipo atual</span>
                                    </label>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div id="eso-home-preview" class="eso-home-preview"
                             data-background-url="<?= htmlspecialchars($homeBackgroundUrl, ENT_QUOTES, 'UTF-8') ?>"
                             data-logo-url="<?= htmlspecialchars($homeLogoUrl, ENT_QUOTES, 'UTF-8') ?>">
                            <div class="eso-home-preview-overlay">
                                <div class="eso-home-preview-logo-wrap">
                                    <img class="eso-home-preview-logo <?= $homeLogoUrl === '' ? 'd-none' : '' ?>"
                                         src="<?= htmlspecialchars($homeLogoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Prévia do logotipo">
                                    <span class="eso-home-preview-logo-fallback <?= $homeLogoUrl !== '' ? 'd-none' : '' ?>">ESO / GLPI</span>
                                </div>
                                <div class="eso-home-preview-copy">
                                    <strong class="eso-home-preview-title">
                                        <?= htmlspecialchars($config['home_title'] !== '' ? $config['home_title'] : 'Como podemos ajudá-lo?', ENT_QUOTES, 'UTF-8') ?>
                                    </strong>
                                    <span class="eso-home-preview-subtitle"><?= htmlspecialchars($config['home_subtitle'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <div class="eso-home-preview-search"><i class="ti ti-search"></i> Pesquisar soluções e formulários</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-login-2 me-2"></i>Tela de login</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Personaliza a página anterior à autenticação, inclusive quando o Entra ID ou outro provedor
                            de login único estiver ativo. O plugin não altera botões, endereços nem regras de autenticação.
                        </p>

                        <div class="row">
                            <?php
                            esocss_toggle(
                                'login_enabled',
                                'Ativar personalização da tela de login',
                                $config['login_enabled'] === '1',
                                'A configuração é carregada pelo gancho oficial do GLPI, sem endpoint público adicional.'
                            );
                            ?>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="login_title">Título do login</label>
                                <input class="form-control" type="text" maxlength="160" id="login_title" name="login_title"
                                       value="<?= htmlspecialchars($config['login_title'], ENT_QUOTES, 'UTF-8') ?>"
                                       placeholder="Ex.: Logon Único (vazio mantém o texto atual)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="login_subtitle">Subtítulo</label>
                                <input class="form-control" type="text" maxlength="280" id="login_subtitle" name="login_subtitle"
                                       value="<?= htmlspecialchars($config['login_subtitle'], ENT_QUOTES, 'UTF-8') ?>"
                                       placeholder="Ex.: Acesse com sua conta institucional">
                            </div>
                        </div>

                        <h4 class="h5 mt-4 mb-2">Textos do login único</h4>
                        <p class="small text-muted">Campos vazios mantêm exatamente o texto fornecido pelo GLPI ou pelo provedor atual.</p>
                        <div class="row">
                            <?php
                            esocss_text_field('login_welcome_text', 'Mensagem de boas-vindas', $config['login_welcome_text'], 'Ex.: Utilize sua conta institucional', 280, 'col-12');
                            esocss_text_field('login_sso_button_text', 'Botão do Entra ID / SSO', $config['login_sso_button_text'], 'Ex.: Entrar com minha conta institucional');
                            esocss_text_field('login_remember_text', 'Opção de permanência', $config['login_remember_text'], 'Ex.: Permanecer conectado');
                            esocss_text_field('login_form_toggle_text', 'Link para o formulário do GLPI', $config['login_form_toggle_text'], 'Ex.: Usar usuário e senha');
                            ?>
                        </div>

                        <h4 class="h5 mt-3 mb-2">Textos do formulário nativo</h4>
                        <div class="row">
                            <?php
                            esocss_text_field('login_user_label', 'Rótulo do usuário', $config['login_user_label'], 'Ex.: E-mail ou usuário');
                            esocss_text_field('login_user_placeholder', 'Exemplo no campo usuário', $config['login_user_placeholder'], 'Ex.: nome.sobrenome');
                            esocss_text_field('login_password_label', 'Rótulo da senha', $config['login_password_label'], 'Ex.: Senha');
                            esocss_text_field('login_password_placeholder', 'Exemplo no campo senha', $config['login_password_placeholder'], 'Ex.: Digite sua senha');
                            esocss_text_field('login_source_label', 'Origem de autenticação', $config['login_source_label'], 'Ex.: Tipo de acesso');
                            esocss_text_field('login_submit_text', 'Botão de entrada', $config['login_submit_text'], 'Ex.: Entrar');
                            esocss_text_field('login_forgot_password_text', 'Recuperação de senha', $config['login_forgot_password_text'], 'Ex.: Esqueci minha senha');
                            esocss_text_field('login_faq_text', 'Link da FAQ', $config['login_faq_text'], 'Ex.: Central de ajuda');
                            esocss_text_field('login_footer_text', 'Texto do rodapé', $config['login_footer_text'], 'Ex.: Central de Serviços', 280, 'col-12');
                            ?>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="login_style">Estilo da tela de login</label>
                                <select class="form-select" id="login_style" name="login_style">
                                    <option value="classic" <?= $config['login_style'] === 'classic' ? 'selected' : '' ?>>Personalizado atual (compatibilidade)</option>
                                    <option value="glass" <?= $config['login_style'] === 'glass' ? 'selected' : '' ?>>Vidro</option>
                                    <option value="portal" <?= $config['login_style'] === 'portal' ? 'selected' : '' ?>>Portal lateral</option>
                                </select>
                                <div class="form-hint">
                                    Os novos estilos usam a foto em tela cheia. Logo, cores, textos e transparência continuam configuráveis abaixo.
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="login_image_mode">Uso da imagem</label>
                                <select class="form-select" id="login_image_mode" name="login_image_mode">
                                    <option value="panel" <?= $config['login_image_mode'] === 'panel' ? 'selected' : '' ?>>Painel separado (sem fundo)</option>
                                    <option value="background" <?= $config['login_image_mode'] === 'background' ? 'selected' : '' ?>>Fundo da página (legado)</option>
                                </select>
                                <div class="form-hint">No painel separado, a imagem ocupa uma área própria e não fica atrás do formulário.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="login_layout">Posição do login</label>
                                <select class="form-select" id="login_layout" name="login_layout">
                                    <option value="center" <?= $config['login_layout'] === 'center' ? 'selected' : '' ?>>Centralizado</option>
                                    <option value="left" <?= $config['login_layout'] === 'left' ? 'selected' : '' ?>>À esquerda</option>
                                    <option value="right" <?= $config['login_layout'] === 'right' ? 'selected' : '' ?>>À direita</option>
                                </select>
                                <div class="form-hint">No modo central, o formulário fica no centro e o painel de imagem é ocultado.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="login_media_width">Largura da imagem (%)</label>
                                <input class="form-control" type="number" min="45" max="75" id="login_media_width"
                                       name="login_media_width" value="<?= (int) $config['login_media_width'] ?>">
                                <div class="form-hint">Usado nas posições esquerda e direita. Recomendado: 65%.</div>
                            </div>
                        </div>

                        <div class="row">
                            <?php
                            esocss_color_field('login_background_color', 'Fundo da página', $config['login_background_color']);
                            esocss_color_field('login_overlay_color', 'Sobreposição da imagem', $config['login_overlay_color']);
                            esocss_color_field('login_card_color', 'Fundo do cartão', $config['login_card_color']);
                            esocss_color_field('login_text_color', 'Texto principal', $config['login_text_color']);
                            esocss_color_field('login_muted_color', 'Texto secundário', $config['login_muted_color']);
                            esocss_color_field('login_primary_color', 'Botões e links', $config['login_primary_color']);
                            esocss_color_field('login_border_color', 'Bordas', $config['login_border_color']);
                            ?>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4 col-xl-2">
                                <label class="form-label fw-semibold" for="login_overlay_opacity">Sobreposição (%)</label>
                                <input class="form-control" type="number" min="0" max="90" id="login_overlay_opacity"
                                       name="login_overlay_opacity" value="<?= (int) $config['login_overlay_opacity'] ?>">
                            </div>
                            <div class="col-md-4 col-xl-2">
                                <label class="form-label fw-semibold" for="login_card_opacity">Opacidade do cartão (%)</label>
                                <input class="form-control" type="number" min="20" max="100" id="login_card_opacity"
                                       name="login_card_opacity" value="<?= (int) $config['login_card_opacity'] ?>">
                            </div>
                            <div class="col-md-4 col-xl-2" id="login_glass_transparency_field">
                                <label class="form-label fw-semibold" for="login_glass_transparency">Transparência do vidro (%)</label>
                                <input class="form-control" type="number" min="0" max="80" id="login_glass_transparency"
                                       name="login_glass_transparency" value="<?= (int) $config['login_glass_transparency'] ?>">
                                <div class="form-hint">0% é sólido; 80% deixa a imagem mais visível.</div>
                            </div>
                            <div class="col-md-4 col-xl-2">
                                <label class="form-label fw-semibold" for="login_card_width">Largura externa (px)</label>
                                <input class="form-control" type="number" min="360" max="1200" id="login_card_width"
                                       name="login_card_width" value="<?= (int) $config['login_card_width'] ?>">
                            </div>
                            <div class="col-md-4 col-xl-2">
                                <label class="form-label fw-semibold" for="login_panel_width">Painel interno (px)</label>
                                <input class="form-control" type="number" min="320" max="1000" id="login_panel_width"
                                       name="login_panel_width" value="<?= (int) $config['login_panel_width'] ?>">
                            </div>
                            <div class="col-md-4 col-xl-2">
                                <label class="form-label fw-semibold" for="login_card_radius">Cantos (px)</label>
                                <input class="form-control" type="number" min="0" max="40" id="login_card_radius"
                                       name="login_card_radius" value="<?= (int) $config['login_card_radius'] ?>">
                            </div>
                            <div class="col-md-4 col-xl-2">
                                <label class="form-label fw-semibold" for="login_logo_max_height">Logo (px)</label>
                                <input class="form-control" type="number" min="24" max="180" id="login_logo_max_height"
                                       name="login_logo_max_height" value="<?= (int) $config['login_logo_max_height'] ?>">
                            </div>
                            <div class="col-md-8 col-xl-2">
                                <label class="form-label fw-semibold" for="login_background_position">Enquadramento da imagem</label>
                                <select class="form-select" id="login_background_position" name="login_background_position">
                                    <?php foreach ($positions as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= $config['login_background_position'] === $value ? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="login_background_file">Imagem do painel lateral / fundo legado</label>
                                <input class="form-control" type="file" id="login_background_file" name="login_background_file"
                                       accept="image/jpeg,image/png,image/webp">
                                <div class="form-hint">JPG, PNG ou WebP, até 5 MB. No modo separado, ela aparece ao lado do login.</div>
                                <?php if ($loginBackgroundUrl !== ''): ?>
                                    <label class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="remove_login_background"
                                               name="remove_login_background" value="1">
                                        <span class="form-check-label">Remover imagem de fundo atual</span>
                                    </label>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="login_logo_file">Logotipo da tela de login</label>
                                <input class="form-control" type="file" id="login_logo_file" name="login_logo_file"
                                       accept="image/jpeg,image/png,image/webp">
                                <div class="form-hint">Prefira PNG ou WebP transparente, até 5 MB.</div>
                                <?php if ($loginLogoUrl !== ''): ?>
                                    <label class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="remove_login_logo"
                                               name="remove_login_logo" value="1">
                                        <span class="form-check-label">Remover logotipo atual</span>
                                    </label>
                                <?php endif; ?>
                                <label class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="login_hide_default_logo"
                                           name="login_hide_default_logo" value="1" <?= $config['login_hide_default_logo'] === '1' ? 'checked' : '' ?>>
                                    <span class="form-check-label">Ocultar a logo padrão “GLPI”</span>
                                </label>
                                <div class="form-hint">Se houver uma logo personalizada, ela continuará visível.</div>
                            </div>
                        </div>

                        <div id="eso-login-preview" class="eso-login-preview"
                             data-background-url="<?= htmlspecialchars($loginBackgroundUrl, ENT_QUOTES, 'UTF-8') ?>"
                            data-logo-url="<?= htmlspecialchars($loginLogoUrl, ENT_QUOTES, 'UTF-8') ?>">
                            <div class="eso-login-preview-overlay">
                                <div class="eso-login-preview-media" aria-hidden="true"></div>
                                <div class="eso-login-preview-hero" aria-hidden="true">
                                    <span class="eso-login-preview-hero-accent"></span>
                                    <strong class="eso-login-preview-hero-title"></strong>
                                    <span class="eso-login-preview-hero-subtitle"></span>
                                </div>
                                <div class="eso-login-preview-content">
                                    <div class="eso-login-preview-shell">
                                    <div class="eso-login-preview-brand">
                                        <img class="eso-login-preview-logo <?= $loginLogoUrl === '' ? 'd-none' : '' ?>"
                                             src="<?= htmlspecialchars($loginLogoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Prévia do logotipo do login">
                                        <span class="eso-login-preview-logo-fallback <?= $loginLogoUrl !== '' ? 'd-none' : '' ?>">SUA MARCA / GLPI</span>
                                    </div>
                                    <div class="eso-login-preview-card">
                                        <strong class="eso-login-preview-title">
                                            <?= htmlspecialchars($config['login_title'] !== '' ? $config['login_title'] : 'Logon Único', ENT_QUOTES, 'UTF-8') ?>
                                        </strong>
                                        <span class="eso-login-preview-subtitle">
                                            <?= htmlspecialchars($config['login_subtitle'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        <span class="eso-login-preview-welcome"></span>
                                        <div class="eso-login-preview-panels">
                                            <div class="eso-login-preview-panel eso-login-preview-sso-panel">
                                                <div class="eso-login-preview-sso">
                                                    <i class="ti ti-lock"></i><span>Entrar com ENTRA ID</span><i class="ti ti-chevron-right"></i>
                                                </div>
                                                <div class="eso-login-preview-remember">
                                                    <i class="ti ti-shield-check"></i><span>Lembrar de mim</span>
                                                </div>
                                                <span class="eso-login-preview-form-toggle">Usar formulário de login do GLPI</span>
                                            </div>
                                            <div class="eso-login-preview-panel eso-login-preview-native">
                                                <label class="eso-login-preview-user-label">Login</label>
                                                <div class="eso-login-preview-input eso-login-preview-user-placeholder"></div>
                                                <label class="eso-login-preview-password-label">Senha</label>
                                                <div class="eso-login-preview-input eso-login-preview-password-placeholder"></div>
                                                <span class="eso-login-preview-source-label"></span>
                                                <div class="eso-login-preview-submit">Entrar</div>
                                                <div class="eso-login-preview-links">
                                                    <span class="eso-login-preview-forgot">Esqueceu sua senha?</span>
                                                    <span class="eso-login-preview-faq">FAQ</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                        <div class="eso-login-preview-footer">GLPI Copyright (C) 2015-2026</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title">Navegação e identidade</h3></div>
                    <div class="card-body">
                        <div class="row">
                            <?php
                            esocss_color_field('primary_color', 'Cor primária', $config['primary_color']);
                            esocss_color_field('primary_hover', 'Primária no hover', $config['primary_hover']);
                            esocss_color_field('sidebar_start', 'Sidebar - início', $config['sidebar_start']);
                            esocss_color_field('sidebar_end', 'Sidebar - fim', $config['sidebar_end']);
                            esocss_color_field('sidebar_text_color', 'Texto da sidebar', $config['sidebar_text_color']);
                            esocss_color_field('header_start', 'Cabeçalho - início', $config['header_start']);
                            esocss_color_field('header_end', 'Cabeçalho - fim', $config['header_end']);
                            esocss_color_field('header_text_color', 'Texto do cabeçalho', $config['header_text_color']);
                            ?>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-click me-2"></i>Botões principais</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Controla botões azuis como <strong>Salvar configuração</strong>, Atualizar e Confirmar.
                            Os botões da tela de login continuam usando a opção própria da seção Login.
                        </p>
                        <div class="row">
                            <?php
                            esocss_color_field('button_background', 'Fundo do botão', $config['button_background']);
                            esocss_color_field('button_text_color', 'Texto e ícone', $config['button_text_color']);
                            esocss_color_field('button_hover_background', 'Fundo ao passar o mouse', $config['button_hover_background']);
                            esocss_color_field('button_hover_text_color', 'Texto ao passar o mouse', $config['button_hover_text_color']);
                            ?>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title">Superfícies, textos e menus</h3></div>
                    <div class="card-body">
                        <div class="row">
                            <?php
                            esocss_color_field('background_color', 'Fundo geral', $config['background_color']);
                            esocss_color_field('card_color', 'Fundo dos cards', $config['card_color']);
                            esocss_color_field('text_color', 'Texto principal', $config['text_color']);
                            esocss_color_field('muted_color', 'Texto secundário', $config['muted_color']);
                            esocss_color_field('border_color', 'Bordas', $config['border_color']);
                            esocss_color_field('menu_background', 'Fundo dos menus', $config['menu_background']);
                            esocss_color_field('menu_text_color', 'Texto dos menus', $config['menu_text_color']);
                            esocss_color_field('menu_hover_background', 'Fundo do item em foco', $config['menu_hover_background']);
                            esocss_color_field('menu_hover_text_color', 'Texto do item em foco', $config['menu_hover_text_color']);
                            ?>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title">Geometria e sombra</h3></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6 col-xl-3">
                                <label class="form-label fw-semibold" for="border_radius">Raio dos cards (px)</label>
                                <input class="form-control" type="number" min="0" max="30" id="border_radius" name="border_radius" value="<?= (int) $config['border_radius'] ?>">
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <label class="form-label fw-semibold" for="shadow_strength">Força da sombra</label>
                                <input class="form-control" type="number" min="0" max="30" id="shadow_strength" name="shadow_strength" value="<?= (int) $config['shadow_strength'] ?>">
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <label class="form-label fw-semibold" for="bar_radius">Raio das barras (px)</label>
                                <input class="form-control" type="number" min="0" max="20" id="bar_radius" name="bar_radius" value="<?= (int) $config['bar_radius'] ?>">
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <label class="form-label fw-semibold" for="bar_max_width">Largura máxima das barras</label>
                                <input class="form-control" type="number" min="8" max="80" id="bar_max_width" name="bar_max_width" value="<?= (int) $config['bar_max_width'] ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Paleta dos gráficos</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <?php esocss_color_field("chart_color_{$i}", "Cor {$i}", $config["chart_color_{$i}"]); ?>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">CSS personalizado</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Use apenas para ajustes adicionais. O conteúdo é carregado depois do CSS padrão do plugin.</p>
                        <textarea class="form-control font-monospace" name="custom_css" id="custom_css" rows="14" spellcheck="false"><?= htmlspecialchars($config['custom_css'], ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card sticky-xl-top" style="top: 6rem;">
                    <div class="card-header"><h3 class="card-title">Prévia</h3></div>
                    <div class="card-body">
                        <div id="eso-live-preview" class="eso-preview-shell">
                            <div class="eso-preview-header">ESO CSS / GLPI</div>
                            <div class="d-flex">
                                <div class="eso-preview-sidebar">
                                    <span></span><span></span><span></span><span></span>
                                </div>
                                <div class="eso-preview-content flex-fill">
                                    <div class="eso-preview-menu mb-3">
                                        <strong>Menu do usuário</strong>
                                        <span>Perfil e entidade</span>
                                        <span class="is-hovered">Configurações</span>
                                    </div>
                                    <div class="eso-preview-card">
                                        <div class="eso-preview-number">4.7K</div>
                                        <div>Computadores</div>
                                    </div>
                                    <button type="button" class="eso-preview-primary-button mt-3">
                                        <i class="ti ti-device-floppy me-1"></i>Salvar configuração
                                    </button>
                                    <div class="eso-preview-chart mt-3">
                                        <i style="height: 40%"></i>
                                        <i style="height: 65%"></i>
                                        <i style="height: 90%"></i>
                                        <i style="height: 55%"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="small text-muted">
                            As alterações só são aplicadas globalmente depois de clicar em <strong>Salvar configuração</strong>.
                        </div>
                    </div>
                    <div class="card-footer" id="eso-save-actions">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" name="update" value="1">
                                <i class="ti ti-device-floppy me-1"></i>Salvar configuração
                            </button>
                            <button type="submit" class="btn btn-outline-danger" name="reset" value="1"
                                    onclick="return confirm('Restaurar todas as configurações do tema?');">
                                <i class="ti ti-restore me-1"></i>Restaurar padrão
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="row justify-content-end mb-5">
        <div class="col-12 col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-refresh me-2"></i>Atualizações</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Versão instalada</span>
                        <strong>v<?= htmlspecialchars(Updater::currentVersion(), ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <?php if ($updateState['checked']): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Última versão</span>
                            <strong>v<?= htmlspecialchars($updateState['latest_version'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>
                        <div class="small text-muted mb-3">
                            Verificado em <?= htmlspecialchars($updateState['checked_at'], ENT_QUOTES, 'UTF-8') ?>.
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Consulte o lançamento mais recente no repositório oficial do GitHub.</p>
                    <?php endif; ?>

                    <form method="post" action="<?= htmlspecialchars($updateFormUrl, ENT_QUOTES, 'UTF-8') ?>">
                        <?= Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]) ?>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-outline-primary" name="check_update" value="1">
                                <i class="ti ti-search me-1"></i>Verificar versão
                            </button>
                            <?php if ($updateState['available']): ?>
                                <button type="submit" class="btn btn-success" name="install_update" value="1"
                                        onclick="return confirm('Atualizar automaticamente o ESO CSS para a versão <?= htmlspecialchars($updateState['latest_version'], ENT_QUOTES, 'UTF-8') ?>?');">
                                    <i class="ti ti-download me-1"></i>Atualizar automaticamente
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>

                    <?php if ($updateState['release_url'] !== ''): ?>
                        <a class="d-inline-block mt-3 small" href="<?= htmlspecialchars($updateState['release_url'], ENT_QUOTES, 'UTF-8') ?>"
                           target="_blank" rel="noopener noreferrer">Ver lançamento no GitHub</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const page = document.getElementById('esocss-config-page');
    if (!page) return;

    const $ = (selector) => page.querySelector(selector);
    const all = (selector) => [...page.querySelectorAll(selector)];
    let homeBackgroundObjectUrl = '';
    let homeLogoObjectUrl = '';
    let loginBackgroundObjectUrl = '';
    let loginLogoObjectUrl = '';
    const brandObjectUrls = {};

    function hexToRgba(hex, opacity) {
        const match = /^#([0-9a-f]{6})$/i.exec(hex || '');
        if (!match) return `rgba(16, 33, 63, ${opacity})`;
        const value = parseInt(match[1], 16);
        return `rgba(${(value >> 16) & 255}, ${(value >> 8) & 255}, ${value & 255}, ${opacity})`;
    }

    function syncHomePreview() {
        const preview = $('#eso-home-preview');
        if (!preview) return;

        const removeBackground = $('#remove_home_background')?.checked;
        const backgroundUrl = removeBackground
            ? ''
            : (preview.dataset.selectedBackgroundUrl || preview.dataset.backgroundUrl || '');
        preview.style.backgroundImage = backgroundUrl ? `url("${backgroundUrl}")` : 'none';
        preview.style.backgroundPosition = $('#home_background_position')?.value || 'center';
        preview.style.minHeight = Math.max(190, Math.min(360, Number($('#home_banner_height')?.value || 360) * .62)) + 'px';

        const overlay = preview.querySelector('.eso-home-preview-overlay');
        const opacity = Math.max(0, Math.min(90, Number($('#home_overlay_opacity')?.value || 38))) / 100;
        if (overlay) {
            overlay.style.backgroundColor = hexToRgba($('#home_overlay_color')?.value || '#10213F', opacity);
        }

        const title = preview.querySelector('.eso-home-preview-title');
        if (title) {
            title.textContent = $('#home_title')?.value.trim() || 'Como podemos ajudá-lo?';
            title.style.color = $('#home_title_color')?.value || '#FFFFFF';
            title.style.fontSize = Math.max(24, Math.min(72, Number($('#home_title_size')?.value || 44))) * .58 + 'px';
        }

        const subtitle = preview.querySelector('.eso-home-preview-subtitle');
        if (subtitle) {
            subtitle.textContent = $('#home_subtitle')?.value.trim() || '';
            subtitle.classList.toggle('d-none', subtitle.textContent === '');
        }

        const removeLogo = $('#remove_home_logo')?.checked;
        const logoUrl = removeLogo ? '' : (preview.dataset.selectedLogoUrl || preview.dataset.logoUrl || '');
        const logo = preview.querySelector('.eso-home-preview-logo');
        const logoFallback = preview.querySelector('.eso-home-preview-logo-fallback');
        if (logo) {
            logo.src = logoUrl;
            logo.style.maxHeight = Math.max(20, Math.min(96, Number($('#home_logo_max_height')?.value || 42))) + 'px';
            logo.classList.toggle('d-none', logoUrl === '');
        }
        logoFallback?.classList.toggle('d-none', logoUrl !== '');
    }

    function syncLoginPreview() {
        const preview = $('#eso-login-preview');
        if (!preview) return;

        const removeBackground = $('#remove_login_background')?.checked;
        const backgroundUrl = removeBackground
            ? ''
            : (preview.dataset.selectedBackgroundUrl || preview.dataset.backgroundUrl || '');
        const styleValue = $('#login_style')?.value || 'classic';
        const previewLoginStyle = ['classic', 'glass', 'portal'].includes(styleValue) ? styleValue : 'classic';
        const configuredImageMode = $('#login_image_mode')?.value === 'background' ? 'background' : 'panel';
        const layoutValue = $('#login_layout')?.value || 'center';
        const configuredLayout = ['center', 'left', 'right'].includes(layoutValue) ? layoutValue : 'center';
        const imageMode = previewLoginStyle === 'classic' ? configuredImageMode : 'background';
        const layout = previewLoginStyle === 'portal' && configuredLayout === 'center' ? 'right' : configuredLayout;
        const mediaWidth = Math.max(45, Math.min(75, Number($('#login_media_width')?.value || 65)));
        const imagePosition = $('#login_background_position')?.value || 'center';
        const overlayOpacity = Math.max(0, Math.min(90, Number($('#login_overlay_opacity')?.value || 12))) / 100;
        const overlayColor = hexToRgba($('#login_overlay_color')?.value || '#FFFFFF', overlayOpacity);

        preview.classList.remove(
            'eso-login-preview-image-panel',
            'eso-login-preview-image-background',
            'eso-login-preview-align-center',
            'eso-login-preview-align-left',
            'eso-login-preview-align-right',
            'eso-login-preview-style-classic',
            'eso-login-preview-style-glass',
            'eso-login-preview-style-portal',
            'eso-login-preview-has-background'
        );
        preview.classList.add(
            `eso-login-preview-image-${imageMode}`,
            `eso-login-preview-align-${layout}`,
            `eso-login-preview-style-${previewLoginStyle}`
        );
        if (!backgroundUrl) {
            preview.dataset.checkedBackgroundUrl = '';
            preview.dataset.backgroundAvailable = '0';
        } else if (preview.dataset.checkedBackgroundUrl === backgroundUrl) {
            if (preview.dataset.backgroundAvailable === '1') {
                preview.classList.add('eso-login-preview-has-background');
            }
        } else {
            preview.dataset.checkedBackgroundUrl = backgroundUrl;
            preview.dataset.backgroundAvailable = '0';
            const imageProbe = new Image();
            imageProbe.addEventListener('load', () => {
                if (preview.dataset.checkedBackgroundUrl !== backgroundUrl) return;
                preview.dataset.backgroundAvailable = '1';
                preview.classList.add('eso-login-preview-has-background');
            }, {once: true});
            imageProbe.addEventListener('error', () => {
                if (preview.dataset.checkedBackgroundUrl !== backgroundUrl) return;
                preview.dataset.backgroundAvailable = '0';
                preview.classList.remove('eso-login-preview-has-background');
            }, {once: true});
            imageProbe.src = backgroundUrl;
        }
        preview.style.setProperty('--lp-media-width', mediaWidth + '%');
        preview.style.backgroundColor = $('#login_background_color')?.value || '#F3F4F6';
        preview.style.backgroundImage = imageMode === 'background' && backgroundUrl ? `url("${backgroundUrl}")` : 'none';
        preview.style.backgroundPosition = imagePosition;

        const overlay = preview.querySelector('.eso-login-preview-overlay');
        if (overlay) {
            overlay.style.backgroundColor = imageMode === 'background' ? overlayColor : 'transparent';
        }
        const media = preview.querySelector('.eso-login-preview-media');
        if (media) {
            media.style.backgroundColor = $('#login_background_color')?.value || '#F3F4F6';
            media.style.backgroundImage = imageMode === 'panel' && backgroundUrl
                ? `linear-gradient(${overlayColor}, ${overlayColor}), url("${backgroundUrl}")`
                : 'none';
            media.style.backgroundPosition = imagePosition;
        }

        const cardColor = $('#login_card_color')?.value || '#FFFFFF';
        const configuredCardOpacity = Math.max(20, Math.min(100, Number($('#login_card_opacity')?.value || 98)));
        const glassTransparency = Math.max(0, Math.min(80, Number($('#login_glass_transparency')?.value || 35)));
        const cardOpacity = (previewLoginStyle === 'glass' ? 100 - glassTransparency : configuredCardOpacity) / 100;
        const textColor = $('#login_text_color')?.value || '#1F2937';
        const mutedColor = $('#login_muted_color')?.value || '#667085';
        const primaryColor = $('#login_primary_color')?.value || '#3157D5';
        const borderColor = $('#login_border_color')?.value || '#DDE3EC';
        const radius = Math.max(0, Math.min(40, Number($('#login_card_radius')?.value || 16)));
        preview.style.setProperty('--eso-login-card-preview', cardColor);
        const shell = preview.querySelector('.eso-login-preview-shell');
        const card = preview.querySelector('.eso-login-preview-card');
        [shell, card].forEach(element => {
            if (!element) return;
            element.style.backgroundColor = hexToRgba(cardColor, cardOpacity);
            element.style.borderColor = borderColor;
            element.style.color = textColor;
        });
        if (shell) {
            shell.style.borderRadius = radius + 'px';
            const outerWidth = Math.max(360, Math.min(1200, Number($('#login_card_width')?.value || 920)));
            const shellWidth = Math.min(660, outerWidth * .72);
            shell.style.maxWidth = shellWidth + 'px';
            if (card) {
                const panelWidth = Math.max(320, Math.min(1000, Number($('#login_panel_width')?.value || 720)));
                card.style.maxWidth = Math.min(Math.max(240, shellWidth - 48), panelWidth * .72) + 'px';
                card.style.marginInline = 'auto';
            }
        }
        if (card) card.style.borderRadius = Math.max(0, radius * .78) + 'px';
        $('#login_glass_transparency_field')?.classList.toggle('d-none', previewLoginStyle !== 'glass');
        preview.style.setProperty('--lp-primary', primaryColor);
        preview.style.setProperty('--lp-muted', mutedColor);
        preview.style.setProperty('--lp-border', borderColor);

        const title = preview.querySelector('.eso-login-preview-title');
        if (title) {
            title.textContent = previewLoginStyle === 'portal'
                ? ($('#login_welcome_text')?.value.trim() || 'Acesse sua conta')
                : ($('#login_title')?.value.trim() || 'Logon Único');
        }
        const subtitle = preview.querySelector('.eso-login-preview-subtitle');
        if (subtitle) {
            subtitle.textContent = $('#login_subtitle')?.value.trim() || '';
            subtitle.classList.toggle('d-none', subtitle.textContent === '');
        }
        const heroTitle = preview.querySelector('.eso-login-preview-hero-title');
        const heroSubtitle = preview.querySelector('.eso-login-preview-hero-subtitle');
        if (heroTitle) heroTitle.textContent = $('#login_title')?.value.trim() || 'Portal de Atendimento';
        if (heroSubtitle) heroSubtitle.textContent = $('#login_subtitle')?.value.trim() || '';

        const previewText = (selector, field, fallback = '') => {
            const element = preview.querySelector(selector);
            if (!element) return;
            element.textContent = $('#' + field)?.value.trim() || fallback;
        };
        previewText('.eso-login-preview-welcome', 'login_welcome_text');
        preview.querySelector('.eso-login-preview-welcome')?.classList.toggle(
            'd-none',
            !$('#login_welcome_text')?.value.trim()
        );
        previewText('.eso-login-preview-sso span', 'login_sso_button_text', 'Entrar com ENTRA ID');
        previewText('.eso-login-preview-remember span', 'login_remember_text', 'Lembrar de mim');
        previewText('.eso-login-preview-form-toggle', 'login_form_toggle_text', 'Usar formulário de login do GLPI');
        previewText('.eso-login-preview-user-label', 'login_user_label', 'Login');
        previewText('.eso-login-preview-user-placeholder', 'login_user_placeholder', 'usuario@instituicao.edu.br');
        previewText('.eso-login-preview-password-label', 'login_password_label', 'Senha');
        previewText('.eso-login-preview-password-placeholder', 'login_password_placeholder', '••••••••');
        previewText('.eso-login-preview-source-label', 'login_source_label', 'Origem de autenticação');
        previewText('.eso-login-preview-submit', 'login_submit_text', 'Entrar');
        previewText('.eso-login-preview-forgot', 'login_forgot_password_text', 'Esqueceu sua senha?');
        previewText('.eso-login-preview-faq', 'login_faq_text', 'FAQ');
        previewText('.eso-login-preview-footer', 'login_footer_text', 'GLPI Copyright (C) 2015-2026');

        const removeLogo = $('#remove_login_logo')?.checked;
        const logoUrl = removeLogo ? '' : (preview.dataset.selectedLogoUrl || preview.dataset.logoUrl || '');
        const hideDefaultLogo = $('#login_hide_default_logo')?.checked;
        const logo = preview.querySelector('.eso-login-preview-logo');
        const fallback = preview.querySelector('.eso-login-preview-logo-fallback');
        if (logo) {
            logo.src = logoUrl;
            logo.style.maxHeight = Math.min(120, Math.max(24, Number($('#login_logo_max_height')?.value || 78))) + 'px';
            logo.classList.toggle('d-none', logoUrl === '');
        }
        fallback?.classList.toggle('d-none', logoUrl !== '' || hideDefaultLogo);
    }

    function syncBrandPreview() {
        const preview = $('#eso-brand-preview');
        if (!preview) return;

        const resolveUrl = (selectedKey, currentKey, removeId) => {
            if (preview.dataset[selectedKey]) return preview.dataset[selectedKey];
            return $('#' + removeId)?.checked ? '' : (preview.dataset[currentKey] || '');
        };
        const sidebarUrl = resolveUrl('selectedSidebarUrl', 'sidebarUrl', 'remove_brand_sidebar_logo');
        const compactOwnUrl = resolveUrl('selectedCompactUrl', 'compactUrl', 'remove_brand_sidebar_compact_logo');
        const compactUrl = compactOwnUrl || sidebarUrl;
        const headerUrl = resolveUrl('selectedHeaderUrl', 'headerUrl', 'remove_brand_header_logo');
        const faviconUrl = resolveUrl('selectedFaviconUrl', 'faviconUrl', 'remove_brand_favicon');

        const applyImage = (imageSelector, fallbackSelector, url) => {
            const image = preview.querySelector(imageSelector);
            const fallback = preview.querySelector(fallbackSelector);
            if (image) {
                if (url) image.src = url;
                else image.removeAttribute('src');
                image.classList.toggle('d-none', !url);
            }
            fallback?.classList.toggle('d-none', !!url);
            return image;
        };

        const sidebarImage = applyImage(
            '.eso-brand-preview-sidebar-logo',
            '.eso-brand-preview-sidebar-fallback',
            sidebarUrl
        );
        if (sidebarImage) {
            sidebarImage.style.height = Math.max(
                24,
                Math.min(96, Number($('#brand_sidebar_logo_height')?.value || 44))
            ) + 'px';
        }

        const compactImage = applyImage(
            '.eso-brand-preview-compact-logo',
            '.eso-brand-preview-compact-fallback',
            compactUrl
        );
        if (compactImage) {
            const size = Math.max(24, Math.min(64, Number($('#brand_sidebar_compact_size')?.value || 40)));
            compactImage.style.width = size + 'px';
            compactImage.style.height = size + 'px';
        }

        const headerImage = applyImage(
            '.eso-brand-preview-header-logo',
            '.eso-brand-preview-header-fallback',
            headerUrl
        );
        if (headerImage) {
            headerImage.style.height = Math.max(
                24,
                Math.min(72, Number($('#brand_header_logo_height')?.value || 36))
            ) + 'px';
        }

        applyImage(
            '.eso-brand-preview-favicon',
            '.eso-brand-preview-favicon-fallback',
            faviconUrl
        );
    }

    function syncPreview() {
        const preview = $('#eso-live-preview');
        if (!preview) return;

        const val = (id, fallback) => $('#' + id)?.value || fallback;
        preview.style.setProperty('--p-primary', val('primary_color', '#3157D5'));
        preview.style.setProperty('--p-button-bg', val('button_background', val('primary_color', '#3157D5')));
        preview.style.setProperty('--p-button-text', val('button_text_color', '#FFFFFF'));
        preview.style.setProperty('--p-button-hover-bg', val('button_hover_background', val('primary_hover', '#2547B5')));
        preview.style.setProperty('--p-button-hover-text', val('button_hover_text_color', '#FFFFFF'));
        preview.style.setProperty('--p-sidebar-a', val('sidebar_start', '#172D55'));
        preview.style.setProperty('--p-sidebar-b', val('sidebar_end', '#10213F'));
        preview.style.setProperty('--p-sidebar-text', val('sidebar_text_color', '#F8FAFC'));
        preview.style.setProperty('--p-header-a', val('header_start', '#10213F'));
        preview.style.setProperty('--p-header-b', val('header_end', '#172D55'));
        preview.style.setProperty('--p-header-text', val('header_text_color', '#F8FAFC'));
        preview.style.setProperty('--p-bg', val('background_color', '#F5F7FB'));
        preview.style.setProperty('--p-card', val('card_color', '#FFFFFF'));
        preview.style.setProperty('--p-text', val('text_color', '#17233C'));
        preview.style.setProperty('--p-border', val('border_color', '#E3E8F0'));
        preview.style.setProperty('--p-menu-bg', val('menu_background', '#FFFFFF'));
        preview.style.setProperty('--p-menu-text', val('menu_text_color', '#374151'));
        preview.style.setProperty('--p-menu-hover-bg', val('menu_hover_background', '#EEF3FF'));
        preview.style.setProperty('--p-menu-hover-text', val('menu_hover_text_color', '#2547B5'));
        preview.style.setProperty('--p-radius', (parseInt($('#border_radius')?.value || '14', 10)) + 'px');

        for (let i = 1; i <= 4; i++) {
            preview.style.setProperty('--p-chart-' + i, val('chart_color_' + i, '#3157D5'));
        }

        syncHomePreview();
        syncLoginPreview();
        syncBrandPreview();
    }

    all('.eso-color-picker').forEach(picker => {
        picker.addEventListener('input', () => {
            const input = $('#' + picker.dataset.target);
            if (input) input.value = picker.value.toUpperCase();
            syncPreview();
        });
    });

    all('.eso-color-text').forEach(input => {
        input.addEventListener('input', () => {
            const picker = page.querySelector('.eso-color-picker[data-target="' + input.id + '"]');
            if (/^#[0-9a-fA-F]{6}$/.test(input.value) && picker) {
                picker.value = input.value;
            }
            syncPreview();
        });
    });

    all('input[type="number"]').forEach(input => input.addEventListener('input', syncPreview));
    all('#home_title, #home_subtitle, #home_background_position, #remove_home_background, #remove_home_logo')
        .forEach(input => input.addEventListener('input', syncPreview));
    all('#login_title, #login_subtitle, #login_style, #login_image_mode, #login_layout, #login_background_position, #login_glass_transparency, #remove_login_background, #remove_login_logo, #login_hide_default_logo')
        .forEach(input => input.addEventListener('input', syncPreview));
    all('#remove_brand_sidebar_logo, #remove_brand_sidebar_compact_logo, #remove_brand_header_logo, #remove_brand_favicon')
        .forEach(input => input.addEventListener('input', syncPreview));
    all('.eso-login-text-input').forEach(input => input.addEventListener('input', syncPreview));

    $('#home_background_file')?.addEventListener('change', event => {
        if (homeBackgroundObjectUrl) URL.revokeObjectURL(homeBackgroundObjectUrl);
        const file = event.target.files?.[0];
        homeBackgroundObjectUrl = file ? URL.createObjectURL(file) : '';
        const preview = $('#eso-home-preview');
        if (preview) preview.dataset.selectedBackgroundUrl = homeBackgroundObjectUrl;
        syncHomePreview();
    });

    $('#home_logo_file')?.addEventListener('change', event => {
        if (homeLogoObjectUrl) URL.revokeObjectURL(homeLogoObjectUrl);
        const file = event.target.files?.[0];
        homeLogoObjectUrl = file ? URL.createObjectURL(file) : '';
        const preview = $('#eso-home-preview');
        if (preview) preview.dataset.selectedLogoUrl = homeLogoObjectUrl;
        syncHomePreview();
    });

    $('#login_background_file')?.addEventListener('change', event => {
        if (loginBackgroundObjectUrl) URL.revokeObjectURL(loginBackgroundObjectUrl);
        const file = event.target.files?.[0];
        loginBackgroundObjectUrl = file ? URL.createObjectURL(file) : '';
        const preview = $('#eso-login-preview');
        if (preview) preview.dataset.selectedBackgroundUrl = loginBackgroundObjectUrl;
        syncLoginPreview();
    });

    $('#login_logo_file')?.addEventListener('change', event => {
        if (loginLogoObjectUrl) URL.revokeObjectURL(loginLogoObjectUrl);
        const file = event.target.files?.[0];
        loginLogoObjectUrl = file ? URL.createObjectURL(file) : '';
        const preview = $('#eso-login-preview');
        if (preview) preview.dataset.selectedLogoUrl = loginLogoObjectUrl;
        syncLoginPreview();
    });

    const bindBrandUpload = (inputId, objectKey, datasetKey) => {
        $('#' + inputId)?.addEventListener('change', event => {
            if (brandObjectUrls[objectKey]) URL.revokeObjectURL(brandObjectUrls[objectKey]);
            const file = event.target.files?.[0];
            brandObjectUrls[objectKey] = file ? URL.createObjectURL(file) : '';
            const preview = $('#eso-brand-preview');
            if (preview) preview.dataset[datasetKey] = brandObjectUrls[objectKey];
            syncBrandPreview();
        });
    };

    bindBrandUpload('brand_sidebar_logo_file', 'sidebar', 'selectedSidebarUrl');
    bindBrandUpload('brand_sidebar_compact_logo_file', 'compact', 'selectedCompactUrl');
    bindBrandUpload('brand_header_logo_file', 'header', 'selectedHeaderUrl');
    bindBrandUpload('brand_favicon_file', 'favicon', 'selectedFaviconUrl');

    const presets = {
        eso: {
            primary_color: '#3157D5', primary_hover: '#2547B5',
            button_background: '#3157D5', button_text_color: '#FFFFFF',
            button_hover_background: '#2547B5', button_hover_text_color: '#FFFFFF',
            sidebar_start: '#172D55', sidebar_end: '#10213F', sidebar_text_color: '#F8FAFC',
            header_start: '#10213F', header_end: '#172D55', header_text_color: '#F8FAFC',
            background_color: '#F5F7FB', card_color: '#FFFFFF', text_color: '#17233C',
            muted_color: '#66758C', border_color: '#E3E8F0', menu_background: '#FFFFFF',
            menu_text_color: '#374151', menu_hover_background: '#EEF3FF', menu_hover_text_color: '#2547B5'
        },
        oceano: {
            primary_color: '#0F766E', primary_hover: '#115E59',
            button_background: '#0F766E', button_text_color: '#FFFFFF',
            button_hover_background: '#115E59', button_hover_text_color: '#FFFFFF',
            sidebar_start: '#134E4A', sidebar_end: '#0F3D3A', sidebar_text_color: '#F0FDFA',
            header_start: '#0F3D3A', header_end: '#115E59', header_text_color: '#F0FDFA',
            background_color: '#F0FDFA', card_color: '#FFFFFF', text_color: '#134E4A',
            muted_color: '#5F7472', border_color: '#CCFBF1', menu_background: '#FFFFFF',
            menu_text_color: '#134E4A', menu_hover_background: '#CCFBF1', menu_hover_text_color: '#115E59'
        },
        grafite: {
            primary_color: '#7C3AED', primary_hover: '#6D28D9',
            button_background: '#7C3AED', button_text_color: '#FFFFFF',
            button_hover_background: '#6D28D9', button_hover_text_color: '#FFFFFF',
            sidebar_start: '#252A34', sidebar_end: '#171A21', sidebar_text_color: '#F8FAFC',
            header_start: '#171A21', header_end: '#252A34', header_text_color: '#F8FAFC',
            background_color: '#F3F4F6', card_color: '#FFFFFF', text_color: '#1F2937',
            muted_color: '#6B7280', border_color: '#E5E7EB', menu_background: '#FFFFFF',
            menu_text_color: '#1F2937', menu_hover_background: '#F3E8FF', menu_hover_text_color: '#6D28D9'
        }
    };

    all('.eso-preset').forEach(button => {
        button.addEventListener('click', () => {
            const preset = presets[button.dataset.preset];
            if (!preset) return;

            Object.entries(preset).forEach(([id, value]) => {
                const input = $('#' + id);
                const picker = page.querySelector('.eso-color-picker[data-target="' + id + '"]');
                if (input) input.value = value;
                if (picker) picker.value = value;
            });
            syncPreview();
        });
    });

    syncPreview();
})();
</script>

<?php Html::footer(); ?>
