(function($){

  /**
   * scroll to top
   */
  var scrltp = $('#wstd-scroll-top');
  scrltp.click(function(e) {
    e.preventDefault();
    $('html, body').animate({scrollTop: 0}, 400);
  });
  $(window).on('scroll resize', function() {
    var len = $(window).scrollTop();
    var fdrh = $('#wstd-footer').innerHeight();
    if (250 < len) {
      scrltp.fadeIn('200');
    } else {
      scrltp.fadeOut('800');
    }
  });

  /**
   * linked box element
   */
  $('div.linked-box').click(function(e) {
    e.preventDefault();
    var anchor = $(this).find('a');
    if (0 < anchor.length)
      window.location = anchor.attr('href');
  });

  /**
   * box elements auto adjust height
   */
  $.fn.autoHeight = function() {
    this.css('height', '');
    var h = 0;
    this.each(function() {
      var _h = $(this).height();
      if ( _h > h )
        h = _h;
    });
    return this.each(function() {
      $(this).height(h);
    });
  }

  /**
   * home
   */
  $('body.home #wstdCompany').click(function(e) {
    e.preventDefault();
    $('html, body').animate({scrollTop: $('#company').offset().top}, 400);
  });

  $(window, document).on('ready resize', function() {
    $('body.home div.wstd-header-image').mkpadding();
  });

  $.fn.mkpadding = function() {
    this.find('#mkpaddingMask').children().unwrap();
    this.find('#mkpaddingInner').children().unwrap();
    var wid = $(window).width();
    if (1350 < wid) {
      var img = this.css('backgroundImage'),
          hei = this.height();
          pos = this.css('backgroundPosition');
      this.wrapInner('<div id="mkpaddingInner" />');
      $('#mkpaddingInner').addClass('container').css({
        backgroundImage: img,
        backgroundSize: 'cover',
        backgroundPosition: pos,
        height: hei,
        zIndex: 2
      });
      $('#mkpaddingInner').wrap('<div id="mkpaddingMask" />');
      $('#mkpaddingMask').css({
        backgroundColor: 'rgba(0,0,0,.95)',
        width: '100%',
        height: hei,
        position: 'absolute',
        top: 0,
        left: 0,
        zIndex: 0
      });
    }
  }

})(jQuery);