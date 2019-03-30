$(function () {
    base_url = $('meta[name="base-url"]').attr("content");

    /** SINGLE PRODUTO **/
    var windowWidth = window.innerWidth;

    if (windowWidth > 767) {
        $('.prev.item-control-zoom .fa').removeClass('fa-angle-left');
        $('.prev.item-control-zoom .fa').addClass('fa-angle-up');
        $('.next.item-control-zoom .fa').removeClass('fa-angle-right');
        $('.next.item-control-zoom .fa').addClass('fa-angle-down');
    }
    $(window).resize(function () {
        var windowWidth = window.innerWidth;
        if (windowWidth > 767) {
            $('.prev.item-control-zoom .fa').removeClass('fa-angle-left');
            $('.prev.item-control-zoom .fa').addClass('fa-angle-up');
            $('.next.item-control-zoom .fa').removeClass('fa-angle-right');
            $('.next.item-control-zoom .fa').addClass('fa-angle-down');
        } else {
            $('.prev.item-control-zoom .fa').addClass('fa-angle-left');
            $('.prev.item-control-zoom .fa').removeClass('fa-angle-up');
            $('.next.item-control-zoom .fa').addClass('fa-angle-right');
            $('.next.item-control-zoom .fa').removeClass('fa-angle-down');
        }
    });

    $('.item-control-zoom').click(function (e) {
        e.preventDefault();
        var direction = $(this).attr("data-gallery");
        var windowWidth = window.innerWidth;
        if (windowWidth > 767) {
            verticalMoveGallery(direction);
        } else {
            hotizontalMoveGallery(direction);
        }
    });

    clicks = 0;

    function verticalMoveGallery(direction) {
        var imgheight = $('.zoom-gallery img').outerHeight() + 6;
        var itens = $('.zoom-gallery .items img').length - 3;

        if (direction === 'next') {
            if (clicks <= itens) {
                clicks++;
                $('.zoom-gallery .items').animate({
                    marginTop: "-=" + imgheight
                }, "fast");
                $('.prev.item-control-zoom').removeClass('desabled');
            } else {
                $('.next.item-control-zoom').addClass('desabled');

            }

        } else if (direction === 'prev') {
            if (clicks >= 1) {
                clicks--;
                $('.zoom-gallery .items').animate({
                    marginTop: "+=" + imgheight
                }, "fast");
                $('.next.item-control-zoom').removeClass('desabled');
            } else {
                $('.prev.item-control-zoom').addClass('desabled');
            }
        }
    }

    function hotizontalMoveGallery(direction) {
        var imgwidth = $('.zoom-gallery img').outerHeight() + 6;
        var itens = $('.zoom-gallery .items img').length - 3;
        if (direction === 'next') {
            if (clicks <= itens) {
                clicks++;
                $('.zoom-gallery .items').animate({
                    marginLeft: "-=" + imgwidth
                }, "fast");
                $('.prev.item-control-zoom').removeClass('desabled');
            } else {
                $('.next.item-control-zoom').addClass('desabled');

            }

        } else if (direction === 'prev') {
            if (clicks >= 1) {
                clicks--;
                $('.zoom-gallery .items').animate({
                    marginLeft: "+=" + imgwidth
                }, "fast");
                $('.next.item-control-zoom').removeClass('desabled');
            } else {
                $('.prev.item-control-zoom').addClass('desabled');
            }
        }
    }

    $('.zoom-gallery img').click(function (e) {
        $('.zoom-gallery img').removeClass("active");
        $(this).addClass("active");
        e.preventDefault();
        var img = $(this).attr("data-image");
        $('.view-product img').fadeTo('fast', 0.5, function () {
            $(this).attr("src", img);
            $("#zoom_product").data('zoom-image', img).elevateZoom({
                zoomType: "inner",
                cursor: "crosshair",
                zoomWindowFadeIn: 500,
                zoomWindowFadeOut: 750
            });
        }).fadeTo("fast", 1);
    });

    $('#zoom_product').elevateZoom({
        zoomType: "inner",
        cursor: "crosshair",
        zoomWindowFadeIn: 500,
        zoomWindowFadeOut: 750
    });

    //Adicionar produto single
    $('form[name="add-product"]').submit(function (e) {
        e.preventDefault();
        var idproduct = parseInt($('input[name="product_id"]').val());
        var quantity = parseInt($('input[name="quantity"]').val());
        var total_products = parseInt($('.cart_total_products').attr("data-products"));

        $('.cart_total_products span').text(quantity + total_products);
        $('.cart_total_products').attr("data-products", quantity + total_products);
        $('#modal_add_product').modal("show");

        $.post(base_url + "cart/add", {product: idproduct, quant: quantity});
    });

});