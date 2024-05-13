<?php

namespace LEXO\AcfIF\Core;

use Exception;
use LEXO\AcfIF\Core\Abstracts\Singleton;
use LEXO\AcfIF\Core\Plugin\AcfImageFocusField;
use LEXO\AcfIF\Core\Plugin\PluginService;
use LEXO\AcfIF\Core\Notices\Notices;

use const LEXO\AcfIF\{
    DOMAIN,
    PATH,
    LOCALES,
    MIN_ACF_VERSION,
    PLUGIN_NAME
};

class Bootloader extends Singleton
{
    protected static $instance = null;

    public array $settings = [];

    public function run()
    {
        add_action('acf/init', [$this, 'onAcfInit'], 10);
        add_action(DOMAIN . '/localize/admin-acfif.js', [$this, 'onAdminAcfifJsLoad']);
        add_action('acf/include_field_types', [$this, 'initField']);
        add_action('after_setup_theme', [$this, 'onAfterSetupTheme']);
    }

    public function onAcfInit()
    {
        do_action(DOMAIN . '/init');

        !defined('LEXO\AcfIF\Core\ACF_MAJOR_VERSION')
        && define('LEXO\AcfIF\Core\ACF_MAJOR_VERSION', ACF_MAJOR_VERSION);

        if (version_compare(ACF_MAJOR_VERSION, MIN_ACF_VERSION, '<')) {
            throw new Exception(
                sprintf(
                    __('%s requires at least ACF %s!', 'acfif'),
                    PLUGIN_NAME,
                    MIN_ACF_VERSION
                )
            );
        }

        wp_enqueue_media();

        $plugin_settings = PluginService::getInstance();
        $plugin_settings->setNamespace(DOMAIN);
        $plugin_settings->registerNamespace();
        $plugin_settings->addUpdateCheckLink();
        $plugin_settings->noUpdatesNotice();
        $plugin_settings->updateSuccessNotice();

        (new Notices())->run();
    }

    public function onAdminAcfifJsLoad()
    {
        PluginService::getInstance()->addAdminLocalizedScripts();
    }

    public function onAfterSetupTheme()
    {
        $this->loadPluginTextdomain();
        PluginService::getInstance()->updater()->run();
    }

    public function initField()
    {
        if (version_compare(ACF_MAJOR_VERSION, MIN_ACF_VERSION, '>=')) {
            new AcfImageFocusField();
        }
    }

    public function loadPluginTextdomain()
    {
        load_plugin_textdomain(DOMAIN, false, trailingslashit(trailingslashit(basename(PATH)) . LOCALES));
    }
}
