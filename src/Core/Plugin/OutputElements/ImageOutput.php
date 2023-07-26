<?php

namespace LEXO\AcfIF\Core\Plugin\OutputElements;

use LEXO\AcfIF\Core\Plugin\Interfaces\OutputInterface;

use const LEXO\AcfIF\{
    DOMAIN
};

class ImageOutput implements OutputInterface
{
    protected string $name;

    public function __construct()
    {
        $this->name = DOMAIN;
    }

    public function getOutputElement(array $formatted_value): ?string
    {
        $image_data = $this->getImageData($formatted_value);

        $style_attr = [
            'object-fit'        => 'cover',
            'object-position'   => "{$formatted_value['position_x']}% {$formatted_value['position_y']}%",
            'aspect-ratio'      => "{$formatted_value['aspect_ratio']}",
            'height'            => 'auto',
            'max-width'         => '100%'
        ];

        $style_attr = apply_filters("{$this->name}/image/style-attribute", $style_attr);
        $style_attr = apply_filters("{$this->name}/image/style-attribute/name={$formatted_value['field_name']}", $style_attr);
        $style_attr = apply_filters("{$this->name}/image/style-attribute/key={$formatted_value['field_key']}", $style_attr);

        $classes = [
            'acf-image-focus'
        ];

        $classes = apply_filters("{$this->name}/image/classes", $classes);
        $classes = apply_filters("{$this->name}/image/classes/name={$formatted_value['field_name']}", $classes);
        $classes = apply_filters("{$this->name}/image/classes/key={$formatted_value['field_key']}", $classes);

        $attrs = [
            'data-image-id' => $formatted_value['image_id'],
            'loading'       => 'lazy',
            'decoding'      => 'async',
            'width'         => $image_data['width'],
            'height'        => $image_data['height'],
            'src'           => $image_data['url'],
            'alt'           => $image_data['alt']
        ];

        if ($style_attr) {
            $attrs['style'] = implode('; ', $this->arrayMapAssoc(function ($k, $v) {
                return "$k: $v";
            }, $style_attr));
        }

        if ($classes) {
            $attrs['class'] = implode(' ', $classes);
        }

        $attrs = apply_filters("{$this->name}/image/attributes", $attrs);
        $attrs = apply_filters("{$this->name}/image/attributes/name={$formatted_value['field_name']}", $attrs);
        $attrs = apply_filters("{$this->name}/image/attributes/key={$formatted_value['field_key']}", $attrs);

        $image = '<img ' . acf_esc_attrs($attrs) . '>';

        $image = apply_filters("{$this->name}/image/element", $image);
        $image = apply_filters("{$this->name}/image/element/name={$formatted_value['field_name']}", $image);
        $image = apply_filters("{$this->name}/image/element/key={$formatted_value['field_key']}", $image);

        return $image;
    }

    public function arrayMapAssoc($callback, array $array): array
    {
        $r = [];

        foreach ($array as $key => $value) {
            $r[$key] = $callback($key, $value);
        }

        return $r;
    }

    public function getImageData(array $formatted_value): array
    {
        $attachment = acf_get_attachment($formatted_value['image_id']);

        $size = $formatted_value['image_size'];

        $is_full = $size === 'full';

        return [
            'url'       => $is_full ? $attachment['url'] : $attachment['sizes'][$size],
            'width'     => $is_full ? $attachment['width'] : $attachment['sizes']["{$size}-width"],
            'height'    => $is_full ? $attachment['height'] : $attachment['sizes']["{$size}-height"],
            'alt'       => !empty($attachment['alt']) ? $attachment['alt'] : $attachment['title']
        ];
    }
}
