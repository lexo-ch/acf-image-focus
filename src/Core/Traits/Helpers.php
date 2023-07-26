<?php

namespace LEXO\AcfIF\Core\Traits;

use LEXO\AcfIF\Core\Notices\Notice;
use LEXO\AcfIF\Core\Notices\Notices;

use const LEXO\AcfIF\{
    PLUGIN_NAME
};

trait Helpers
{
    public $notice;
    public $notices;

    public function __construct()
    {
        $this->notice = new Notice();
        $this->notices = new Notices();
    }

    public static function getClassName($classname)
    {
        if ($name = strrpos($classname, '\\')) {
            return substr($classname, $name + 1);
        };

        return $name;
    }

    public static function setStatus404()
    {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
    }

    public static function printr(mixed $data): string
    {
        return "<pre>" . \print_r($data, true) . "</pre>";
    }

    public static function getSettingsNames(): array
    {
        return [
            'return_format' => __('Return Format', 'acf'),
            'aspect_ratio'  => __('Image Aspect Ratio', 'acfif'),
            'preview_size'  => __('Image Size', 'acfif'),
            'library'       => __('Library', 'acf'),
            'min_width'     => __('Minimum', 'acf'),
            'min_height'    => '',
            'min_size'      => '',
            'max_width'     => __('Maximum', 'acf'),
            'max_height'    => '',
            'max_size'      => '',
            'mime_types'    => __('Allowed file types', 'acf'),
        ];
    }

    public static function getRenderFieldSettings(): array
    {
        $settings_names = self::getSettingsNames();

        return [
            'return_format' => [
                'label'         => $settings_names['return_format'],
                'instructions'  => '',
                'type'          => 'radio',
                'name'          => 'return_format',
                'layout'        => 'horizontal',
                'choices'       => [
                    'array'         => __('Image Array', 'acf'),
                    'image_element' => __('Image Element', 'acfif'),
                ],
            ],
            'aspect_ratio' => [
                'label'         => $settings_names['aspect_ratio'],
                'instructions'  => __('Applies to the image in the frontend.', 'acfif'),
                'type'          => 'number',
                'min'           => 0.001,
                'name'          => 'aspect_ratio',
            ],
            'preview_size' => [
                'label'         => $settings_names['preview_size'],
                'instructions'  => sprintf(__('Image size used for %s', 'acfif'), PLUGIN_NAME),
                'type'          => 'select',
                'name'          => 'preview_size',
                'choices'       => acf_get_image_sizes(),
            ],
            'library' => [
                'label'         => $settings_names['library'],
                'instructions'  => __('Limit the media library choice', 'acf'),
                'type'          => 'radio',
                'name'          => 'library',
                'layout'        => 'horizontal',
                'choices'       => [
                    'all'           => __('All', 'acf'),
                    'uploadedTo'    => __('Uploaded to post', 'acf')
                ],
            ],
            'min_width' => [
                'label'         => $settings_names['min_width'],
                'hint'          => __('Restrict which images can be uploaded', 'acf'),
                'type'          => 'text',
                'name'          => 'min_width',
                'prepend'       => __('Width', 'acf'),
                'append'        => 'px',
            ],
            'min_height' => [
                'label'         => $settings_names['min_height'],
                'type'          => 'text',
                'name'          => 'min_height',
                'prepend'       => __('Height', 'acf'),
                'append'        => 'px',
                '_append'       => 'min_width'
            ],
            'min_size' => [
                'label'         => $settings_names['min_size'],
                'type'          => 'text',
                'name'          => 'min_size',
                'prepend'       => __('File size', 'acf'),
                'append'        => 'MB',
                '_append'       => 'min_width'
            ],
            'max_width' => [
                'label'         => $settings_names['max_width'],
                'hint'          => __('Restrict which images can be uploaded', 'acf'),
                'type'          => 'text',
                'name'          => 'max_width',
                'prepend'       => __('Width', 'acf'),
                'append'        => 'px',
            ],
            'max_height' => [
                'label'         => $settings_names['max_height'],
                'type'          => 'text',
                'name'          => 'max_height',
                'prepend'       => __('Height', 'acf'),
                'append'        => 'px',
                '_append'       => 'max_width'
            ],
            'max_size' => [
                'label'         => $settings_names['max_size'],
                'type'          => 'text',
                'name'          => 'max_size',
                'prepend'       => __('File size', 'acf'),
                'append'        => 'MB',
                '_append'       => 'max_width'
            ],
            'mime_types' => [
                'label'         => $settings_names['mime_types'],
                'instructions'  => __('Comma separated list. Leave blank for all types', 'acf'),
                'type'          => 'text',
                'name'          => 'mime_types',
            ],
        ];
    }
}
