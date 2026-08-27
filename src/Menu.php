<?php

namespace GlpiPlugin\EsoCss;

/**
 * Adds a direct ESO CSS shortcut to GLPI's Configuration menu.
 */
final class Menu extends \CommonGLPI
{
    public static $rightname = 'config';

    public static function getTypeName($nb = 0)
    {
        return __('ESO CSS for GLPI', 'esocss');
    }

    public static function getMenuName($nb = 0)
    {
        return self::getTypeName($nb);
    }

    public static function getMenuContent()
    {
        if (!\Session::haveRight(self::$rightname, UPDATE)) {
            return false;
        }

        return [
            'title' => self::getMenuName(),
            'page'  => '/plugins/esocss/front/settings.php',
            'icon'  => 'ti ti-palette',
        ];
    }
}
