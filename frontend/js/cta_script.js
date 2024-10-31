jQuery(document).ready(function($) {
    var css = $(".cat-phone a")
        .attr("data-css")
        .split(",");
    if (css.length > 0) {
        $(".cat-phone a").css({
            fontFamily: css[0].toString(),
            fontWeight: css[1].toString()
        });
        var googleApi = 'https://fonts.googleapis.com/css?family=';
        $('link:last').after('<link href="' + googleApi + css[0].toString().replace(/ /g, '+') + '" rel="stylesheet" type="text/css">');
    }

    $('.cta-animation').css('border-color', $('.cat-phone').css('background-color'))
});