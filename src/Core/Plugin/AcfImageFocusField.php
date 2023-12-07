<?php

namespace LEXO\AcfIF\Core\Plugin;

use acf_field;
use LEXO\AcfIF\Core\Plugin\OutputElements\ArrayOutput;
use LEXO\AcfIF\Core\Plugin\OutputElements\ImageOutput;
use LEXO\AcfIF\Core\Traits\Helpers;

use const LEXO\AcfIF\{
    VERSION,
    URL,
    PATH,
    FIELD_NAME,
    PLUGIN_NAME,
    FILE
};
use const LEXO\AcfIF\Core\{
    ACF_MAJOR_VERSION
};

class AcfImageFocusField extends acf_field
{
    use Helpers;

    protected array $render_field_settings;

    public function __construct()
    {
        $this->render_field_settings = self::getRenderFieldSettings();

        $this->name = FIELD_NAME;

        $this->settings = [
            'version'   => VERSION,
            'url'       => URL,
            'path'      => PATH,
        ];

        $this->label = PLUGIN_NAME;

        $this->description = get_file_data(FILE, [
            'Description' => 'Description'
        ])['Description'];

        $this->doc_url = get_file_data(FILE, [
            'Plugin URI' => 'Plugin URI'
        ])['Plugin URI'];

        $this->preview_image = 'https://raw.githubusercontent.com/lexo-ch/acf-image-focus/master/screenshots/3.jpeg';

        $this->category = 'content';

        $this->defaults = [
            'return_format' => 'array',
            'preview_size'  => 'medium',
            'library'       => 'all',
            'max_size'      => 1,
            'mime_types'    => 'jpeg, jpg, png, webp',
            'aspect_ratio'  => 1.778
        ];

        parent::__construct();
    }

    public function render_field_settings($field)
    {
        $clear = [
            'min_width',
            'min_height',
            'min_size',
            'max_width',
            'max_height',
            'max_size',
            'aspect_ratio',
            'acfif_field_classes'
        ];

        foreach ($clear as $k) {
            if (empty($field[$k])) {
                $field[$k] = '';
            }
        }

        /*
        |-----------------------------------------------------------------------
        | If ACF_MAJOR_VERSION >= 6, use following functions:
        |-----------------------------------------------------------------------
        |
        | * render_field_general_settings()
        | * render_field_validation_settings()
        | * render_field_presentation_settings()
        | * render_field_conditional_logic_settings()
        |
        */

        if (version_compare(ACF_MAJOR_VERSION, 6, '>=')) {
            return;
        }

        foreach ($this->render_field_settings as $settings) {
            acf_render_field_setting($field, $settings);
        }
    }

    public function renderTabSettings($field, array $tab_fields)
    {
        foreach ($tab_fields as $tab_field) {
            acf_render_field_setting($field, $this->render_field_settings[$tab_field]);
        }
    }

    public function render_field_general_settings($field)
    {
        $this->renderTabSettings($field, [
            'return_format',
            'library',
        ]);
    }

    public function render_field_validation_settings($field)
    {
        $this->renderTabSettings($field, [
            'min_width',
            'min_height',
            'min_size',
            'max_width',
            'max_height',
            'max_size',
            'mime_types'
        ]);
    }

    public function render_field_presentation_settings($field)
    {
        $this->renderTabSettings($field, [
            'preview_size',
            'aspect_ratio',
            'acfif_field_classes'
        ]);
    }

    public function getNotificationsForChangedSettings($field, array $settings): string
    {
        $notifications = '';

        $settings_names = self::getSettingsNames();

        foreach ($settings as $setting) {
            if (!isset($field['value'][$setting]) || $field['value'][$setting] == $field[$setting]) {
                continue;
            }

            ob_start(); ?>
                <div class="acf-notice -warning acf-error-message acfif-notice">
                    <p>
                        <?php printf(
                            __('%s for this image has been changed from %s to %s. Please adjust the focus again and save this post.', 'acfif'),
                            "<strong>{$settings_names[$setting]}</strong>",
                            "<code>{$field['value'][$setting]}</code>",
                            "<code>{$field[$setting]}</code>"
                        ); ?>
                    </p>
                </div>
            <?php $notifications .= ob_get_clean();
        }

        return $notifications;
    }

    public function render_field($field)
    {
        $field = array_merge($this->defaults, $field);

        $id = $field['value']['image_id'] ?? '';

        $data = [
            'image_id'      => $id,
            'preview_size'  => $field['preview_size'],
            'aspect_ratio'  => $field['aspect_ratio'],
            'canvas_top'    => $field['value']['canvas_top'] ?? '',
            'canvas_left'   => $field['value']['canvas_left'] ?? '',
            'position_x'    => $field['value']['position_x'] ?? '',
            'position_y'    => $field['value']['position_y'] ?? '',
        ];

        $img = $id ? wp_get_attachment_image_src($id, $field['preview_size']) : '';
        $url = ($id && $img) ? $img[0] : '';

        $acf_image_focus_classes = [
            'acf-image-focus',
            'acf-image-uploader'
        ];

        if ($img) {
            $acf_image_focus_classes[] = 'active';
            $acf_image_focus_classes[] = 'has-value';
        }

        echo $this->getNotificationsForChangedSettings($field, [
            'aspect_ratio',
            'preview_size'
        ]); ?>

        <div
            class="<?php echo esc_attr(implode(' ', $acf_image_focus_classes)); ?>"
            data-preview_size="<?php echo $field['preview_size']; ?>"
            data-library="<?php echo esc_attr($field['library']); ?>"
            data-mime_types="<?php echo esc_attr($field['mime_types']); ?>"
            data-aspect_ratio="<?php echo esc_attr($field['aspect_ratio']); ?>"
        >
            <?php foreach ($data as $k => $d) { ?>
                <input
                    data-name="acf-image-focus-image-<?php echo esc_attr($k); ?>"
                    type="hidden"
                    name="<?php echo esc_attr($field['name']); ?>[<?php echo esc_attr($k); ?>]"
                    value="<?php echo esc_attr($d) ?>"
                />
            <?php } ?>

            <div class="show-if-value image-wrap">
                <img
                    src="<?php echo esc_url($url); ?>"
                    data-name="acf-image-focus-image"
                    width="<?php echo esc_attr($img[1] ?? 1); ?>"
                    height="<?php echo esc_attr($img[2] ?? 1); ?>"
                >
                <div class="acf-actions -hover">
                    <a class="acf-icon -cancel dark" data-name="remove" href="#"></a>
                </div>
            </div>

            <div class="hide-if-value">
                <p>
                    <?php _e('No image selected', 'acf'); ?>
                    <a
                        data-name="add"
                        class="acf-button button"
                        href="#"
                    >
                        <?php _e('Add Image', 'acf'); ?>
                    </a>
                </p>
            </div>

        </div>
    <?php }

    public function format_value($value, $post_id, $field)
    {
        if (empty($value['image_id'])) {
            return false;
        }

        $formatted_value = [
            'image_id'      => $value['image_id'],
            'field_name'    => $field['name'],
            'field_key'     => $field['key'],
            'image_size'    => $field['preview_size'],
            'position_x'    => round($value['position_x'], 2),
            'position_y'    => round($value['position_y'], 2),
            'aspect_ratio'  => $field['aspect_ratio'],
            'field_classes' => $field['acfif_field_classes'] ?? ''
        ];

        switch ($field['return_format']) {
            case 'image_element':
                return (new ImageOutput())->getOutputElement($formatted_value);
                break;

            default:
                return (new ArrayOutput())->getOutputElement($formatted_value);
                break;
        }

        return $value;
    }

    public function update_value($value, $post_id, $field)
    {
        if (empty($value['image_id'])) {
            return false;
        }

        return $value;
    }

    public function validate_value($valid, $value, $field, $input)
    {
        if ($field['required'] !== 0 && empty($value['image_id'])) {
            return false;
        }

        if (!empty($value['image_id']) && !is_numeric($value['image_id'])) {
            return false;
        }

        return $valid;
    }
}
