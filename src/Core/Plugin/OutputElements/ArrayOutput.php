<?php

namespace LEXO\AcfIF\Core\Plugin\OutputElements;

use LEXO\AcfIF\Core\Plugin\Interfaces\OutputInterface;

use const LEXO\AcfIF\{
    DOMAIN
};

class ArrayOutput implements OutputInterface
{
    protected string $name;

    public function __construct()
    {
        $this->name = DOMAIN;
    }

    public function getOutputElement(array $formatted_value): array
    {
        $data = $formatted_value;

        $image_atts = wp_get_attachment_image_src($formatted_value['image_id'], $formatted_value['image_size']);

        if ($image_atts !== false) {
            $data['url'] = $image_atts[0];
            $data['width'] = $image_atts[1];
            $data['height'] = $image_atts[2];
        }

        $data = apply_filters("{$this->name}/image/data", $data);
        $data = apply_filters("{$this->name}/image/data/name={$formatted_value['field_name']}", $data);
        $data = apply_filters("{$this->name}/image/data/key={$formatted_value['field_key']}", $data);

        return $data;
    }
}
