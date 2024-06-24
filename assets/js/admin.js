(function ($) {
  'use strict';

  if ($.fn.wpColorPicker) {
    $('input.rtwpvg-color-picker').wpColorPicker();
  }
  $('#rtwpvg-settings-wrapper').on('click', '.nav-tab', function (event) {
    event.preventDefault();
    var self = $(this),
      target = self.data('target');
    self.addClass('nav-tab-active').siblings().removeClass('nav-tab-active');
    $('#' + target).show().siblings().hide();
    $('#_last_active_tab').val(target);
    if (history.pushState) {
      var newurl = setGetParameter('section', target);
      window.history.pushState({
        path: newurl
      }, '', newurl);
    }
  });
  /* Licence */
  $(".rtwpvg-setting-tab #license_key-wrapper").on('keyup', '#license_key-field', function (e) {
    e.preventDefault();
    $('.license-status').html('When add license key first click on Save changes');
  });
  $(".rtwpvg-setting-tab #license_key-wrapper").on('click', '.rt-licensing-btn', function (e) {
    e.preventDefault();
    console.log('clicked');
    var self = $(this),
      type = self.hasClass('license_activate') ? 'license_activate' : 'license_deactivate';
    $.ajax({
      type: "POST",
      url: rtwpvg_admin.ajaxurl,
      data: {
        action: 'rtwpvg_manage_licensing',
        type: type
      },
      beforeSend: function beforeSend() {
        self.addClass('loading');
        self.parents('.description').find(".rt-licence-msg").remove();
        $('<span class="rt-icon-spinner animate-spin"></span>').insertAfter(self);
      },
      success: function success(response) {
        self.next('.rt-icon-spinner').remove();
        self.removeClass('loading');
        if (!response.error) {
          self.text(response.value);
          self.removeClass(type);
          self.addClass(response.type);
          if (response.type == 'license_deactivate') {
            self.removeClass('button-primary');
            self.addClass('danger');
          } else if (response.type == 'license_activate') {
            self.removeClass('danger');
            self.addClass('button-primary');
          }
        }
        if (response.msg) {
          $("<span class='rt-licence-msg'>" + response.msg + "</span>").insertAfter(self);
        }
        self.blur();
      },
      error: function error(jqXHR, exception) {
        self.removeClass('loading');
        self.next('.rt-icon-spinner').remove();
      }
    });
  });
  function setGetParameter(paramName, paramValue) {
    var url = window.location.href;
    var hash = location.hash;
    url = url.replace(hash, '');
    if (url.indexOf("?") >= 0) {
      var params = url.substring(url.indexOf("?") + 1).split("&");
      var paramFound = false;
      params.forEach(function (param, index) {
        var p = param.split("=");
        if (p[0] == paramName) {
          params[index] = paramName + "=" + paramValue;
          paramFound = true;
        }
      });
      if (!paramFound) params.push(paramName + "=" + paramValue);
      url = url.substring(0, url.indexOf("?") + 1) + params.join("&");
    } else url += "?" + paramName + "=" + paramValue;
    return url + hash;
  }
  function imageUploader() {
    $(document).off('click', '.rtwpvg-add-image');
    $(document).on('click', '.rtwpvg-add-image', addImage);
    $(document).on('click', '.rtwpvg-remove-image', removeImage);
    $(document).on('click', '.rtwpvg-media-video-popup', addMediaVideo);
    $('.woocommerce_variation').each(function () {
      var optionsWrapper = $(this).find('.options');
      var galleryWrapper = $(this).find('.rtwpvg-gallery-wrapper');
      galleryWrapper.insertBefore(optionsWrapper);
    });
  }
  function addImage(event) {
    event.preventDefault();
    event.stopPropagation();
    var that = this;
    var file_frame = 0;
    var product_variation_id = $(this).data('product_variation_id');
    var loop = $(this).data('product_variation_loop');
    // console.log( $(this) );
    var _prev_image = $(this).parents('.rtwpvg-gallery-wrapper').find('input').map(function () {
      return Number($(this).val());
    }).get();
    console.log(_prev_image);
    if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
      if (file_frame) {
        file_frame.open();
        return;
      }
      file_frame = wp.media.frames.select_image = wp.media({
        title: rtwpvg_admin.choose_image,
        button: {
          text: rtwpvg_admin.add_image
        },
        library: {
          type: ['image']
        },
        multiple: true
      });
      file_frame.on('select', function () {
        var images = file_frame.state().get('selection').toJSON();
        var html = images.map(function (image) {
          if (image.type === 'image') {
            console.log(image);
            if (_prev_image.indexOf(image.id) === -1) {
              var id = image.id,
                rtwpvg_video_link = image.rtwpvg_video_link,
                image_sizes = image.sizes;
              image_sizes = image_sizes === undefined ? {} : image_sizes;
              var thumbnail = image_sizes.thumbnail,
                full = image_sizes.full;
              var url = thumbnail ? thumbnail.url : full.url;
              var template = wp.template('rtwpvg-image');
              return template({
                id: id,
                url: url,
                product_variation_id: product_variation_id,
                loop: loop,
                rtwpvg_video_link: rtwpvg_video_link
              });
            } else {
              alert('Cannot add duplicate items.');
            }
          }
        }).join('');
        $(that).parent().prev().find('.rtwpvg-images').append(html);
        sortable();
        variationChanged(that);
      });
      file_frame.open();
    }
  }
  function addMediaVideo(e) {
    e.preventDefault();
    e.stopPropagation();
    var that = this;
    var video_frame = 0;
    if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
      // If the media frame already exists, reopen it.
      if (video_frame) {
        video_frame.open();
        return;
      }

      // Create the media frame.
      video_frame = wp.media.frames.select_image = wp.media({
        title: rtwpvg_admin.choose_video,
        button: {
          text: rtwpvg_admin.add_video
        },
        library: {
          type: ['video']
        },
        multiple: false
      });

      // When a file is selected, run a callback.
      video_frame.on('select', function () {
        var video = video_frame.state().get('selection').first().toJSON();
        if (video.type === 'video') {
          $(that).closest('.compat-attachment-fields').find('.compat-field-rtwpvg_video_link input').val(video.url).change();
        }
      });

      // Finally, open the modal.
      video_frame.open();
    }
  }
  function removeImage(event) {
    event.preventDefault();
    event.stopPropagation();
    var that = this;
    variationChanged(this);
    setTimeout(function () {
      $(that).parent().remove();
    }, 1);
  }
  function variationChanged(element) {
    $(element).closest('.woocommerce_variation').addClass('variation-needs-update');
    $('button.cancel-variation-changes, button.save-variation-changes').removeAttr('disabled');
    $('#variable_product_options').trigger('woocommerce_variations_input_changed');
  }
  function sortable() {
    $('.rtwpvg-images').sortable({
      items: 'li.image',
      cursor: 'move',
      scrollSensitivity: 40,
      forcePlaceholderSize: true,
      forceHelperSize: false,
      helper: 'clone',
      opacity: 0.65,
      placeholder: 'rtwpvg-sortable-placeholder',
      start: function start(event, ui) {
        ui.item.css('background-color', '#f6f6f6');
      },
      stop: function stop(event, ui) {
        ui.item.removeAttr('style');
      },
      update: function update() {
        variationChanged(this);
      }
    });
  }

  //Thumbnail Style
  function settingsThumbnailPosition() {
    var thumbnail_position = $('#thumbnail_position-field').val();
    // console.log( thumbnail_position );
    if ('grid' == thumbnail_position) {
      $('#thumbnail_slide-wrapper').hide();
      $('#slider_arrow-wrapper').hide();
      $('#slider_adaptive_height-wrapper').hide();
    } else {
      $('#thumbnail_slide-wrapper').show();
      $('#slider_arrow-wrapper').show();
      $('#slider_adaptive_height-wrapper').show();
    }
  }
  $('#woocommerce-product-data').on('woocommerce_variations_loaded', function () {
    imageUploader();
    sortable();
  });
  $('#variable_product_options').on('woocommerce_variations_added', function () {
    imageUploader();
    sortable();
  });
  $('#woocommerce-product-images .add_product_images').on('click', 'a', function (event) {
    $(document).on('click', '.rtwpvg-media-video-popup', addMediaVideo);
  });
  //techlabpro23
  $(function () {
    $("#rtwpvg-settings-wrapper").on('click', '.pro-field', function (e) {
      e.preventDefault();
      $('.rtvg-pro-alert').show();
    });

    //pro alert close
    $('.rtvg-pro-alert-close').on('click', function (e) {
      e.preventDefault();
      $('.rtvg-pro-alert').hide();
    });

    //preloader option 
    function preloader_option() {
      var preloader = $("#preloader-field").is(':checked');
      if (preloader) {
        $("#preloader_image-wrapper").show();
        $("#preload_style-wrapper").show();
      } else {
        $("#preloader_image-wrapper").hide();
        $("#preload_style-wrapper").hide();
      }
    }
    preloader_option();
    $(document).on('change', '#preloader-field', function () {
      preloader_option();
    });

    //image upload field 
    $(document).on('click', '.rtwpvg-upload-box', function (e) {
      e.preventDefault();
      var name = $(this).attr('data-name');
      var field_type = $(this).attr('data-field');
      var self = $(this),
        file_frame,
        json; // If an instance of file_frame already exists, then we can open it rather than creating a new instance

      if (undefined !== file_frame) {
        file_frame.open();
        return;
      } // Here, use the wp.media library to define the settings of the media uploader

      file_frame = wp.media.frames.file_frame = wp.media({
        frame: 'post',
        state: 'insert',
        multiple: field_type == 'image' ? false : true
      }); // Setup an event handler for what to do when an image has been selected

      file_frame.on('insert', function () {
        // Read the JSON data returned from the media uploader
        json = file_frame.state().get('selection').first().toJSON(); // First, make sure that we have the URL of an image to display

        if (0 > $.trim(json.url.length)) {
          return;
        }
        var images = file_frame.state().get('selection').toJSON();
        var img_data = '';
        var multiple = field_type == 'image' ? '' : '[]';
        images.forEach(function (element) {
          img_data += "<div class='rtwpvg-preview-img'><img src='" + element.url + "' /><input type='hidden' name='" + name + multiple + "' value='" + element.id + "'><button class='rtwpvg-file-remove' data-id='" + element.id + "'>x</button></div>";
        });
        if (field_type == 'image') {
          self.prev().html(img_data);
        } else {
          self.prev().html(img_data);
        }
      }); // Now display the actual file_frame

      file_frame.open();
    });

    //delete image  
    $(document).on('click', '.rtwpvg-file-remove', function (e) {
      e.preventDefault();
      if (confirm(rtwpvg_admin.sure_txt)) {
        if ($(this).parent().parent().children('.rtwpvg-preview-img').length <= 1) {
          $(this).parent().children('img').remove();
          $(this).parent().children('input').val(0);
          $(this).remove();
        } else {
          $(this).parent().remove();
        }
      }
    });

    //Thumbnail Style
    settingsThumbnailPosition();
    $('#thumbnail_position-field').on('change', function (e) {
      e.preventDefault();
      settingsThumbnailPosition();
    });
  });

  //end tachlabpro23
})(jQuery);
