<?php

namespace LEXO\AcfIF\Core\Loader;

use LEXO\AcfIF\Core\Loader\Manifest;

interface LoaderInterface
{
    public function loadStyles(string $namespace, Manifest $manifest, array $assets);

    public function loadScripts(string $namespace, Manifest $manifest, array $assets);

    public function loadEditorStyles(string $namespace, Manifest $manifest, array $assets);

    /**
     * Get the cache-busted URI
     *
     * If the manifest does not have an entry for $asset, then return URI for $asset
     *
     * @param string $asset The original name of the file before cache-busting
     * @return string
     */
    public function getUri(string $namespace, string $asset): string;

    /**
     * Get the cache-busted path
     *
     * If the manifest does not have an entry for $asset, then return URI for $asset
     *
     * @param  string $asset The original name of the file before cache-busting
     * @return string
     */
    public function getPath(string $namespace, string $asset): string;

    /**
     * Get the version of the file from the mix-maniest.json
     *
     * @param Manifest $manifest
     * @param string $file
     *
     * @return string
     * @author Miljan Puzovic
     */
    public function getVersionFromManifest(Manifest $manifest, string $file): string;
}
