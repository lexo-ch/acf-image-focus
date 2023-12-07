<?php

/**
 * Plugin Name:       LEXO ACF Image Focus
 * Plugin URI:        https://github.com/lexo-ch/acf-image-focus/
 * Description:       ACF extension for displaying the images with different proportions in predefined "frame" by setting position, without cropping.
 * Version:           1.1.2
 * Requires at least: 4.7
 * Requires PHP:      7.4.1
 * Author:            LEXO GmbH
 * Author URI:        https://www.lexo.ch
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       acfif
 * Domain Path:       /languages
 * Update URI:        acf-image-focus
 */

namespace LEXO\AcfIF;

use Exception;
use LEXO\AcfIF\Activation;
use LEXO\AcfIF\Deactivation;
use LEXO\AcfIF\Uninstalling;
use LEXO\AcfIF\Core\Bootloader;

// Prevent direct access
!defined('WPINC')
    && die;

// Define Main plugin file
!defined('LEXO\AcfIF\FILE')
    && define('LEXO\AcfIF\FILE', __FILE__);

// Define plugin name
!defined('LEXO\AcfIF\PLUGIN_NAME')
    && define('LEXO\AcfIF\PLUGIN_NAME', get_file_data(FILE, [
        'Plugin Name' => 'Plugin Name'
    ])['Plugin Name']);

// Define plugin slug
!defined('LEXO\AcfIF\PLUGIN_SLUG')
    && define('LEXO\AcfIF\PLUGIN_SLUG', get_file_data(FILE, [
        'Update URI' => 'Update URI'
    ])['Update URI']);

// Define Basename
!defined('LEXO\AcfIF\BASENAME')
    && define('LEXO\AcfIF\BASENAME', plugin_basename(FILE));

// Define internal path
!defined('LEXO\AcfIF\PATH')
    && define('LEXO\AcfIF\PATH', plugin_dir_path(FILE));

// Define assets path
!defined('LEXO\AcfIF\ASSETS')
    && define('LEXO\AcfIF\ASSETS', trailingslashit(PATH) . 'assets');

// Define internal url
!defined('LEXO\AcfIF\URL')
    && define('LEXO\AcfIF\URL', plugin_dir_url(FILE));

// Define internal version
!defined('LEXO\AcfIF\VERSION')
    && define('LEXO\AcfIF\VERSION', get_file_data(FILE, [
        'Version' => 'Version'
    ])['Version']);

// Define min PHP version
!defined('LEXO\AcfIF\MIN_PHP_VERSION')
    && define('LEXO\AcfIF\MIN_PHP_VERSION', '7.4.1');

// Define min WP version
!defined('LEXO\AcfIF\MIN_WP_VERSION')
    && define('LEXO\AcfIF\MIN_WP_VERSION', '4.7');

// Define min ACF version
!defined('LEXO\AcfIF\MIN_ACF_VERSION')
    && define('LEXO\AcfIF\MIN_ACF_VERSION', 5);

// Define Text domain
!defined('LEXO\AcfIF\DOMAIN')
    && define('LEXO\AcfIF\DOMAIN', 'acfif');

// Define locales folder (with all translations)
!defined('LEXO\AcfIF\LOCALES')
    && define('LEXO\AcfIF\LOCALES', 'languages');

!defined('LEXO\AcfIF\FIELD_NAME')
    && define('LEXO\AcfIF\FIELD_NAME', 'acf_image_focus');

!defined('LEXO\AcfIF\CACHE_KEY')
    && define('LEXO\AcfIF\CACHE_KEY', DOMAIN . '_cache_key_update');

!defined('LEXO\AcfIF\UPDATE_PATH')
    && define('LEXO\AcfIF\UPDATE_PATH', 'https://wprepo.lexo.ch/public/acf-image-focus/info.json');

if (!file_exists($composer = PATH . '/vendor/autoload.php')) {
    wp_die('Error locating autoloader in LEXO ACF Image Focus.
        Please run a following command:<pre>composer install</pre>', 'acfif');
}

require $composer;

register_activation_hook(FILE, function () {
    (new Activation())->run();
});

register_deactivation_hook(FILE, function () {
    (new Deactivation())->run();
});

if (!function_exists('acfif_uninstall')) {
    function acfif_uninstall()
    {
        (new Uninstalling())->run();
    }
}
register_uninstall_hook(FILE, __NAMESPACE__ . '\acfif_uninstall');

// Run the plugin
if (!function_exists('run_cf')) {
    function run_cf()
    {
        global $wp_version;

        if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) {
            throw new Exception(
                sprintf(
                    __('%s requires PHP %s or greater.<br/>
                    Go back to <a href="%s">Dashboard</a>', 'acfif'),
                    PLUGIN_NAME,
                    MIN_PHP_VERSION,
                    get_dashboard_url()
                )
            );
        }

        if (version_compare($wp_version, MIN_WP_VERSION, '<')) {
            throw new Exception(
                sprintf(
                    __('%s requires WordPress %s or greater.<br/>
                    Go back to <a href="%s">Dashboard</a>', 'acfif'),
                    PLUGIN_NAME,
                    MIN_WP_VERSION,
                    get_dashboard_url()
                )
            );
        }

        Bootloader::getInstance()->run();
    }
}

try {
    run_cf();
} catch (Exception $e) {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');

    deactivate_plugins(FILE);

    wp_die($e->getMessage());
}
