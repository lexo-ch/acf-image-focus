/* eslint-disable no-undef */
/**
 * Admin .js file
 */

import 'cropperjs/dist/cropper.css';
import Cropper from 'cropperjs';

(function ($) {
  if (typeof acf === 'undefined' || typeof acf.Field === 'undefined') {
    return;
  }

  var Field = acf.Field.extend({

    type: acfifAdminLocalized.field_name,

    cropper: null,

    $control: function () {
      return this.$('.acf-image-uploader');
    },

    $inputId: function () {
      return this.$('[data-name="acf-image-focus-image-image_id"]');
    },

    $inputCanvasTop: function () {
      return this.$('[data-name="acf-image-focus-image-canvas_top"]');
    },

    $inputCanvasLeft: function () {
      return this.$('[data-name="acf-image-focus-image-canvas_left"]');
    },

    $inputPositionX: function () {
      return this.$('[data-name="acf-image-focus-image-position_x"]');
    },

    $inputPositionY: function () {
      return this.$('[data-name="acf-image-focus-image-position_y"]');
    },

    events: {
      'click a[data-name="add"]': 'onClickAdd',
      'click a[data-name="edit"]': 'onClickEdit',
      'click a[data-name="remove"]': 'onClickRemove',
      'change input[type="file"]': 'onChange',
    },

    initialize: function () {
      if (this.get('uploader') === 'basic') {
        this.$el.closest('form').attr('enctype', 'multipart/form-data');
      }

      acf.addAction(`append_field/type=${acfifAdminLocalized.field_name}`, function ($el) {
        $el.$el.find('.cropper-container').remove();
      });

      this.applyImgSelector();
    },

    validateAttachment: function (attachment) {

      // Use WP attachment attributes when available.
      if (attachment && attachment.attributes) {
        attachment = attachment.attributes;
      }

      // Apply defaults.
      attachment = acf.parseArgs(attachment, {
        id: 0,
        url: '',
        alt: '',
        title: '',
        caption: '',
        description: '',
        width: 0,
        height: 0,
      });

      // Override with "preview size".
      var size = acf.isget(attachment, 'sizes', this.get('preview_size'));

      if (size) {
        attachment.url = size.url;
        attachment.width = size.width;
        attachment.height = size.height;
      }

      // Return.
      return attachment;
    },

    fixPosLimits: function (pos) {
      pos = pos < 0 ? 0 : pos;
      return pos > 100 ? 100 : pos;
    },

    updateFieldsData: function () {
      let canvasTop = this.$inputCanvasTop();
      let canvasLeft = this.$inputCanvasLeft();
      let positionX = this.$inputPositionX();
      let positionY = this.$inputPositionY();
      let cropperData = this.cropper.getData(true);
      let imageData = this.cropper.getImageData();
      let posX = 0;
      let posY = 0;

      if (imageData.naturalHeight !== cropperData.height) {
        let simHeight = imageData.naturalHeight - cropperData.height;

        posY = 100 - (simHeight - cropperData.y) * 100 / simHeight;
      }

      if (imageData.naturalWidth !== cropperData.width) {
        let simWidth = imageData.naturalWidth - cropperData.width;
        posX = 100 - (simWidth - cropperData.x) * 100 / simWidth;
      }

      canvasTop.val(cropperData.y);
      canvasLeft.val(cropperData.x);
      positionX.val(this.fixPosLimits(posX));
      positionY.val(this.fixPosLimits(posY));


      this.$inputId().trigger('change');
    },

    getMinContainerSizes: function (width, height) {
      let newWidth, newHeight;

      width = parseInt(width);
      height = parseInt(height);

      const maxWidthHeight = parseInt(acfifAdminLocalized.max_width_height);

      switch (true) {
        case width > height:
          if (width < maxWidthHeight) {
            newWidth = width;
          }
          else {
            newWidth = maxWidthHeight;
          }

          newHeight = Math.floor(height * newWidth / width);

          if (newHeight > maxWidthHeight) {
            newHeight = maxWidthHeight;
            newWidth = Math.floor(width * newHeight / height);
          }
          break;

        case width < height:
          if (height < maxWidthHeight) {
            newHeight = height;
          }
          else {
            newHeight = maxWidthHeight;
          }

          newWidth = Math.floor(width * newHeight / height);

          if (newWidth > maxWidthHeight) {
            newWidth = maxWidthHeight;
            newHeight = Math.floor(height * newWidth / width);
          }
          break;

        default:
          newWidth = maxWidthHeight;
          newHeight = maxWidthHeight;
          break;
      }

      return {
        width: newWidth,
        height: newHeight,
      }
    },

    applyImgSelector: function (imageWidth = false, imageHeight = false) {
      let canvasTop = this.$inputCanvasTop();
      let canvasLeft = this.$inputCanvasLeft();
      let image = this.$('img')[0];
      let ratio = this.get('aspect_ratio');

      if (imageWidth === false) {
        imageWidth = this.$('img').attr('width');
      }

      if (imageHeight === false) {
        imageHeight = this.$('img').attr('height');
      }

      let minContainer = this.getMinContainerSizes(imageWidth, imageHeight);

      // https://github.com/fengyuanchen/cropperjs

      if (this.cropper) {
        this.cropper.destroy();
      }

      image.addEventListener('ready', () => {
        if (canvasTop.val() && canvasLeft.val()) {
          this.cropper.setData({
            x: parseInt(canvasLeft.val()),
            y: parseInt(canvasTop.val()),
          });
        }
        else {
          this.updateFieldsData();
        }
      });

      image.addEventListener('cropend', () => {
        this.updateFieldsData();
      });

      this.cropper = new Cropper(image, {
        aspectRatio: ratio,
        viewMode: 1,
        guides: false,
        center: false,
        autoCropArea: 1,
        zoomable: false,
        rotatable: false,
        scalable: false,
        cropBoxResizable: false,
        toggleDragModeOnDblclick: false,
        dragMode: 'none',
        minContainerWidth: minContainer.width,
        minContainerHeight: minContainer.height,
      });
    },

    clearFieldData: function () {
      this.$inputCanvasTop().val('');
      this.$inputCanvasLeft().val('');
      this.$inputPositionX().val('');
      this.$inputPositionY().val('');
    },

    render: function (attachment) {
      attachment = this.validateAttachment(attachment);

      // Update DOM.
      this.$('img').attr({
        src: attachment.url,
        alt: attachment.alt,
      });

      if (attachment.id) {
        this.$inputId().val(attachment.id);
        this.$control().addClass('has-value');
        this.applyImgSelector(attachment.width, attachment.height);
      }
      else {
        this.val('');
        this.$control().removeClass('has-value');
      }
    },

    // create a new repeater row and render value
    append: function (attachment, parent) {

      // create function to find next available field within parent
      var getNext = function (field, parent) {

        // find existing file fields within parent
        var fields = acf.getFields({
          key: field.get('key'),
          parent: parent.$el,
        });

        // find the first field with no value
        for (var i = 0; i < fields.length; i++) {
          if (!fields[i].val()) {
            return fields[i];
          }
        }

        // return
        return false;
      }

      // find existing file fields within parent
      var field = getNext(this, parent);

      // add new row if no available field
      if (!field) {
        parent.$('.acf-button:last').trigger('click');
        field = getNext(this, parent);
      }

      // render
      if (field) {
        field.render(attachment);
      }
    },

    selectAttachment: function () {

      // vars
      var parent = this.parent();
      var multiple = (parent && parent.get('type') === 'repeater');

      acf.newMediaPopup({
        mode: 'select',
        type: 'image',
        title: acf.__('Select Image'),
        field: this.get('key'),
        multiple: multiple,
        library: this.get('library'),
        allowedTypes: this.get('mime_types'),
        select: $.proxy(function (attachment, i) {
          if (i > 0) {
            this.append(attachment, parent);
          }
          else {
            this.render(attachment);
          }
        }, this),
      });
    },

    editAttachment: function () {

      // vars
      var val = this.val();

      // bail early if no val
      if (!val) return;

      acf.newMediaPopup({
        mode: 'edit',
        title: acf.__('Edit Image'),
        button: acf.__('Update Image'),
        attachment: val,
        field: this.get('key'),
        // eslint-disable-next-line no-unused-vars
        select: $.proxy(function (attachment, i) {
          this.render(attachment);
        }, this),
      });
    },

    removeAttachment: function () {
      this.render(false);
    },

    // eslint-disable-next-line no-unused-vars
    onClickAdd: function (e, $el) {
      this.selectAttachment();
    },

    // eslint-disable-next-line no-unused-vars
    onClickEdit: function (e, $el) {
      this.editAttachment();
    },

    // eslint-disable-next-line no-unused-vars
    onClickRemove: function (e, $el) {
      this.removeAttachment();
      this.clearFieldData();
    },

    // eslint-disable-next-line no-unused-vars
    onChange: function (e, $el) {
      var $hiddenInput = this.$inputId();

      acf.getFileInputData($el, function (data) {
        $hiddenInput.val($.param(data));
      });
    },
  });

  acf.registerFieldType(Field);

})(jQuery);
