(function($) {
  $(document).ready(function() {
    
    $('.ufqc-copy-clipboard').on('click', function(e) {
      e.preventDefault();
      var copyText = $(this).attr('href');
      document.addEventListener('copy', function(e) {
        e.clipboardData.setData('text/plain', copyText);
        e.preventDefault();
      }, true);
      document.execCommand('copy');

      const oldtxt = $(this).html();
      $(this).html('Copied!')
      setTimeout(() => {
        $(this).html(oldtxt)
      }, 3000);
    })

  })
})( jQuery );