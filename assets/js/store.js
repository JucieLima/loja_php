$(function () {

    base_url = $('meta[name="base-url"]').attr("content");

    $('.date').mask('00/00/0000');
    $('.time').mask('00:00:00');
    $('.date_time').mask('00/00/0000 00:00:00');
    $('.cep').mask('00000-000');
    $('.phone').mask('0000-0000');
    $('.phone_with_ddd').mask('(00) 00000-0000');
    $('.cpf').mask('000.000.000-00', {reverse: true});
    $('.cnpj').mask('00.000.000/0000-00', {reverse: true});
    $('.money').mask('000.000.000.000.000,00', {reverse: true});
    $('.percent').mask('00,00', {reverse: true});

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

    /** ALTERAR QUANTIDADE DE PRODUTOS **/
    $('form[name="add-product"] .btn-quantity').click(function (e) {
        e.preventDefault();
        var quantity = parseInt($('input[name="quantity"]').val());
        var action = $(this).attr("data-quantity");
        if (action === 'decrease') {
            if (quantity - 1 > 0) {
                $('input[name="quantity"]').val(quantity - 1);
            }
        } else if (action === 'increase') {
            $('input[name="quantity"]').val(quantity + 1);
        }
    });

    /** CARROSSEL **/
    $("#category-item-carousel").carousel('pause');

    /** AVALIAÇÂO **/
    $('.review-js i').click(function () {
        var rating = $(this).attr("id").substr(4, 1);
        $('.review-js i').removeClass("selected");
        for (i = 1; i <= rating; i++) {
            $('.review-js #star' + i).addClass('selected');
        }
        $('input[name="rating_value"]').val(rating);
    });

    /** CARRINHO DED COMPRAS **/
    //Adicionar produto
    $('.add-to-cart').on('click', function (e) {
        e.preventDefault();
        var idproduct = $(this).attr('data-product');
        var quantity = 1;
        var total_products = parseInt($('.cart_total_products').attr("data-products"));

        $('.cart_total_products span').text(quantity + total_products);
        $('.cart_total_products').attr("data-products", quantity + total_products);
        $('#modal_add_product').modal("show");

        $.post(base_url + "cart/add", {product: idproduct, quant: quantity});
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

    $('.btn-shipping').on('click', function () {
        var cep = $('input[name="cep"]').val();

        if (cep === '') {
            $('.alert-cep').html("<button type='button' class='close'>" +
                    "<span aria-hidden='true'>&times;</span></button>" +
                    "Informe um CEP para realizar o cálculo do frete!");
            $('.alert-cep').fadeIn("slow");
        } else {
            $.post(base_url + "cart/shipping", {cep: cep}, function (r) {
                if (r.error === false) {
                    $('.shipping-data .shipping-value').text(r.valor);
                    $('.shipping-data .shipping-date').text(r.prazo);
                    $('.shipping-data').fadeIn("slow");
                } else {
                    $('.shipping-data').hide();
                    $('.alert-cep').html("<button type='button' class='close'>" +
                            "<span aria-hidden='true'>&times;</span></button>" + r.error);
                    $('.alert-cep').fadeIn("slow");
                }
            }, 'json');
        }
    });

    $('.alert-cep').on('click', '.close', function () {
        $('.alert-cep').fadeOut("slow");
    });

});
