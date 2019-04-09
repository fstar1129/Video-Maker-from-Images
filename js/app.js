$(document).ready(function () {
  $("#upload-images").fileinput();

  function select_image(el) {
    el.classList.add('selected');
    $(el).find('.wrapper > div').text($('.selected').length);
  }

  function deselect_image(el) {
    el.classList.remove('selected');
    let index = parseInt($(el).find('.wrapper > div')[0].innerText);
    $('.selected > .wrapper > div').each(function () {
      let i = parseInt(this.innerText);
      if (i > index) {
        $(this).text(i - 1);
      }
    });
  }

  $('.image-panel > img').click(function () {
    el = this.parentNode;
    if (el.classList.contains('selected')) {
      deselect_image(el);
    } else {
      select_image(el);
    }
  });

  var el_img;

  $('.edit-image').click(function (e) {
    el_img = this.parentNode;
    $('#animation')[0].value = $(el_img).find('.image-animation > span').attr('value');
    $('#overlay-text').val($(el_img).find('.image-overlay-text > span').text());
  });

  $('#animation-submit').click(function(e) {
    let el_select = $('#animation')[0];
    $(el_img).find('.image-animation > span').text(el_select.options[el_select.selectedIndex].innerText);
    $(el_img).find('.image-animation > span').attr('value', el_select.value);
    $(el_img).find('.image-overlay-text > span').text($('#overlay-text').val());
  });

  $('#btn-generate-video').click(function() {

    $(this).button('loading');

    let images = [];
    $('.image-panel.selected').each(function()
    {
      images.push(1);
    });

    $('.image-panel.selected').each(function()
    {
      images[parseInt($(this).find('.wrapper > div').text())-1] = {
        'src': $(this).find('img').attr('val'),
        'animation': $(this).find('.image-animation > span').attr('value'),
        'overlay_text': $(this).find('.image-overlay-text > span').text()
      };
    });

    let data = {
      'images': images,
      'top_bar_text': $('#top-bar-text').val(),
      'bottom_bar_text': $('#bottom-bar-text').val(),
      'end_screen_text': $('#end-screen-text').val(),
      'your_brand_name': $('#your-brand-name').val(),
      'select_image_fit': $('#select-image-fit').val(),
      'select_sound': $('#select-sound').val(),
      'select_per_frame': parseFloat($('#select-per-frame').val())
    }
    // $.ajax({
    //   type: 'POST',
    //   url: 'make-video.php',
    //   data: JSON.stringify({
    //     'param': data
    //   }),
    //   contentType: 'application/json; charset=utf-8',
    //   dataType: 'json',
    //   success: function(response)
    //   {
    //     console.log(response)
    //   }
    // })

    $.post('make-video.php', {'param': JSON.stringify(data)}, function(result){
      console.log(result);
      $('#result-video')[0].pause();
      $('#result-video > source').attr('src', 'video.php?param=' + new Date().getTime());
      $('#result-video')[0].load();
      $('#result-video')[0].play();
      $('#btn-generate-video').button('reset');
    });
  });
});