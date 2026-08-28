<?php

namespace GlpiPlugin\EsoCss;

require_once __DIR__ . '/MediaManager.php';

final class Settings
{
    public const CONTEXT = 'plugin:esocss';

    public static function defaults(): array
    {
        return [
            'theme_enabled'        => '1',
            'chart_enabled'        => '1',
            'card_hover'           => '1',
            'header_dark'          => '1',
            'home_enabled'         => '0',
            'home_hide_scenes'     => '0',
            'login_enabled'        => '0',
            'login_hide_default_logo' => '0',
            'login_style'          => 'classic',
            'login_image_mode'     => 'panel',
            'login_layout'         => 'center',

            'primary_color'        => '#3157D5',
            'primary_hover'        => '#2547B5',
            'button_background'    => '#3157D5',
            'button_text_color'    => '#FFFFFF',
            'button_hover_background' => '#2547B5',
            'button_hover_text_color' => '#FFFFFF',
            'sidebar_start'        => '#172D55',
            'sidebar_end'          => '#10213F',
            'sidebar_text_color'   => '#F8FAFC',
            'header_start'         => '#10213F',
            'header_end'           => '#172D55',
            'header_text_color'    => '#F8FAFC',
            'background_color'     => '#F5F7FB',
            'card_color'           => '#FFFFFF',
            'text_color'           => '#17233C',
            'muted_color'          => '#66758C',
            'border_color'         => '#E3E8F0',
            'menu_background'       => '#FFFFFF',
            'menu_text_color'       => '#374151',
            'menu_hover_background' => '#EEF3FF',
            'menu_hover_text_color' => '#2547B5',
            'home_overlay_color'    => '#10213F',
            'home_title_color'      => '#FFFFFF',
            'login_background_color' => '#F3F4F6',
            'login_card_color'       => '#FFFFFF',
            'login_text_color'       => '#1F2937',
            'login_muted_color'      => '#667085',
            'login_primary_color'    => '#3157D5',
            'login_border_color'     => '#DDE3EC',
            'login_overlay_color'    => '#FFFFFF',

            'border_radius'        => '14',
            'shadow_strength'      => '8',
            'bar_radius'           => '7',
            'bar_max_width'        => '28',
            'home_overlay_opacity' => '38',
            'home_banner_height'   => '360',
            'home_title_size'      => '44',
            'home_logo_max_height' => '42',
            'login_overlay_opacity' => '12',
            'login_card_opacity'    => '98',
            'login_glass_transparency' => '35',
            'login_card_width'      => '920',
            'login_panel_width'     => '720',
            'login_media_width'     => '65',
            'login_card_radius'     => '16',
            'login_logo_max_height' => '78',
            'brand_sidebar_logo_height' => '44',
            'brand_sidebar_compact_size' => '40',
            'brand_header_logo_height' => '36',

            'home_title'           => '',
            'home_subtitle'        => '',
            'home_background_position' => 'center',
            'home_background_image' => '',
            'home_logo_image'       => '',
            'login_title'           => '',
            'login_subtitle'        => '',
            'login_welcome_text'    => '',
            'login_sso_button_text' => '',
            'login_remember_text'   => '',
            'login_form_toggle_text' => '',
            'login_user_label'      => '',
            'login_user_placeholder' => '',
            'login_password_label'  => '',
            'login_password_placeholder' => '',
            'login_source_label'    => '',
            'login_submit_text'     => '',
            'login_forgot_password_text' => '',
            'login_faq_text'        => '',
            'login_footer_text'     => '',
            'login_background_position' => 'center',
            'login_background_image' => '',
            'login_logo_image'       => '',
            'brand_sidebar_logo_image' => '',
            'brand_sidebar_compact_logo_image' => '',
            'brand_header_logo_image' => '',
            'brand_favicon_image' => '',

            'chart_color_1'        => '#3157D5',
            'chart_color_2'        => '#3B82F6',
            'chart_color_3'        => '#38BDF8',
            'chart_color_4'        => '#14B8A6',
            'chart_color_5'        => '#22C55E',
            'chart_color_6'        => '#8B5CF6',
            'chart_color_7'        => '#F59E0B',
            'chart_color_8'        => '#EF4444',

            'custom_css'           => '',
        ];
    }

    public static function get(): array
    {
        $saved = \Config::getConfigurationValues(self::CONTEXT);
        $saved = is_array($saved) ? $saved : [];
        $values = array_merge(self::defaults(), $saved);

        // Preserve the previous behavior for upgrades: buttons followed the
        // primary colors before dedicated button options existed.
        if (!array_key_exists('button_background', $saved)) {
            $values['button_background'] = $values['primary_color'];
        }
        if (!array_key_exists('button_hover_background', $saved)) {
            $values['button_hover_background'] = $values['primary_hover'];
        }

        // Keep the full-background presentation for installations upgraded
        // from versions that did not yet offer a separate image panel.
        if ($saved !== [] && !array_key_exists('login_image_mode', $saved)) {
            $values['login_image_mode'] = 'background';
        }

        return $values;
    }

    public static function sanitize(array $input): array
    {
        $defaults = self::defaults();
        $out = [];

        foreach (
            [
                'theme_enabled', 'chart_enabled', 'card_hover', 'header_dark',
                'home_enabled', 'home_hide_scenes', 'login_enabled', 'login_hide_default_logo',
            ]
            as $key
        ) {
            $out[$key] = isset($input[$key]) && (string) $input[$key] === '1' ? '1' : '0';
        }

        foreach (
            [
                'primary_color', 'primary_hover',
                'button_background', 'button_text_color',
                'button_hover_background', 'button_hover_text_color',
                'sidebar_start', 'sidebar_end',
                'sidebar_text_color', 'header_start', 'header_end', 'header_text_color',
                'background_color', 'card_color', 'text_color', 'muted_color',
                'border_color', 'menu_background', 'menu_text_color',
                'menu_hover_background', 'menu_hover_text_color',
                'home_overlay_color', 'home_title_color',
                'login_background_color', 'login_card_color', 'login_text_color',
                'login_muted_color', 'login_primary_color', 'login_border_color',
                'login_overlay_color',
                'chart_color_1', 'chart_color_2', 'chart_color_3', 'chart_color_4',
                'chart_color_5', 'chart_color_6', 'chart_color_7', 'chart_color_8',
            ] as $key
        ) {
            $value = strtoupper(trim((string) ($input[$key] ?? $defaults[$key])));
            $out[$key] = preg_match('/^#[0-9A-F]{6}$/', $value) ? $value : $defaults[$key];
        }

        $out['border_radius']   = (string) self::clampInt($input['border_radius'] ?? 14, 0, 30, 14);
        $out['shadow_strength'] = (string) self::clampInt($input['shadow_strength'] ?? 8, 0, 30, 8);
        $out['bar_radius']      = (string) self::clampInt($input['bar_radius'] ?? 7, 0, 20, 7);
        $out['bar_max_width']   = (string) self::clampInt($input['bar_max_width'] ?? 28, 8, 80, 28);
        $out['home_overlay_opacity'] = (string) self::clampInt(
            $input['home_overlay_opacity'] ?? 38,
            0,
            90,
            38
        );
        $out['home_banner_height'] = (string) self::clampInt(
            $input['home_banner_height'] ?? 360,
            240,
            720,
            360
        );
        $out['home_title_size'] = (string) self::clampInt($input['home_title_size'] ?? 44, 24, 72, 44);
        $out['home_logo_max_height'] = (string) self::clampInt(
            $input['home_logo_max_height'] ?? 42,
            20,
            96,
            42
        );
        $out['login_overlay_opacity'] = (string) self::clampInt(
            $input['login_overlay_opacity'] ?? 12,
            0,
            90,
            12
        );
        $out['login_card_opacity'] = (string) self::clampInt(
            $input['login_card_opacity'] ?? 98,
            20,
            100,
            98
        );
        $out['login_glass_transparency'] = (string) self::clampInt(
            $input['login_glass_transparency'] ?? 35,
            0,
            80,
            35
        );
        $out['login_card_width'] = (string) self::clampInt(
            $input['login_card_width'] ?? 920,
            360,
            1200,
            920
        );
        $out['login_panel_width'] = (string) self::clampInt(
            $input['login_panel_width'] ?? 720,
            320,
            1000,
            720
        );
        $out['login_media_width'] = (string) self::clampInt(
            $input['login_media_width'] ?? 65,
            45,
            75,
            65
        );
        $out['login_card_radius'] = (string) self::clampInt(
            $input['login_card_radius'] ?? 16,
            0,
            40,
            16
        );
        $out['login_logo_max_height'] = (string) self::clampInt(
            $input['login_logo_max_height'] ?? 78,
            24,
            180,
            78
        );
        $out['brand_sidebar_logo_height'] = (string) self::clampInt(
            $input['brand_sidebar_logo_height'] ?? 44,
            24,
            96,
            44
        );
        $out['brand_sidebar_compact_size'] = (string) self::clampInt(
            $input['brand_sidebar_compact_size'] ?? 40,
            24,
            64,
            40
        );
        $out['brand_header_logo_height'] = (string) self::clampInt(
            $input['brand_header_logo_height'] ?? 36,
            24,
            72,
            36
        );

        $out['home_title'] = self::sanitizeText($input['home_title'] ?? '', 160);
        $out['home_subtitle'] = self::sanitizeText($input['home_subtitle'] ?? '', 280);
        $positions = ['center', 'top', 'bottom', 'left', 'right'];
        $position = strtolower(trim((string) ($input['home_background_position'] ?? 'center')));
        $out['home_background_position'] = in_array($position, $positions, true) ? $position : 'center';

        $out['login_title'] = self::sanitizeText($input['login_title'] ?? '', 160);
        $out['login_subtitle'] = self::sanitizeText($input['login_subtitle'] ?? '', 280);
        $out['login_welcome_text'] = self::sanitizeText($input['login_welcome_text'] ?? '', 280);
        foreach (
            [
                'login_sso_button_text', 'login_remember_text', 'login_form_toggle_text',
                'login_user_label', 'login_user_placeholder', 'login_password_label',
                'login_password_placeholder', 'login_source_label', 'login_submit_text',
                'login_forgot_password_text', 'login_faq_text', 'login_footer_text',
            ] as $textKey
        ) {
            $out[$textKey] = self::sanitizeText($input[$textKey] ?? '', $textKey === 'login_footer_text' ? 280 : 160);
        }
        $loginPosition = strtolower(trim((string) ($input['login_background_position'] ?? 'center')));
        $out['login_background_position'] = in_array($loginPosition, $positions, true)
            ? $loginPosition
            : 'center';
        $imageMode = strtolower(trim((string) ($input['login_image_mode'] ?? 'panel')));
        $out['login_image_mode'] = in_array($imageMode, ['panel', 'background'], true)
            ? $imageMode
            : 'panel';
        $loginStyle = strtolower(trim((string) ($input['login_style'] ?? 'classic')));
        $out['login_style'] = in_array($loginStyle, ['classic', 'glass', 'portal'], true)
            ? $loginStyle
            : 'classic';
        $loginLayout = strtolower(trim((string) ($input['login_layout'] ?? 'center')));
        $out['login_layout'] = in_array($loginLayout, ['center', 'left', 'right'], true)
            ? $loginLayout
            : 'center';

        $customCss = (string) ($input['custom_css'] ?? '');
        $out['custom_css'] = mb_substr($customCss, 0, 100000);

        return $out;
    }

    public static function publicPayload(): array
    {
        $c = self::get();

        return [
            'theme_enabled'    => $c['theme_enabled'] === '1',
            'chart_enabled'    => $c['chart_enabled'] === '1',
            'card_hover'       => $c['card_hover'] === '1',
            'header_dark'      => $c['header_dark'] === '1',
            'home'             => [
                'enabled'             => $c['home_enabled'] === '1',
                'hide_scenes'         => $c['home_hide_scenes'] === '1',
                'title'               => $c['home_title'],
                'subtitle'            => $c['home_subtitle'],
                'background_url'      => MediaManager::publicUrl($c['home_background_image']),
                'logo_url'            => MediaManager::publicUrl($c['home_logo_image']),
                'background_position' => $c['home_background_position'],
                'overlay_color'       => $c['home_overlay_color'],
                'overlay_opacity'     => (int) $c['home_overlay_opacity'],
                'title_color'         => $c['home_title_color'],
                'banner_height'       => (int) $c['home_banner_height'],
                'title_size'          => (int) $c['home_title_size'],
                'logo_max_height'     => (int) $c['home_logo_max_height'],
            ],
            'branding'         => [
                'sidebar_url'          => MediaManager::publicUrl($c['brand_sidebar_logo_image']),
                'sidebar_compact_url'  => MediaManager::publicUrl($c['brand_sidebar_compact_logo_image']),
                'header_url'           => MediaManager::publicUrl($c['brand_header_logo_image']),
                'favicon_url'          => MediaManager::publicUrl($c['brand_favicon_image']),
                'sidebar_height'       => (int) $c['brand_sidebar_logo_height'],
                'sidebar_compact_size' => (int) $c['brand_sidebar_compact_size'],
                'header_height'        => (int) $c['brand_header_logo_height'],
            ],
            'colors'           => [
                'primary'              => $c['primary_color'],
                'primary_hover'        => $c['primary_hover'],
                'button_background'    => $c['button_background'],
                'button_text'          => $c['button_text_color'],
                'button_hover_background' => $c['button_hover_background'],
                'button_hover_text'    => $c['button_hover_text_color'],
                'sidebar_start'        => $c['sidebar_start'],
                'sidebar_end'          => $c['sidebar_end'],
                'sidebar_text'         => $c['sidebar_text_color'],
                'header_start'         => $c['header_start'],
                'header_end'           => $c['header_end'],
                'header_text'          => $c['header_text_color'],
                'background'           => $c['background_color'],
                'card'                 => $c['card_color'],
                'text'                 => $c['text_color'],
                'muted'                => $c['muted_color'],
                'border'               => $c['border_color'],
                'menu_background'       => $c['menu_background'],
                'menu_text'             => $c['menu_text_color'],
                'menu_hover_background' => $c['menu_hover_background'],
                'menu_hover_text'       => $c['menu_hover_text_color'],
            ],
            'border_radius'    => (int) $c['border_radius'],
            'shadow_strength'  => (int) $c['shadow_strength'],
            'bar_radius'       => (int) $c['bar_radius'],
            'bar_max_width'    => (int) $c['bar_max_width'],
            'chart_palette'    => [
                $c['chart_color_1'], $c['chart_color_2'], $c['chart_color_3'], $c['chart_color_4'],
                $c['chart_color_5'], $c['chart_color_6'], $c['chart_color_7'], $c['chart_color_8'],
            ],
            'custom_css'       => $c['custom_css'],
        ];
    }

    /**
     * Anonymous-safe subset used by the official display_login hook.
     * Custom CSS and authenticated-interface preferences are intentionally excluded.
     */
    public static function publicLoginPayload(): array
    {
        $c = self::get();

        return [
            'enabled'             => $c['login_enabled'] === '1',
            'hide_default_logo'   => $c['login_hide_default_logo'] === '1',
            'title'               => $c['login_title'],
            'subtitle'            => $c['login_subtitle'],
            'texts'               => [
                'welcome'              => $c['login_welcome_text'],
                'sso_button'           => $c['login_sso_button_text'],
                'remember'             => $c['login_remember_text'],
                'form_toggle'          => $c['login_form_toggle_text'],
                'user_label'           => $c['login_user_label'],
                'user_placeholder'     => $c['login_user_placeholder'],
                'password_label'       => $c['login_password_label'],
                'password_placeholder' => $c['login_password_placeholder'],
                'source_label'         => $c['login_source_label'],
                'submit'               => $c['login_submit_text'],
                'forgot_password'      => $c['login_forgot_password_text'],
                'faq'                  => $c['login_faq_text'],
                'footer'               => $c['login_footer_text'],
            ],
            'background_url'      => MediaManager::publicUrl($c['login_background_image']),
            'logo_url'            => MediaManager::publicUrl($c['login_logo_image']),
            'favicon_url'         => MediaManager::publicUrl($c['brand_favicon_image']),
            'style'               => $c['login_style'],
            'image_mode'          => $c['login_image_mode'],
            'layout'              => $c['login_layout'],
            'background_position' => $c['login_background_position'],
            'background_color'    => $c['login_background_color'],
            'card_color'          => $c['login_card_color'],
            'text_color'          => $c['login_text_color'],
            'muted_color'         => $c['login_muted_color'],
            'primary_color'       => $c['login_primary_color'],
            'border_color'        => $c['login_border_color'],
            'overlay_color'       => $c['login_overlay_color'],
            'overlay_opacity'     => (int) $c['login_overlay_opacity'],
            'card_opacity'        => (int) $c['login_card_opacity'],
            'glass_transparency'  => (int) $c['login_glass_transparency'],
            'card_width'          => (int) $c['login_card_width'],
            'panel_width'         => (int) $c['login_panel_width'],
            'media_width'         => (int) $c['login_media_width'],
            'card_radius'         => (int) $c['login_card_radius'],
            'logo_max_height'     => (int) $c['login_logo_max_height'],
        ];
    }

    private static function clampInt(mixed $value, int $min, int $max, int $fallback): int
    {
        $value = filter_var($value, FILTER_VALIDATE_INT);
        if ($value === false) {
            return $fallback;
        }
        return max($min, min($max, $value));
    }

    private static function sanitizeText(mixed $value, int $maxLength): string
    {
        $value = trim(strip_tags((string) $value));
        $value = preg_replace('/\s+/u', ' ', $value) ?? '';

        return mb_substr($value, 0, $maxLength);
    }
}
