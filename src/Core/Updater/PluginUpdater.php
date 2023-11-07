<?php

namespace LEXO\AcfIF\Core\Updater;

use Exception;
use stdClass;

class PluginUpdater
{
    private array $args = [
        'basename'          => '',
        'slug'              => '',
        'version'           => '',
        'remote_path'       => '',
        'remote_args'       => [
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/json'
            ]
        ],
        'cache_key'         => 'custom_cache_key_update',
        'cache_expiration'  => DAY_IN_SECONDS,
        'cache'             => true,
    ];

    public function setBasename(string $basename): PluginUpdater
    {
        $this->args['basename'] = $basename;
        return $this;
    }

    public function setSlug(string $slug): PluginUpdater
    {
        $this->args['slug'] = $slug;
        return $this;
    }

    public function setVersion(string $version): PluginUpdater
    {
        $this->args['version'] = $version;
        return $this;
    }

    public function setRemotePath(string $remote_path): PluginUpdater
    {
        $this->args['remote_path'] = $remote_path;
        return $this;
    }

    public function setRemoteArgs(array $remote_args): PluginUpdater
    {
        $this->args['remote_args'] = $remote_args;
        return $this;
    }

    public function setCacheKey(string $cache_key): PluginUpdater
    {
        $this->args['cache_key'] = $cache_key;
        return $this;
    }

    public function setCacheExpiration(float $cache_expiration): PluginUpdater
    {
        $this->args['cache_expiration'] = ceil($cache_expiration);
        return $this;
    }

    public function setCache(bool $cache): PluginUpdater
    {
        $this->args['cache'] = $cache;
        return $this;
    }

    private function validateInputs(): bool
    {
        foreach ($this->args as $key => $arg) {
            if (!is_bool($this->args[$key]) && empty($arg)) {
                throw new Exception("Parameter {$key} is missing.");
            }
        }

        return true;
    }

    public function run(): void
    {
        $this->validateInputs();

        add_filter('plugins_api', [$this, 'info'], 20, 3);
        add_filter('site_transient_update_plugins', [$this, 'update']);
        add_action('upgrader_process_complete', [$this, 'purge'], 10, 2);
    }

    public function request()
    {
        $remote = get_transient($this->args['cache_key']);

        if (false === $remote || !$this->args['cache']) {
            $remote = $this->getRemoteData();

            if ($remote === false) {
                return false;
            }

            set_transient($this->args['cache_key'], $remote, $this->args['cache_expiration']);
        }

        $remote = json_decode(wp_remote_retrieve_body($remote));

        return $remote;
    }

    public function getRemoteData()
    {
        $remote = wp_remote_get($this->args['remote_path'], $this->args['remote_args']);

        if (
            is_wp_error($remote)
            || 200 !== wp_remote_retrieve_response_code($remote)
            || empty(wp_remote_retrieve_body($remote))
        ) {
            return false;
        }

        return $remote;
    }

    public function info($res, $action, $args)
    {
        if ('plugin_information' !== $action) {
            return $res;
        }

        if ($this->args['slug'] !== $args->slug) {
            return $res;
        }

        $remote = $this->request();

        if (!$remote) {
            return $res;
        }

        $sections = [
            'description'   => $remote->sections->description,
            'changelog'     => $remote->sections->changelog
        ];

        $sections = apply_filters("{$this->args['slug']}/plugin_sections", $sections);

        $res = new stdClass();

        $res->name              = $remote->name;
        $res->slug              = $remote->slug;
        $res->version           = $remote->version;
        $res->tested            = $remote->tested;
        $res->requires          = $remote->requires;
        $res->author            = $remote->author;
        $res->author_profile    = $remote->author_profile;
        $res->download_link     = $remote->download_url;
        $res->trunk             = $remote->download_url;
        $res->requires_php      = $remote->requires_php;
        $res->donate_link       = $remote->donate_link;
        $res->sections          = $sections;

        // $res->last_updated   = $remote->last_updated;

        if (!empty($remote->banners)) {
            $res->banners = [
                'low'   => $remote->banners->low,
                'high'  => $remote->banners->high
            ];
        }


    // in case you want the screenshots tab, use the following HTML format for its content:
    // <ol><li><a href="IMG_URL" target="_blank"><img src="IMG_URL" alt="CAPTION" /></a><p>CAPTION</p></li></ol>
        if (!empty($remote->sections->screenshots)) {
            $res->sections['screenshots'] = $remote->sections->screenshots;
        }

        return $res;
    }

    public function update($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $remote = $this->request();

        if (
            $remote
            && version_compare($this->args['version'], $remote->version, '<')
            && version_compare($remote->requires, get_bloginfo('version'), '<')
            && version_compare($remote->requires_php, PHP_VERSION, '<')
        ) {
            $res = new stdClass();

            $res->slug          = $remote->slug;
            $res->plugin        = $this->args['basename'];
            $res->new_version   = $remote->version;
            $res->tested        = $remote->tested;
            $res->package       = $remote->download_url;

            $transient->response[$res->plugin] = $res;
        }

        return $transient;
    }

    public function purge($upgrader, $options)
    {
        if (
            $this->args['cache']
            && 'update' === $options['action']
            && 'plugin' === $options[ 'type' ]
        ) {
            delete_transient($this->args['cache_key']);
        }
    }

    public function hasNewUpdate()
    {
        $remote = $this->getRemoteData();

        if ($remote === false) {
            return false;
        }

        $remote = json_decode(wp_remote_retrieve_body($remote));

        if (
            $remote
            && version_compare($this->args['version'], $remote->version, '<')
            && version_compare($remote->requires, get_bloginfo('version'), '<')
            && version_compare($remote->requires_php, PHP_VERSION, '<')
        ) {
            // There is a new update available
            return true;
        }

        // No update available
        return false;
    }
}
