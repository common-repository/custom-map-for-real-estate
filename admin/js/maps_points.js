jQuery(document).ready(function($) {
    var meta_image_frame;

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
            $(".svl-image-wrap").addClass("has-image");
            $("#maps_images").val(media_attachment.url);
            if ($("#body_drag .images_wrap img").length > 0) {
                $("#body_drag .images_wrap img").attr("src", media_attachment.url);
            } else {
                $("#body_drag .images_wrap").html(
                    '<img src="' + media_attachment.url + '">'
                );
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

    $("body").on("click", ".delete-image", function() {
        var index = $(this)
            .closest("img")
            .val();
        var parentDiv = $(this).parents(".svl-upload-image");
        var imagesData = JSON.parse(parentDiv.find('input[type="hidden"]').val());
        imagesData.splice(imagesData.indexOf(index), 1);
        parentDiv
            .find('input[name="pointdata[pin_images][]"]')
            .val(JSON.stringify(imagesData));
        if (imagesData.length === 0) {
            parentDiv.removeClass("has-image");
        }
        $(this)
            .parents(".view-has-value")
            .remove();
        return false;
    });

    function doDraggable() {
        $(".drag_element").draggable({
            containment: "#body_drag",
            drag: function(event, ui) {},
            stop: function(event, ui) {
                var thisPoint = ui.helper.context.id;
                var dataPoint = $("#" + thisPoint).attr("data-points");
                var element = $("#body_drag");
                var left = ui.position.left,
                    top = ui.position.top;
                var wWrap = element.width(),
                    hWrap = element.height();
                var topPosition = ((top / hWrap) * 100).toFixed(2),
                    leftPosition = ((left / wWrap) * 100).toFixed(2);

                var targeted_popup_class = jQuery(this)
                    .find("a")
                    .attr("data-popup-open");
                $('[data-popup="' + targeted_popup_class + '"]')
                    .find('input[name="pointdata[top][]"]')
                    .val(topPosition);
                $('[data-popup="' + targeted_popup_class + '"]')
                    .find('input[name="pointdata[left][]"]')
                    .val(leftPosition);
            }
        });
    }
    doDraggable();
    $(".add_point").click(function() {
        var pins_image_view = $(".pins_image").val();
        var countPoint = parseInt(
            $(".wrap_svl .drag_element")
            .last()
            .attr("data-points")
        );
        var nonceForm = $("#map4re_meta_box_nonce").val();
        if (!countPoint) countPoint = 0;
        countPoint = countPoint + 1;
        var fullId = "point_content" + countPoint;
        $.ajax({
            type: "post",
            dataType: "json",
            url: meta_image.ajaxurl,
            data: {
                action: "map4re_clone_point",
                countpoint: countPoint,
                img_pins: pins_image_view,
                nonce: nonceForm
            },
            context: this,
            beforeSend: function() {
                $(this)
                    .parent()
                    .addClass("adding_point");
            },
            success: function(response) {
                if (response.success === true) {
                    var data = response.data;
                    $(".wrap_svl").append(data.point_pins);
                    $(".all_points").append(data.point_data);

                    quicktags({ id: fullId });
                    tinymce.init({
                        selector: "#" + fullId,
                        content_css: meta_image.editor_style,
                        min_height: 200,
                        textarea_name: "pointdata[content][]",
                        relative_urls: false,
                        remove_script_host: false,
                        convert_urls: false,
                        browser_spellcheck: false,
                        fix_list_elements: true,
                        entities: "38,amp,60,lt,62,gt",
                        entity_encoding: "raw",
                        keep_styles: false,
                        wpeditimage_disable_captions: false,
                        wpeditimage_html5_captions: true,
                        plugins: "charmap,hr,media,paste,tabfocus,textcolor,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview",
                        resize: "vertical",
                        menubar: false,
                        wpautop: true,
                        indent: false,
                        toolbar1: "bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,wp_adv",
                        toolbar2: "formatselect,underline,alignjustify,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help",
                        toolbar3: "",
                        toolbar4: "",
                        tabfocus_elements: ":prev,:next"
                    });

                    tinyMCE.execCommand("mceFocus", false, fullId);
                    tinyMCE.execCommand("mceRemoveEditor", false, fullId);
                    tinyMCE.execCommand("mceAddEditor", false, fullId);

                    doDraggable();

                    $(this)
                        .parent()
                        .removeClass("adding_point");
                } else {
                    alert("Try again!");
                }
            }
        });
        return false;
    });

    $("#btn_shortcode").click(e => {
        $("#copy_shortcode").select();
        document.execCommand("copy");
        e.preventDefault();
    });

    $("body").on("click", ".button_delete", function() {
        var idDiv = $(this)
            .parents(".list_points")
            .attr("data-points");
        $('[data-popup="info_draggable' + idDiv + '"]').fadeOut(350, function() {
            $("#info_draggable" + idDiv).remove();
            $("#draggable" + idDiv).remove();
        });
        return false;
    });

    $("body").on("click", ".button_save", function(e) {
        var elements = $(this)
            .parents(".map4re-popup-modal-content")
            .find(".map4re-popup-modal-body .map4re_row div");
        var title = elements.find('input[name="pointdata[title][]"]');
        var summary = elements.find('input[name="pointdata[summary][]"]');
        var contentType = elements.find('select[name="pointdata[contentType][]"]');
        var link = elements.find('input[name="pointdata[link][]"]');
        var images = elements.find('input[name="pointdata[pin_images][]"]');

        title.removeClass("invalid");
        summary.removeClass("invalid");
        link.removeClass("invalid");

        if (title.val() == "") {
            title.addClass("invalid");
        } else if (summary.val() == "") {
            summary.addClass("invalid");
        } else if (contentType.val() == "link") {
            var regex = new RegExp(
                /^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/).[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/
            );
            if (!regex.test(link.val())) {
                link.addClass("invalid");
                e.preventDefault();
            } else {
                var targeted_popup_class = jQuery(this).attr("data-popup-close");
                $('[data-popup="' + targeted_popup_class + '"]').fadeOut(350);
                e.preventDefault();
            }
        } else if (
            contentType.val() == "description" &&
            images.val().length === 0
        ) {
            alert("Please insert image");
        } else {
            var targeted_popup_class = jQuery(this).attr("data-popup-close");
            $('[data-popup="' + targeted_popup_class + '"]').fadeOut(350);
            e.preventDefault();
        }
    });

    $("body").on("click", ".button-close", function(e) {
        var targeted_popup_class = jQuery(this).attr("data-popup-close");
        $('[data-popup="' + targeted_popup_class + '"]').fadeOut(350);
        e.preventDefault();
    });

    $("body").on("click", "[data-popup-open]", function(e) {
        var targeted_popup_class = jQuery(this).attr("data-popup-open");
        $('[data-popup="' + targeted_popup_class + '"]').fadeIn(350);
        e.preventDefault();
    });
});