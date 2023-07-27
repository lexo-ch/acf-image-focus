# ACF Image Focus
ACF extension for displaying the images with different proportions in predefined "frame" by setting position, without cropping.

This extension adds a new "ACF Image Focus" field to [Advanced Custom Fields](https://www.advancedcustomfields.com/). It works similar to a default [ACF Image field](https://www.advancedcustomfields.com/resources/image/).

---
## Versioning
Release tags are created with Semantic versioning in mind. Commit messages were following convention of [Conventional Commits](https://www.conventionalcommits.org/).

---
## Compatibility
- WordPress version `>=4.7`. Tested and works fine up to `6.2.2`.
- PHP version `>=7.4.1`. Tested and works fine up to `8.1.9`.
- Minimum major ACF version `5`. Tested and works fine up to `6.1.7`.

---
## Installation
1. Download the [latest release](https://github.com/lexo-ch/acf-image-focus/releases/latest/) (the file named `Version [number]`). It's a compiled build.
2. Extract zip file and copy the folder into your `wp-content/plugins` folder and activate ACF Image Focus in plugins admin page. Alternatively, you can use downloaded zip file to install it directly from your plugin admin page.

---
## Usage
Use it as any other ACF field.

<img src="/screenshots/1.jpeg?raw=true" style="width: 100; max-width: 600px; height: auto;">
<blockquote>
    <small>Selecting return format: (1) Image Array and (2) Image Element.</small>
</blockquote>

---
<img src="/screenshots/2.jpeg?raw=true" style="width: 100; max-width: 600px; height: auto;">
<blockquote>
    <small>(1) Selecting Image Size of the image used in frontend </small><br>
    <small>(2) Selecting Image Aspect Ratio of the image used in frontend and in the "frame" as well. </small>
</blockquote>

---
<img src="/screenshots/3.jpeg?raw=true" style="width: 100; max-width: 600px; height: auto;">
<blockquote>
    <small>(1) "Frame" over the image in desired aspect ratio. In our example that "frame" has aspect ratio of 1.7 over the portrait image. "Frame" can be dragged to desired position.</small>
</blockquote>

---
<img src="/screenshots/4.jpeg?raw=true" style="width: 100; max-width: 600px; height: auto;">
<blockquote>
    <small>Final result in the frontend. Image uses seleted image size (Medium in our example) but only the the are selected by "frame" is shown, no additional crops of the image are being made.</small>
</blockquote>

---
## Return values
### Image array
Return value is array with folowing elements:

- (int) `image_id` - ID of the image.
- (string) `field_name` - ACF field name.
- (string) `field_key` - ACF field key.
- (string) `image_size` - Image size.
- (float) `position_x` - Value (percentage) of the X-axis (equivalent to `left`).
- (float) `position_y` - Value (percentage) of the Y-axis (equivalent to `top`).
- (float) `aspect_ratio` - Image aspect ratio in frontend.
- (string) `url` - Image URL.
- (int) `width` - Width in pixels.
- (int) `height` - Height in pixels.

*Example*

```
Array
(
    [image_id] => 13
    [field_name] => acf_image_focus_field
    [field_key] => field_630b58a621924
    [image_size] => medium
    [position_x] => 0
    [position_y] => 16.13
    [aspect_ratio] => 1.778
    [url] => http://acfimagefocus.test/wp-content/uploads/2023/07/test-image-768x599.webp
    [width] => 768
    [height] => 671
)
```

### Image element
Return value is `<img>` tag with already applied Image array.

*Example*
```
<img
    data-image-id="13"
    class="acf-image-focus"
    loading="lazy"
    decoding="async"
    width="768"
    height="671"
    src="http://acfimagefocus.test/wp-content/uploads/2023/07/test-image-768x599.webp" 
    alt="ACF Image Focus" 
    style="object-fit: cover; object-position: 0% 16.13%; aspect-ratio: 1.778; height: auto; max-width: 100%"
>
```

---
## Filters
#### - `acfif/admin/localized-script`
*Parameters*
`apply_filters('acfif/admin/localized-script', $args);`
- $args (array) The array which will be used for localizing `acfifAdminLocalized` variable in the admin.

#### - `acfif/enqueue/admin-acfif.js`
*Parameters*
`apply_filters('acfif/enqueue/admin-acfif.js', $args);`
- $args (bool) Printing of the file `admin-acfif.js` (stylesheet id is `acfif/admin-acfif.js-js`). It also affects printing of the localized `acfifAdminLocalized` variable.

#### - `acfif/enqueue/admin-acfif.css`
*Parameters*
`apply_filters('acfif/enqueue/admin-acfif.css', $args);`
- $args (bool) Printing of the file `admin-acfif.css` (stylesheet id is `acfif/admin-acfif.css-css`).

#### - `acfif/image/style-attribute`
*Parameters*
`apply_filters('acfif/image/style-attribute', $args);`
- $args (array) The array which will be used for `style` attribute of the ACF Image Focus image element.

#### - `acfif/image/style-attribute/name={$acf_field_name}`
*Parameters*
`apply_filters('acfif/image/style-attribute/name={$acf_field_name}', $args);`
- $args (array) The array which will be used for `style` attribute of the ACF Image Focus image element with provided name.

#### - `acfif/image/style-attribute/key={$acf_field_key}`
*Parameters*
`apply_filters('acfif/image/style-attribute/key={$acf_field_key}', $args);`
- $args (array) The array which will be used for `style` attribute of the ACF Image Focus image element with provided key.

#### - `acfif/image/classes`
*Parameters*
`apply_filters('acfif/image/classes', $args);`
- $args (array) The array which will be used for `class` attribute of the ACF Image Focus image element.

#### - `acfif/image/classes/name={$acf_field_name}`
*Parameters*
`apply_filters('acfif/image/classes/name={$acf_field_name}', $args);`
- $args (array) The array which will be used for `class` attribute of the ACF Image Focus image element with provided name.

#### - `acfif/image/classes/key={$acf_field_key}`
*Parameters*
`apply_filters('acfif/image/classes/key={$acf_field_key}', $args);`
- $args (array) The array which will be used for `class` attribute of the ACF Image Focus image element with provided key.

#### - `acfif/image/attributes`
*Parameters*
`apply_filters('acfif/image/attributes', $args);`
- $args (array) The array which will be used for creating all attributes when printing ACF Image Focus field as image element.

#### - `acfif/image/attributes/name={$acf_field_name}`
*Parameters*
`apply_filters('acfif/image/attributes/name={$acf_field_name}', $args);`
- $args (array) The array which will be used for creating all attributes when printing ACF Image Focus field as image element with provided name.

#### - `acfif/image/attributes/key={$acf_field_key}`
*Parameters*
`apply_filters('acfif/image/attributes/key={$acf_field_key}', $args);`
- $args (array) The array which will be used for creating all attributes when printing ACF Image Focus field as image element with provided key.

#### - `acfif/image/element`
*Parameters*
`apply_filters('acfif/image/element', $args);`
- $args (string) HTML output of the ACF Image Focus image element.

#### - `acfif/image/element/name={$acf_field_name}`
*Parameters*
`apply_filters('acfif/image/element/name={$acf_field_name}', $args);`
- $args (string) HTML output of the ACF Image Focus image element with provided name.

#### - `acfif/image/element/key={$acf_field_key}`
*Parameters*
`apply_filters('acfif/image/element/key={$acf_field_key}', $args);`
- $args (string) HTML output of the ACF Image Focus image element with provided key.

#### - `acfif/image/data`
*Parameters*
`apply_filters('acfif/image/data', $args);`
- $args (array) The array which is returned if return type is array.

#### - `acfif/image/data/name={$acf_field_name}`
*Parameters*
`apply_filters('acfif/image/data/name={$acf_field_name}', $args);`
- $args (array) The array which is returned if return type is array for element with provided name.

#### - `acfif/image/data/key={$acf_field_key}`
*Parameters*
`apply_filters('acfif/image/data/key={$acf_field_key}', $args);`
- $args (array) The array which is returned if return type is array for element with provided key.

---
## Actions
#### - `acfif/init`
- Fires on ACF Image Focus init.

#### - `acfif/localize/admin-acfif.js`
- Fires right before ACF Image Focus admin script has been enqueued.

---
#### Dependencies
This plugin heavily relies on [Cropper.js](https://fengyuanchen.github.io/cropperjs/).

---
## Changelog
Changelog can be seen on [latest release](https://github.com/lexo-ch/acf-image-focus/releases/latest/).
