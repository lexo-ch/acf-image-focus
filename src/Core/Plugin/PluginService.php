<?php

namespace LEXO\AcfIF\Core\Plugin;

use LEXO\AcfIF\Core\Abstracts\Singleton;
use LEXO\AcfIF\Core\Loader\Loader;
use LEXO\AcfIF\Core\Traits\Helpers;
use LEXO\AcfIF\Core\Updater\PluginUpdater;

use const LEXO\AcfIF\{
    ASSETS,
    FIELD_NAME,
    PLUGIN_NAME,
    PLUGIN_SLUG,
    VERSION,
    MIN_PHP_VERSION,
    MIN_WP_VERSION,
    MIN_ACF_VERSION,
    DOMAIN,
    BASENAME,
    CACHE_KEY,
    UPDATE_PATH
};

class PluginService extends Singleton
{
    use Helpers;

    private static string $namespace    = 'custom-plugin-namespace';
    protected static $instance          = null;
    private const CHECK_UPDATE          = 'check-update-' . PLUGIN_SLUG;


    public function setNamespace(string $namespace)
    {
        self::$namespace = $namespace;
    }

    public function registerNamespace()
    {
        $config = require_once trailingslashit(ASSETS) . 'config/config.php';

        $loader = Loader::getInstance();

        $loader->registerNamespace(self::$namespace, $config);

        add_action('admin_post_' . self::CHECK_UPDATE, [$this, 'checkForUpdateManually']);
    }

    public function addAdminLocalizedScripts()
    {
        $vars = [
            'plugin_name'       => PLUGIN_NAME,
            'field_name'        => FIELD_NAME,
            'plugin_version'    => VERSION,
            'min_acf_version'   => MIN_ACF_VERSION,
            'min_php_version'   => MIN_PHP_VERSION,
            'min_wp_version'    => MIN_WP_VERSION,
            'text_domain'       => DOMAIN,
            'max_width_height'  => 300
        ];

        $vars = apply_filters(self::$namespace . '/admin/localized-script', $vars);

        wp_localize_script(trailingslashit(self::$namespace) . 'admin-acfif.js', 'acfifAdminLocalized', $vars);
    }

    public function addUpdateCheckLink()
    {
        add_filter(
            'plugin_action_links_' . BASENAME,
            [$this, 'setUpdateCheckLink']
        );
    }

    public function setUpdateCheckLink($links)
    {
        $url = self::getManualUpdateCheckLink();

        $settings_link = "<a href='$url'>" . __('Update check', 'acfif') . '</a>';

        array_push(
            $links,
            $settings_link
        );

        return $links;
    }

    public function updater()
    {
        return (new PluginUpdater())
            ->setBasename(BASENAME)
            ->setSlug(PLUGIN_SLUG)
            ->setVersion(VERSION)
            ->setRemotePath(UPDATE_PATH)
            ->setCacheKey(CACHE_KEY)
            ->setCacheExpiration(12 * HOUR_IN_SECONDS)
            ->setCache(true);
    }

    public function checkForUpdateManually()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], self::CHECK_UPDATE)) {
            wp_die(__('Security check failed.', 'acfif'));
        }

        $plugin_settings = PluginService::getInstance();

        if (!$plugin_settings->updater()->hasNewUpdate()) {
            set_transient(
                DOMAIN . '_no_updates_notice',
                sprintf(
                    __('Plugin %s is up to date.', 'acfif'),
                    PLUGIN_NAME
                ),
                HOUR_IN_SECONDS
            );
        } else {
            delete_transient(CACHE_KEY);
        }

        wp_safe_redirect(admin_url('plugins.php'));

        exit;
    }

    public static function nextAutoUpdateCheck()
    {
        $expiration_datetime = get_option('_transient_timeout_' . CACHE_KEY);

        if (!$expiration_datetime) {
            return false;
        }

        return wp_date(get_option('date_format') . ' ' . get_option('time_format'), $expiration_datetime);
    }

    public function noUpdatesNotice()
    {
        $message = get_transient(DOMAIN . '_no_updates_notice');
        delete_transient(DOMAIN . '_no_updates_notice');

        if (!$message) {
            return false;
        }

        $this->notices->add(
            $this->notice->message($message)->type('success')
        );
    }

    public function updateSuccessNotice()
    {
        $message = get_transient(DOMAIN . '_update_success_notice');
        delete_transient(DOMAIN . '_update_success_notice');

        if (!$message) {
            return false;
        }

        $this->notices->add(
            $this->notice->message($message)->type('success')
        );
    }

    public static function getManualUpdateCheckLink(): string
    {
        return esc_url(
            add_query_arg(
                [
                    'action' => self::CHECK_UPDATE,
                    'nonce' => wp_create_nonce(self::CHECK_UPDATE)
                ],
                admin_url('admin-post.php')
            )
        );
    }
}
