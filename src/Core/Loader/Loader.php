<?php

namespace LEXO\AcfIF\Core\Loader;

use LEXO\AcfIF\Core\Loader\Manifest;
use LEXO\AcfIF\Core\Abstracts\Singleton;

class Loader extends Singleton implements LoaderInterface
{
    private static ?string $hook = null;

    private static ?string $context = null;

    protected static $instance = null;

    private array $namespaces;

    public function __construct()
    {
        self::$hook    = !is_admin() ? 'wp_enqueue_scripts' : 'admin_enqueue_scripts';
        self::$context = !is_admin() ? 'front' : 'admin';

        $this->namespaces = [];

        add_action(self::$hook, [$this, 'run'], -1);

        if (self::$context === 'admin') {
            add_action('admin_init', [$this, 'runEditor']);
        }
    }

    public function registerNamespace(string $namespace, array $data)
    {
        $this->namespaces[$namespace] = [
            'assets'   => $data['assets'],
            'priority' => $data['priority'] ?? 50,
            'manifest' => new Manifest(
                $data['dist_path'] . '/mix-manifest.json',
                $data['dist_uri'],
                $data['dist_path']
            ),
        ];
    }

    public function run()
    {
        foreach ($this->namespaces as $namespace => $data) {
            add_action(self::$hook, function () use ($namespace, $data) {

                $this->loadStyles(
                    $namespace,
                    $data['manifest'],
                    $data['assets'][self::$context]['styles']
                );

                $this->loadScripts(
                    $namespace,
                    $data['manifest'],
                    $data['assets'][self::$context]['scripts']
                );
            }, $data['priority']);
        }
    }

    public function runEditor()
    {
        foreach ($this->namespaces as $namespace => $data) {
            if (!empty($data['assets']['editor']['styles'])) {
                add_action('admin_init', function () use ($namespace, $data) {

                    $this->loadEditorStyles(
                        $namespace,
                        $data['manifest'],
                        $data['assets']['editor']['styles']
                    );
                }, $data['priority']);
            }
        }
    }

    public function loadStyles(string $namespace, Manifest $manifest, array $assets)
    {
        $load_styles = apply_filters("{$namespace}/load_styles", true);

        if (!$load_styles) {
            return;
        }

        foreach ($assets as $style) {
            $basename = basename($style);
            $handler  = "{$namespace}/{$basename}";

            if (!apply_filters("{$namespace}/enqueue/{$basename}", true)) {
                continue;
            }

            $version = $this->getVersionFromManifest($manifest, $style);

            wp_register_style(
                $handler,
                $manifest->getUri($style),
                [],
                $version
            );
            wp_enqueue_style($handler);

            add_filter('style_loader_src', function ($src, $handle) use ($handler, $version) {
                if ($handle !== $handler) {
                    return $src;
                }

                return add_query_arg(
                    [
                        'ver' => $version
                    ],
                    $src
                );
            }, PHP_INT_MAX, 2);

            add_filter('style_loader_tag', function ($html, $handle, $href, $media) use ($handler, $version) {
                if ($handle !== $handler) {
                    return $html;
                }

                ob_start(); ?>
                    <link
                        rel="preload"
                        href="<?php echo $href; ?>"
                        as="style"
                        id="<?php echo $handle; ?>"
                        media="<?php echo $media; ?>"
                        onload="this.onload=null;this.rel='stylesheet'"
                    >
                    <noscript><?php echo trim($html); ?></noscript>
                <?php return ob_get_clean();
            }, PHP_INT_MAX, 4);
        }
    }

    public function loadScripts(string $namespace, Manifest $manifest, array $assets)
    {
        $load_scripts = apply_filters("{$namespace}/load_scripts", true);

        if (!$load_scripts) {
            return;
        }

        foreach ($assets as $script) {
            $basename = basename($script);
            $handler  = "{$namespace}/{$basename}";

            if (!apply_filters("{$namespace}/enqueue/{$basename}", true)) {
                continue;
            }

            $version = $this->getVersionFromManifest($manifest, $script);

            wp_register_script(
                $handler,
                $manifest->getUri($script),
                [],
                $version,
                [
                    'strategy'  => 'defer',
                    'in_footer' => false
                ]
            );

            do_action("{$namespace}/localize/$basename");
            wp_enqueue_script($handler);

            add_filter('script_loader_src', function ($src, $handle) use ($handler, $version) {
                if ($handle !== $handler) {
                    return $src;
                }

                return add_query_arg(
                    [
                        'ver' => $version
                    ],
                    $src
                );
            }, PHP_INT_MAX, 2);
        }
    }

    public function loadEditorStyles(string $namespace, Manifest $manifest, array $assets)
    {
        $load_editor_styles = apply_filters("{$namespace}/load_editor_styles", true);

        if (!$load_editor_styles) {
            return;
        }

        $editor_styles = [];

        foreach ($assets as $style) {
            $basename = basename($style);

            if (!apply_filters("{$namespace}/add_editor_style/{$basename}", true)) {
                continue;
            }

            $editor_styles[] = $manifest->getUri($style)
                                . '?manifest-ver='
                                . $this->getVersionFromManifest($manifest, $style);
        }

        add_editor_style($editor_styles);
    }

    public function getUri(string $namespace, string $asset): string
    {
        return $this->namespaces[$namespace]['manifest']->getUri($asset);
    }

    public function getPath(string $namespace, string $asset): string
    {
        return $this->namespaces[$namespace]['manifest']->getPath($asset);
    }

    public function getVersionFromManifest(Manifest $manifest, string $file): string
    {
        return explode('?id=', $manifest->manifest["/{$file}"])[1] ?? 'generic-1.0.0';
    }
}
