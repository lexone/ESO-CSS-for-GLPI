(() => {
    'use strict';

    function getPluginBaseUrl() {
        const scripts = [...document.scripts];
        const script = scripts.find((item) => /\/esocss\/js\/eso-theme\.js(?:\?|$)/.test(item.src));
        if (script) {
            const url = new URL(script.src, window.location.href);
            url.search = '';
            url.hash = '';
            url.pathname = url.pathname.replace(/\/js\/eso-theme\.js$/, '');
            return url.toString().replace(/\/$/, '');
        }

        // Fallback for the standard GLPI plugins path.
        const root = window.location.pathname.split('/plugins/')[0].replace(/\/$/, '');
        return `${window.location.origin}${root}/plugins/esocss`;
    }

    const CONFIG_ENDPOINT = `${getPluginBaseUrl()}/front/config-data.php`;
    let config = null;
    let chartTimer = null;
    let loginConfig = null;

    function setCssVariable(name, value) {
        document.documentElement.style.setProperty(name, value);
    }

    function injectCustomCss(css) {
        let style = document.getElementById('esocss-custom-css');
        if (!style) {
            style = document.createElement('style');
            style.id = 'esocss-custom-css';
            document.head.appendChild(style);
        }
        style.textContent = css || '';
    }

    function colorWithOpacity(hex, opacity) {
        const match = /^#([0-9a-f]{6})$/i.exec(hex || '');
        if (!match) return `rgba(16, 33, 63, ${opacity})`;
        const value = parseInt(match[1], 16);
        return `rgba(${(value >> 16) & 255}, ${(value >> 8) & 255}, ${value & 255}, ${opacity})`;
    }

    function cssUrl(url) {
        return typeof url === 'string' && url !== '' ? `url(${JSON.stringify(url)})` : 'none';
    }

    function applyFavicon(url) {
        if (typeof url !== 'string' || url === '') return;

        let icon = document.getElementById('esocss-favicon');
        if (!icon) {
            icon = document.createElement('link');
            icon.id = 'esocss-favicon';
            icon.rel = 'icon';
            document.head.appendChild(icon);
        }
        if (icon.href !== url) icon.href = url;
    }

    function applyBranding(branding = {}) {
        const body = document.body;
        if (!body) return;

        const sidebarUrl = branding.sidebar_url || '';
        const compactUrl = branding.sidebar_compact_url || sidebarUrl;
        const headerUrl = branding.header_url || '';

        body.classList.toggle('eso-brand-sidebar', sidebarUrl !== '');
        body.classList.toggle('eso-brand-sidebar-compact', compactUrl !== '');
        body.classList.toggle('eso-brand-header', headerUrl !== '');

        setCssVariable('--eso-brand-sidebar-image', cssUrl(sidebarUrl));
        setCssVariable('--eso-brand-sidebar-compact-image', cssUrl(compactUrl));
        setCssVariable('--eso-brand-header-image', cssUrl(headerUrl));
        setCssVariable(
            '--eso-brand-sidebar-height',
            `${Math.max(24, Math.min(96, Number(branding.sidebar_height || 44)))}px`
        );
        setCssVariable(
            '--eso-brand-sidebar-compact-size',
            `${Math.max(24, Math.min(64, Number(branding.sidebar_compact_size || 40)))}px`
        );
        setCssVariable(
            '--eso-brand-header-height',
            `${Math.max(24, Math.min(72, Number(branding.header_height || 36)))}px`
        );
        applyFavicon(branding.favicon_url || '');
    }

    function applyHelpdeskHome(home = {}) {
        const body = document.body;
        if (!body) return;

        const enabled = !!home.enabled && body.classList.contains('eso-theme-enabled');
        body.classList.toggle('eso-home-custom', enabled);
        body.classList.toggle('eso-home-hide-scenes', enabled && !!home.hide_scenes);
        body.classList.toggle('eso-home-has-logo', enabled && !!home.logo_url);

        const title = document.querySelector('.helpdesk-home-container [data-testid="home-title"]');
        if (title) {
            if (!title.dataset.esoOriginalTitle) {
                title.dataset.esoOriginalTitle = title.textContent.trim();
            }
            const desiredTitle = enabled && home.title
                ? home.title
                : title.dataset.esoOriginalTitle;
            if (title.textContent.trim() !== desiredTitle) {
                title.textContent = desiredTitle;
            }

            let subtitle = title.parentElement?.querySelector('.eso-home-subtitle');
            if (enabled && home.subtitle) {
                if (!subtitle) {
                    subtitle = document.createElement('p');
                    subtitle.className = 'eso-home-subtitle';
                    title.insertAdjacentElement('afterend', subtitle);
                }
                if (subtitle.textContent !== home.subtitle) {
                    subtitle.textContent = home.subtitle;
                }
            } else {
                subtitle?.remove();
            }
        }

        if (!enabled) return;

        const overlayOpacity = Math.max(0, Math.min(90, Number(home.overlay_opacity || 0))) / 100;
        setCssVariable('--eso-home-background-image', cssUrl(home.background_url));
        setCssVariable('--eso-home-background-position', home.background_position || 'center');
        setCssVariable('--eso-home-overlay', colorWithOpacity(home.overlay_color || '#10213F', overlayOpacity));
        setCssVariable('--eso-home-title-color', home.title_color || '#FFFFFF');
        setCssVariable('--eso-home-banner-height', `${Math.max(240, Math.min(720, Number(home.banner_height || 360)))}px`);
        setCssVariable('--eso-home-title-size', `${Math.max(24, Math.min(72, Number(home.title_size || 44)))}px`);
        setCssVariable('--eso-home-logo-image', cssUrl(home.logo_url));
        setCssVariable('--eso-home-logo-height', `${Math.max(20, Math.min(96, Number(home.logo_max_height || 42)))}px`);
    }

    function readLoginConfig() {
        const marker = document.getElementById('esocss-login-config');
        if (!marker) return null;

        try {
            return JSON.parse(marker.textContent || '{}');
        } catch (error) {
            console.debug('[ESO CSS] A configuração da tela de login é inválida:', error);
            return null;
        }
    }

    function markEmptyLoginHookHost() {
        const marker = document.getElementById('esocss-login-config');
        const host = marker?.parentElement;
        if (!host?.classList.contains('col-auto')) return;

        const onlyConfiguration = ![...host.childNodes].some((node) => {
            if (node === marker) return false;
            if (node.nodeType === Node.TEXT_NODE) return (node.textContent || '').trim() !== '';
            if (node.nodeType !== Node.ELEMENT_NODE) return false;
            if (['SCRIPT', 'STYLE', 'LINK', 'META'].includes(node.tagName)) return false;

            const element = /** @type {Element} */ (node);
            return (element.textContent || '').trim() !== ''
                || element.matches('img, video, iframe, button, input, select, textarea, a[href]')
                || !!element.querySelector('img, video, iframe, button, input, select, textarea, a[href]');
        });
        host.classList.toggle('eso-login-config-only', onlyConfiguration);
    }

    function findLoginHeadings() {
        const headings = [...document.querySelectorAll(
            'body.welcome-anonymous .main-content-card .card-header h1, ' +
            'body.welcome-anonymous .main-content-card .card-header h2, ' +
            'body.welcome-anonymous .main-content-card .card-header h3, ' +
            'body.welcome-anonymous .main-content-card h1, ' +
            'body.welcome-anonymous .main-content-card h2, ' +
            'body.welcome-anonymous .main-content-card h3, ' +
            'body.welcome-anonymous .singlesignon-login-title, ' +
            'body.welcome-anonymous .singlesignon-classic-title h2'
        )];
        const hookHost = document.getElementById('esocss-login-config')?.parentElement;
        if (hookHost) headings.push(...hookHost.querySelectorAll('h1, h2, h3'));
        return [...new Set(headings)];
    }

    function setPlainText(element, value) {
        if (!element || !value || element.textContent.trim() === value) return;
        element.textContent = value;
    }

    function setControlText(element, value) {
        if (!element || !value) return;
        if (element.dataset.esoCustomText === value && element.textContent.includes(value)) return;

        const textNodes = [...element.childNodes].filter(
            (node) => node.nodeType === Node.TEXT_NODE && (node.textContent || '').trim() !== ''
        );
        if (textNodes.length) {
            textNodes[0].nodeValue = ` ${value} `;
            textNodes.slice(1).forEach((node) => { node.nodeValue = ''; });
        } else {
            const leaf = [...element.querySelectorAll('span, strong, b')].find(
                (candidate) => candidate.children.length === 0 && candidate.textContent.trim() !== ''
            );
            if (leaf) {
                leaf.textContent = value;
            } else {
                element.appendChild(document.createTextNode(` ${value}`));
            }
        }
        element.dataset.esoCustomText = value;
    }

    function findTextElement(root, selector, pattern) {
        if (!root) return null;
        return [...root.querySelectorAll(selector)]
            .filter((element) => pattern.test(element.textContent.trim()))
            .sort((left, right) => left.textContent.trim().length - right.textContent.trim().length)[0] || null;
    }

    function applyCustomFooterText(value) {
        if (!value) return;
        const footer = document.querySelector(
            'body.welcome-anonymous .container-tight > .text-center.text-muted'
        );
        if (!footer) return;

        let custom = footer.querySelector('.eso-login-custom-footer');
        if (!custom) {
            custom = document.createElement('span');
            custom.className = 'eso-login-custom-footer';
            [...footer.childNodes].forEach((node) => {
                if (node.nodeType === Node.ELEMENT_NODE && node.matches('div[style*="cron.php"]')) return;
                if (node.nodeType === Node.ELEMENT_NODE) {
                    node.classList.add('eso-login-footer-original');
                } else {
                    node.nodeValue = '';
                }
            });
            footer.prepend(custom);
        }
        setPlainText(custom, value);
    }

    function applyLoginTextOverrides(login = {}) {
        const texts = login.texts || {};
        const marker = document.getElementById('esocss-login-config');
        const hookHost = marker?.parentElement || null;
        const form = document.querySelector('body.welcome-anonymous .main-content-card form');

        setPlainText(form?.querySelector('.rich_text_container'), texts.welcome);
        setPlainText(form?.querySelector('label[for="login_name"]'), texts.user_label);
        setPlainText(form?.querySelector('label[for="login_password"]'), texts.password_label);
        setPlainText(
            form?.querySelector('label[for^="dropdown_auth"], label[for*="auth"]'),
            texts.source_label
        );

        const userInput = form?.querySelector('#login_name');
        if (userInput && texts.user_placeholder) userInput.setAttribute('placeholder', texts.user_placeholder);
        const passwordInput = form?.querySelector('#login_password');
        if (passwordInput && texts.password_placeholder) {
            passwordInput.setAttribute('placeholder', texts.password_placeholder);
        }

        setControlText(form?.querySelector('button[name="submit"]'), texts.submit);
        setControlText(form?.querySelector('.forgot_password a'), texts.forgot_password);
        setControlText(form?.querySelector('a[href*="helpdesk.faq.php"]'), texts.faq);

        const nativeRemember = form?.querySelector('label[for="login_remember"] .form-check-label');
        const ssoRemember = form?.querySelector('.singlesignon-login-remember-title');
        const hookRemember = findTextElement(
            form || hookHost,
            'label, .form-check-label, [class*="remember"] span, [id*="remember"] span',
            /lembrar|remember/i
        );
        setControlText(nativeRemember, texts.remember);
        setPlainText(ssoRemember, texts.remember);
        if (hookRemember !== nativeRemember) setControlText(hookRemember, texts.remember);

        const actionRoot = form || hookHost;
        const hookActions = actionRoot
            ? [...actionRoot.querySelectorAll('button, a[href]')].filter((element) => element !== marker)
            : [];
        const formToggle = form?.querySelector('[data-sso-switch="classic"]')
            || hookActions.find((element) => /formul|form|glpi/i.test(element.textContent.trim()))
            || null;
        const ssoControl = form?.querySelector('.singlesignon-login-button') || hookActions.find(
            (element) => element !== formToggle && /entra|entrar|acess|login|sso|saml|openid|microsoft/i.test(element.textContent.trim())
        ) || hookActions.find((element) => element !== formToggle) || null;

        const ssoLabel = ssoControl?.querySelector('.singlesignon-login-label');
        if (ssoLabel) {
            setPlainText(ssoLabel, texts.sso_button);
        } else {
            setControlText(ssoControl, texts.sso_button);
        }
        setControlText(formToggle, texts.form_toggle);
        applyCustomFooterText(texts.footer);
    }

    function syncLoginHero(login = {}, style = 'classic') {
        const page = document.querySelector('body.welcome-anonymous .page-anonymous');
        const container = page?.querySelector('.container-tight');
        const card = container?.querySelector('.main-content-card');
        let hero = document.getElementById('esocss-login-hero');

        if (!page || !['glass', 'portal'].includes(style) || (!login.title && !login.subtitle)) {
            hero?.remove();
            return;
        }

        if (!hero) {
            hero = document.createElement('section');
            hero.id = 'esocss-login-hero';
            hero.className = 'eso-login-hero';
            hero.setAttribute('aria-label', 'Apresentação da área de acesso');

            const accent = document.createElement('span');
            accent.className = 'eso-login-hero-accent';
            accent.setAttribute('aria-hidden', 'true');

            const title = document.createElement('strong');
            title.className = 'eso-login-hero-title';

            const subtitle = document.createElement('p');
            subtitle.className = 'eso-login-hero-subtitle';

            hero.append(accent, title, subtitle);
        }

        hero.classList.toggle('eso-login-hero--glass', style === 'glass');
        hero.classList.toggle('eso-login-hero--portal', style === 'portal');
        if (style === 'glass' && container && card) {
            container.insertBefore(hero, card);
        } else {
            page.appendChild(hero);
        }

        const title = hero.querySelector('.eso-login-hero-title');
        const subtitle = hero.querySelector('.eso-login-hero-subtitle');
        if (title) {
            title.textContent = login.title || '';
            title.hidden = !login.title;
        }
        if (subtitle) {
            subtitle.textContent = login.subtitle || '';
            subtitle.hidden = !login.subtitle;
        }
    }

    function applyLoginTheme(login = {}) {
        const body = document.body;
        if (!body?.classList.contains('welcome-anonymous')) return;

        applyFavicon(login.favicon_url || '');
        markEmptyLoginHookHost();

        const enabled = !!login.enabled;
        body.classList.toggle('eso-login-custom', enabled);
        body.classList.toggle('eso-login-has-logo', enabled && !!login.logo_url);
        body.classList.remove(
            'eso-login-image-panel',
            'eso-login-image-background',
            'eso-login-align-center',
            'eso-login-align-left',
            'eso-login-align-right',
            'eso-login-style-classic',
            'eso-login-style-glass',
            'eso-login-style-portal'
        );
        if (!enabled) {
            syncLoginHero({}, 'classic');
            return;
        }

        const style = ['classic', 'glass', 'portal'].includes(login.style) ? login.style : 'classic';
        const configuredImageMode = ['panel', 'background'].includes(login.image_mode) ? login.image_mode : 'panel';
        const configuredLayout = ['center', 'left', 'right'].includes(login.layout) ? login.layout : 'center';
        const imageMode = style === 'classic' ? configuredImageMode : 'background';
        const layout = style === 'glass'
            ? 'center'
            : (style === 'portal' && configuredLayout === 'center' ? 'right' : configuredLayout);
        body.classList.add(
            `eso-login-image-${imageMode}`,
            `eso-login-align-${layout}`,
            `eso-login-style-${style}`
        );

        const overlayOpacity = Math.max(0, Math.min(90, Number(login.overlay_opacity || 0))) / 100;
        const cardOpacity = Math.max(70, Math.min(100, Number(login.card_opacity || 98))) / 100;
        setCssVariable('--eso-login-background-image', cssUrl(login.background_url));
        setCssVariable('--eso-login-background-position', login.background_position || 'center');
        setCssVariable('--eso-login-background', login.background_color || '#F3F4F6');
        setCssVariable('--eso-login-overlay', colorWithOpacity(login.overlay_color || '#FFFFFF', overlayOpacity));
        setCssVariable('--eso-login-card', colorWithOpacity(login.card_color || '#FFFFFF', cardOpacity));
        setCssVariable('--eso-login-text', login.text_color || '#1F2937');
        setCssVariable('--eso-login-muted', login.muted_color || '#667085');
        setCssVariable('--eso-login-primary', login.primary_color || '#3157D5');
        setCssVariable('--eso-login-border', login.border_color || '#DDE3EC');
        setCssVariable('--eso-login-card-width', `${Math.max(360, Math.min(1200, Number(login.card_width || 920)))}px`);
        setCssVariable('--eso-login-panel-width', `${Math.max(320, Math.min(1000, Number(login.panel_width || 720)))}px`);
        setCssVariable('--eso-login-media-width', `${Math.max(45, Math.min(75, Number(login.media_width || 65)))}vw`);
        setCssVariable('--eso-login-card-radius', `${Math.max(0, Math.min(40, Number(login.card_radius || 16)))}px`);
        setCssVariable('--eso-login-logo-image', cssUrl(login.logo_url));
        setCssVariable('--eso-login-logo-height', `${Math.max(24, Math.min(180, Number(login.logo_max_height || 78)))}px`);
        syncLoginHero(login, style);

        const headings = findLoginHeadings();
        if (login.title && style === 'classic') {
            headings.forEach((candidate) => {
                if (!candidate.dataset.esoOriginalTitle) {
                    candidate.dataset.esoOriginalTitle = candidate.textContent.trim();
                }
                if (candidate.textContent.trim() !== login.title) {
                    candidate.textContent = login.title;
                }
            });
        }
        const heading = style !== 'classic'
            ? null
            : (headings.find((candidate) => candidate.offsetParent !== null) || headings[0] || null);
        if (heading) {
            if (!heading.dataset.esoOriginalTitle) {
                heading.dataset.esoOriginalTitle = heading.textContent.trim();
            }

            let subtitle = heading.parentElement?.querySelector('.eso-login-subtitle');
            if (login.subtitle) {
                if (!subtitle) {
                    subtitle = document.createElement('p');
                    subtitle.className = 'eso-login-subtitle';
                    heading.insertAdjacentElement('afterend', subtitle);
                }
                if (subtitle.textContent !== login.subtitle) {
                    subtitle.textContent = login.subtitle;
                }
            } else {
                subtitle?.remove();
            }
        }

        applyLoginTextOverrides(login);
    }

    function startLoginWatcher(login) {
        let timer = null;
        const observer = new MutationObserver(() => {
            clearTimeout(timer);
            timer = setTimeout(() => applyLoginTheme(login), 120);
        });
        observer.observe(document.body, { childList: true, subtree: true });
        setTimeout(() => observer.disconnect(), 10000);
        setTimeout(() => applyLoginTheme(login), 400);
        setTimeout(() => applyLoginTheme(login), 1400);
    }

    function applyInterfaceTheme(c) {
        const body = document.body;
        if (!body) return;

        applyBranding(c.branding || {});
        body.classList.toggle('eso-theme-enabled', !!c.theme_enabled);
        body.classList.toggle('eso-card-hover', !!c.card_hover);
        body.classList.toggle('eso-header-dark', !!c.header_dark);

        if (!c.theme_enabled) {
            injectCustomCss('');
            applyHelpdeskHome({});
            return;
        }

        const colors = c.colors || {};
        setCssVariable('--eso-primary', colors.primary || '#3157D5');
        setCssVariable('--eso-primary-hover', colors.primary_hover || '#2547B5');
        setCssVariable('--eso-button-bg', colors.button_background || colors.primary || '#3157D5');
        setCssVariable('--eso-button-text', colors.button_text || '#FFFFFF');
        setCssVariable('--eso-button-hover-bg', colors.button_hover_background || colors.primary_hover || '#2547B5');
        setCssVariable('--eso-button-hover-text', colors.button_hover_text || '#FFFFFF');
        setCssVariable('--eso-sidebar-start', colors.sidebar_start || '#172D55');
        setCssVariable('--eso-sidebar-end', colors.sidebar_end || '#10213F');
        setCssVariable('--eso-sidebar-text', colors.sidebar_text || '#F8FAFC');
        setCssVariable('--eso-header-start', colors.header_start || '#10213F');
        setCssVariable('--eso-header-end', colors.header_end || '#172D55');
        setCssVariable('--eso-header-text', colors.header_text || '#F8FAFC');
        setCssVariable('--eso-bg', colors.background || '#F5F7FB');
        setCssVariable('--eso-card', colors.card || '#FFFFFF');
        setCssVariable('--eso-text', colors.text || '#17233C');
        setCssVariable('--eso-muted', colors.muted || '#66758C');
        setCssVariable('--eso-border', colors.border || '#E3E8F0');
        setCssVariable('--eso-menu-bg', colors.menu_background || '#FFFFFF');
        setCssVariable('--eso-menu-text', colors.menu_text || '#374151');
        setCssVariable('--eso-menu-hover-bg', colors.menu_hover_background || '#EEF3FF');
        setCssVariable('--eso-menu-hover-text', colors.menu_hover_text || '#2547B5');
        setCssVariable('--eso-radius', `${Number(c.border_radius || 14)}px`);
        setCssVariable('--eso-shadow-alpha', Math.max(0, Math.min(30, Number(c.shadow_strength || 8))) / 100);

        injectCustomCss(c.custom_css || '');
        applyHelpdeskHome(c.home || {});
    }

    function normalizeSeriesType(series) {
        if (!series) return '';
        if (Array.isArray(series.type)) return series.type[0] || '';
        return series.type || '';
    }

    function styleAxis(axis, colors) {
        const border = colors?.border || '#E3E8F0';
        const muted = colors?.muted || '#66758C';

        return {
            ...axis,
            axisLine: {
                ...(axis?.axisLine || {}),
                lineStyle: {
                    ...(axis?.axisLine?.lineStyle || {}),
                    color: border,
                },
            },
            axisTick: {
                ...(axis?.axisTick || {}),
                lineStyle: {
                    ...(axis?.axisTick?.lineStyle || {}),
                    color: border,
                },
            },
            axisLabel: {
                ...(axis?.axisLabel || {}),
                color: muted,
                fontSize: 11,
            },
            splitLine: {
                ...(axis?.splitLine || {}),
                lineStyle: {
                    ...(axis?.splitLine?.lineStyle || {}),
                    color: border,
                    type: 'dashed',
                },
            },
        };
    }

    function decorateDataItem(item, color, borderRadius = null) {
        if (item !== null && typeof item === 'object' && !Array.isArray(item)) {
            return {
                ...item,
                itemStyle: {
                    ...(item.itemStyle || {}),
                    color,
                    ...(borderRadius !== null ? { borderRadius } : {}),
                },
            };
        }

        return {
            value: item,
            itemStyle: {
                color,
                ...(borderRadius !== null ? { borderRadius } : {}),
            },
        };
    }

    function themeEChart(target, c) {
        if (typeof window.echarts === 'undefined' || typeof window.echarts.getInstanceByDom !== 'function') {
            return false;
        }

        const chart = window.echarts.getInstanceByDom(target);
        if (!chart) return false;

        const option = chart.getOption();
        if (!option || !Array.isArray(option.series)) return false;

        const widget = target.closest('.g-chart');
        const distributed = !!widget?.classList.contains('distributed');
        const horizontal = !!widget?.classList.contains('horizontal');
        const palette = Array.isArray(c.chart_palette) && c.chart_palette.length
            ? c.chart_palette
            : ['#3157D5', '#3B82F6', '#38BDF8', '#14B8A6', '#22C55E', '#8B5CF6', '#F59E0B', '#EF4444'];
        const colors = c.colors || {};
        const cardColor = colors.card || '#FFFFFF';
        const mutedColor = colors.muted || '#66758C';

        const barRadius = Math.max(0, Math.min(20, Number(c.bar_radius || 7)));
        const barRadiusValue = horizontal ? [0, barRadius, barRadius, 0] : [barRadius, barRadius, 0, 0];

        const series = option.series.map((serie, seriesIndex) => {
            const type = normalizeSeriesType(serie);
            const baseColor = palette[seriesIndex % palette.length];

            if (type === 'pie') {
                return {
                    ...serie,
                    color: palette,
                    data: (serie.data || []).map((item, index) => decorateDataItem(item, palette[index % palette.length])),
                    itemStyle: {
                        ...(serie.itemStyle || {}),
                        borderColor: cardColor,
                        borderWidth: 3,
                        borderRadius: 3,
                    },
                    emphasis: {
                        ...(serie.emphasis || {}),
                        scale: true,
                        scaleSize: 5,
                    },
                };
            }

            if (type === 'bar') {
                const data = distributed
                    ? (serie.data || []).map((item, index) => decorateDataItem(item, palette[index % palette.length], barRadiusValue))
                    : serie.data;

                return {
                    ...serie,
                    color: baseColor,
                    data,
                    barMaxWidth: Math.max(8, Math.min(80, Number(c.bar_max_width || 28))),
                    itemStyle: {
                        ...(serie.itemStyle || {}),
                        color: distributed ? undefined : baseColor,
                        borderRadius: barRadiusValue,
                    },
                    emphasis: {
                        ...(serie.emphasis || {}),
                        focus: 'series',
                    },
                };
            }

            if (type === 'line') {
                return {
                    ...serie,
                    color: baseColor,
                    smooth: true,
                    symbol: 'circle',
                    symbolSize: 7,
                    lineStyle: {
                        ...(serie.lineStyle || {}),
                        color: baseColor,
                        width: 3,
                    },
                    itemStyle: {
                        ...(serie.itemStyle || {}),
                        color: baseColor,
                        borderColor: cardColor,
                        borderWidth: 2,
                    },
                };
            }

            return serie;
        });

        const xAxis = Array.isArray(option.xAxis) ? option.xAxis.map((axis) => styleAxis(axis, colors)) : option.xAxis;
        const yAxis = Array.isArray(option.yAxis) ? option.yAxis.map((axis) => styleAxis(axis, colors)) : option.yAxis;

        chart.setOption({
            color: palette,
            textStyle: {
                color: mutedColor,
                fontFamily: 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
            },
            ...(xAxis ? { xAxis } : {}),
            ...(yAxis ? { yAxis } : {}),
            series,
        }, false, true);

        target.dataset.esoCss = '1';
        chart.resize();
        return true;
    }

    function applyChartTheme() {
        if (!config?.theme_enabled || !config?.chart_enabled) return;

        document.querySelectorAll('.g-chart .chart, [_echarts_instance_]').forEach((target) => {
            try {
                themeEChart(target, config);
            } catch (error) {
                console.debug('[ESO CSS] Falha ao tematizar um gráfico:', error);
            }
        });
    }

    function startChartWatcher() {
        if (config?.chart_enabled) {
            let attempts = 0;
            clearInterval(chartTimer);
            chartTimer = setInterval(() => {
                applyChartTheme();
                attempts++;
                if (attempts >= 40) clearInterval(chartTimer);
            }, 500);
        }

        let mutationTimer = null;
        const observer = new MutationObserver(() => {
            clearTimeout(mutationTimer);
            mutationTimer = setTimeout(() => {
                applyHelpdeskHome(config?.home || {});
                applyChartTheme();
            }, 250);
        });
        observer.observe(document.documentElement, { childList: true, subtree: true });

        window.addEventListener('resize', () => setTimeout(applyChartTheme, 150));
    }

    async function loadConfig() {
        try {
            const response = await fetch(`${CONFIG_ENDPOINT}?_=${Date.now()}`, {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: { 'Accept': 'application/json' },
            });
            if (!response.ok) return;
            config = await response.json();
            applyInterfaceTheme(config);
            startChartWatcher();
            setTimeout(applyChartTheme, 600);
            setTimeout(applyChartTheme, 1600);
        } catch (error) {
            console.debug('[ESO CSS] Não foi possível carregar a configuração:', error);
        }
    }

    function initialize() {
        if (document.body?.classList.contains('welcome-anonymous')) {
            loginConfig = readLoginConfig();
            if (loginConfig) {
                applyLoginTheme(loginConfig);
            }
            if (loginConfig?.enabled) {
                startLoginWatcher(loginConfig);
            }
            return;
        }

        loadConfig();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize, { once: true });
    } else {
        initialize();
    }
})();
