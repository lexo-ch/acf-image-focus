<?php

namespace LEXO\AcfIF\Core\Loader;

/**
 * Interface ManifestInterface
 */
interface ManifestInterface
{
    /**
     * Get the cache-busted filename
     *
     * If the manifest does not have an entry for $asset, then return $asset
     *
     * @param  string $asset The original name of the file before cache-busting
     * @return string
     */
    public function get($asset);

    /**
     * Get the cache-busted URI
     *
     * If the manifest does not have an entry for $asset, then return URI for $asset
     *
     * @param string $asset The original name of the file before cache-busting
     * @return string
     */
    public function getUri($asset);

    /**
     * Get the cache-busted path
     *
     * If the manifest does not have an entry for $asset, then return URI for $asset
     *
     * @param  string $asset The original name of the file before cache-busting
     * @return string
     */
    public function getPath($asset);
}
