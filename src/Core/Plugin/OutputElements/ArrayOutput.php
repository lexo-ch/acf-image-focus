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

        $attachment = acf_get_attachment($formatted_value['image_id']);

        if ($attachment === false) {
            return [];
        }

        $size = $formatted_value['image_size'];

        $is_full = $size === 'full';

        $data['url']            = $is_full ? $attachment['url'] : $attachment['sizes'][$size];
        $data['width']          = $is_full ? $attachment['width'] : $attachment['sizes']["{$size}-width"];
        $data['height']         = $is_full ? $attachment['height'] : $attachment['sizes']["{$size}-height"];
        $data['alt']            = $attachment['alt'];
        $data['author']         = $attachment['author'];
        $data['description']    = $attachment['description'];
        $data['caption']        = $attachment['caption'];
        $data['uploaded_to']    = $attachment['uploaded_to'];
        $data['date']           = $attachment['date'];
        $data['modified']       = $attachment['modified'];
        $data['mime_type']      = $attachment['mime_type'];
        $data['subtype']        = $attachment['subtype'];

        $data = apply_filters("{$this->name}/image/data", $data);
        $data = apply_filters("{$this->name}/image/data/name={$formatted_value['field_name']}", $data);
        $data = apply_filters("{$this->name}/image/data/key={$formatted_value['field_key']}", $data);

        return $data;
    }
}
