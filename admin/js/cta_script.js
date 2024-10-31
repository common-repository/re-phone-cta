jQuery(document).ready(function($) {
    var meta_image_frame;
    $("#color_picker, #text_color_picker").wpColorPicker();

    function applyFont(font) {
        // Replace + signs with spaces for css
        font = font.replace(/\+/g, " ");

        // Split font into family and weight
        font = font.split(":");

        var fontFamily = font[0];
        var fontWeight = font[1] || 400;

        // Set selected font on paragraphs
        var css = fontFamily + "," + fontWeight;

        $('input[name="font"]').val(css);
    }

    $("#select_font")
        .fontselect({
            systemFonts: true,
            // placeholderSearch: "Type to search...",
            lookahead: 4
        })
        .on("change", function() {
            applyFont(this.value);
        });

    $("body").on("click", "[id*=meta-image-button]", function(e) {
        e.preventDefault();

        meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
            title: meta_image.title,
            button: { text: meta_image.button },
            library: { type: "image" },
            multiple: false
        });

        meta_image_frame.on("select", function() {
            var media_attachment = meta_image_frame
                .state()
                .get("selection")
                .first()
                .toJSON();
            if (media_attachment.width > 200 || media_attachment.height > 200) {
                alert("Please insert image with lower size");
            } else {
                $("#maps_images").val(media_attachment.url);
                $("#remove-image").css("display", "block");
                if ($("#image_upload img").length > 0) {
                    $("#image_upload img").attr("src", media_attachment.url);
                } else {
                    $("#image_upload input").before(
                        '<img alt="image" src="' + media_attachment.url + '">'
                    );
                }
            }
        });
        meta_image_frame.open();
    });
    $("body").on("click", ".button-upload", function(e) {
        e.preventDefault();
        var thisUpload = $(this).parents(".svl-upload-image");
        var imagesData = JSON.parse(
            thisUpload.find('input[type="hidden"]').val() || "[]"
        );

        meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
            title: meta_image.title,
            button: { text: meta_image.button },
            library: { type: "image" },
            multiple: true
        });

        meta_image_frame.on("select", function() {
            var media_images = meta_image_frame
                .state()
                .get("selection")
                .map(attachment => {
                    return attachment.toJSON();
                });

            thisUpload.addClass("has-image");
            var arrImage = media_images.map(image => {
                return image.url;
            });
            imagesInput = JSON.stringify([...new Set(imagesData.concat(arrImage))]);

            thisUpload.find('input[type="hidden"]').val(imagesInput);

            thisUpload.find(".view-has-value").remove();
            JSON.parse(imagesInput).forEach(image => {
                var html =
                    '<div class="view-has-value">' +
                    '<img src="' +
                    image +
                    '" class="image_view pins_img" />' +
                    '<a href="#" data-index="' +
                    JSON.parse(imagesInput).indexOf(image) +
                    '" class="delete-image">x</a>' +
                    "</div>";
                thisUpload.find(".hidden-has-value").before(html);
            });
        });
        meta_image_frame.open();
    });

    $("body").on("click", "#remove-image", function() {
        $("#maps_images").val("");
        $("#image_upload img").remove();
        $("#remove-image").css("display", "none");
        return false;
    });

    $("#btn_shortcode").click(e => {
        $("#copy_shortcode").select();
        document.execCommand("copy");
        e.preventDefault();
    });
});