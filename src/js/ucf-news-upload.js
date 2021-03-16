/* global wp */

(function ($) {

  $('.ucf_news_fallback_image_upload').click((e) => {
    e.preventDefault();

    const uploader = wp.media({
      title: 'News Fallback Image',
      button: {
        text: 'Select Image'
      },
      multiple: false
    })
      .on('select', () => {
        const attachment = uploader.state().get('selection').first().toJSON();
        $('.ucf_news_fallback_image_preview').attr('src', attachment.url);
        $('.ucf_news_fallback_image_upload').text('Change Image');
        $('.ucf_news_fallback_image').val(attachment.id);
        $('.ucf_news_fallback_image_preview').show();
        $('.ucf_news_clear_image_upload').show();
        $('.ucf_news_fallback_message').hide();
      })
      .open();
  });

  $('.ucf_news_clear_image_upload ').click((e) => {
    e.preventDefault();

    $('.ucf_news_fallback_image_preview').hide();
    $('.ucf_news_fallback_image_preview').attr('src', '');
    $('.ucf_news_fallback_image_upload').text('Add Image');
    $('.ucf_news_clear_image_upload').hide();
    $('.ucf_news_fallback_message').show();
    $('.ucf_news_fallback_image').val('');

  });

}(jQuery));
