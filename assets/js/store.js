$(function () {
    
//    if (location.protocol != 'https:'){
//        location.href = 'https:' + window.location.href.substring(window.location.protocol.length);
//    }

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
    

    // Calcular frete na página produtos
    $('.btn-shipping').on('click', function () {
        var cep = $('input[name="cep"]').val();
        
        if (cep === '') {
            $('.alert-cep').html("<button type='button' class='close'>" +
                    "<span aria-hidden='true'>&times;</span></button>" +
                    "Informe um CEP para realizar o cálculo do frete!");
            $('.alert-cep').fadeIn("slow");
        } else {
            $.ajax({
                url: base_url + "cart/shipping",
                data: {cep: cep},
                type: 'POST',
                dataType: 'json',
                beforeSend: function () {
                    $('.btn-shipping .fa-truck').fadeOut(1000);
                    $('.btn-shipping .fa-fw').fadeIn(1000);
                },
                success: function (r) {
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
                },
                complete: function () {
                    $('.btn-shipping .fa-truck').fadeIn('fast');
                    $('.btn-shipping .fa-fw').fadeOut('fast');
                },
                error: function () {}
            });
        }
    });

    $('.alert-cep').on('click', '.close', function () {
        $('.alert-cep').fadeOut("slow");
    });

    $('.alert-discount').on('click', '.close', function (e) {
        e.preventDefault();
        $('.alert-discount .alert').fadeOut("slow");
    });

    /** CARRINHO DE COMPRAS **/
    //Aplicar desconto
    $('.add_discount').click(function (e) {
        e.preventDefault();
        var code = $('#coupon_code').val();
        $.ajax({
            url: base_url + "cart/add_coupon",
            type: 'POST',
            data: {code: code},
            dataType: 'json',
            beforeSend: function () {
                $('.add_discount .fa-fw').fadeIn("fast");
            },
            success: function (r) {
                if (r.error === false) {
                    discountCouponUpdate(r.coupon_discount);
                    $('.cupom_data .coupon_title').text(r.coupon_title);
                    $('.cupom_data .coupon_discount').text(r.discount_text);
                    $('.cupom_data').fadeIn("slow");
                    $('.alert-discount').hide();
                    $('.alert-discount .alert').hide();
                } else {
                    $('.cupom_data').hide();
                    $('.alert-discount .alert').html("<button type='button' class='close'>" +
                            "<span aria-hidden='true'>&times;</span></button>" + r.error);
                    $('.alert-discount').fadeIn("slow");
                    $('.alert-discount .alert').fadeIn("slow");
                }
            },
            complete: function () {
                $('.add_discount .fa-fw').fadeOut("fast");
            },
            error: function () {}
        });
    });

    // ALTERAR QUANTIDADE DE ITENS NO CARRINHO

    $('.cart_quantity_up').click(function () {
        var iditem = $(this).attr("data-item");
        var input = $("input[id=" + iditem + "]");
        var quantity = parseInt(input.val()) + 1;
        input.val(quantity);

        var price = parseFloat($('tr[id="' + iditem + '"]').attr('data-item-price'));
        addItmePrice(price, quantity, iditem);

        $.post(base_url + "cart/add", {product: iditem, quant: 1});
    });

    $('.cart_quantity_down').click(function () {
        var iditem = $(this).attr("data-item");
        var input = $("input[id=" + iditem + "]");
        var quantity = parseInt(input.val());
        if (quantity > 1) {
            quantity = quantity - 1;
            input.val(quantity);
            var price = parseFloat($('tr[id="' + iditem + '"]').attr('data-item-price'));
            decreaseItmePrice(price, quantity, iditem);
            $.post(base_url + "cart/decrease", {product: iditem, quant: 1});
        }
    });

    function addItmePrice(price, quantity, id) {
        var subtotal = parseFloat($('#sub_total').attr("data-value"));
        var frete = parseFloat($('#shipping_cost').attr("data-value"));
        var cupom = parseFloat($('#coupon_discount').attr("data-value"));
        var total = parseFloat($('#total_cost').attr("data-value"));
        var total_products = parseInt($(".cart_total_products").attr("data-products"));

        var discount = cupom / subtotal;
        var total_item = price * quantity;

        total_products = total_products + 1;
        subtotal = subtotal + price;
        cupom = subtotal * discount;
        total = subtotal + frete - cupom;

        var formatter = new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
            minimumFractionDigits: 2
        });

        $(".cart_total_products").attr("data-products", total_products);
        $(".cart_total_products span").text(total_products);
        $('tr[id="' + id + '"] .cart_total_price').text(formatter.format(total_item));
        $('#sub_total span').html(formatter.format(subtotal) + " <strong>+</strong>");
        $('#sub_total').attr("data-value", subtotal);
        $('#coupon_discount span').html(formatter.format(cupom) + " <strong>-</strong>");
        $('#coupon_discount').attr("data-value", cupom);
        $('#total_cost span').html(formatter.format(total) + " <strong>+</strong>");
        $('#total_cost').attr("data-value", total);
    }

    function decreaseItmePrice(price, quantity, id) {
        var subtotal = parseFloat($('#sub_total').attr("data-value"));
        var frete = parseFloat($('#shipping_cost').attr("data-value"));
        var cupom = parseFloat($('#coupon_discount').attr("data-value"));
        var total = parseFloat($('#total_cost').attr("data-value"));
        var total_products = parseInt($(".cart_total_products").attr("data-products"));

        var discount = cupom / subtotal;
        var total_item = price * quantity;

        total_products = total_products - 1;
        subtotal = subtotal - price;
        cupom = subtotal * discount;
        total = subtotal + frete - cupom;

        var formatter = new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
            minimumFractionDigits: 2
        });

        $(".cart_total_products").attr("data-products", total_products);
        $(".cart_total_products span").text(total_products);
        $('tr[id="' + id + '"] .cart_total_price').text(formatter.format(total_item));
        $('#sub_total span').html(formatter.format(subtotal) + " <strong>+</strong>");
        $('#sub_total').attr("data-value", subtotal);
        $('#coupon_discount span').html(formatter.format(cupom) + " <strong>-</strong>");
        $('#coupon_discount').attr("data-value", cupom);
        $('#total_cost span').html(formatter.format(total) + " <strong>+</strong>");
        $('#total_cost').attr("data-value", total);
    }

    // Calcular frete na página carrinho de compras
    $('.shipping-info').on('click', '.btn-default', function (e) {
        e.preventDefault();
        var cep = $('input[name="cep_code"]').val();        

        if (cep === '') {
            $('.alert-cep').html("<button type='button' class='close'>" +
                    "<span aria-hidden='true'>&times;</span></button>" +
                    "Informe um CEP para realizar o cálculo do frete!");
            $('.alert-cep').fadeIn("slow");
        } else {
            $.ajax({
                url: base_url + "cart/shipping",
                data: {cep: cep},
                type: 'POST',
                dataType: 'json',
                beforeSend: function () {
                    $('.shipping-info .fa-fw').fadeIn(1000);
                },
                success: function (r) {
                    if (r.error === false) {
                        shippingCostUpdate(r.cost);
                        $('.alert-cep').hide();
                        $('.shipping_data .shipping-value').text(r.valor);
                        $('.shipping_data .shipping-date').text(r.prazo);
                        $('.shipping_data').fadeIn("slow");
                    } else {
                        $('.alert-cep').hide();
                        $('.shipping_data').fadeOut("slow");
                        $('.alert-cep').html("<button type='button' class='close'>" +
                                "<span aria-hidden='true'>&times;</span></button>" + r.error);
                        $('.alert-cep').fadeIn("slow");
                    }
                },
                complete: function () {
                    $('.shipping-info .fa-fw').fadeOut('fast');
                },
                error: function () {}
            });
        }
    });

    $('.alert-cep').on('click', '.close', function () {
        $('.alert-cep').fadeOut("slow");
    });

    $('.alert-discount').on('click', '.close', function (e) {
        e.preventDefault();
        $('.alert-discount .alert').fadeOut("slow");
    });

    function shippingCostUpdate(shipping) {
        var coupon = parseFloat($('#coupon_discount').attr("data-value"));
        var sub_total = parseFloat($('#sub_total').attr("data-value"));
        var total_cost = shipping + sub_total - coupon;

        var formatter = new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
            minimumFractionDigits: 2
        });

        $('#shipping_cost span').html(formatter.format(shipping) + " <strong>+</strong>");
        $('#shipping_cost').attr("data-value", shipping);
        $('#shipping_cost').fadeIn("fast");
        $('#total_cost span').html(formatter.format(total_cost) + " <strong>+</strong>");
        $('#total_cost').attr("data-value", total_cost);
    }

    function discountCouponUpdate(discount) {
        var shipping = parseFloat($('#shipping_cost').attr("data-value"));
        var sub_total = parseFloat($('#sub_total').attr("data-value"));
        var total_cost = shipping + sub_total - (discount * sub_total);

        var formatter = new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
            minimumFractionDigits: 2
        });

        $('#coupon_discount span').html(formatter.format(discount * sub_total) + " <strong>-</strong>");
        $('#coupon_discount').attr("data-value", discount * sub_total);
        $('#coupon_discount').fadeIn("fast");
        $('#total_cost span').html(formatter.format(total_cost) + " <strong>+</strong>");
        $('#total_cost').attr("data-value", total_cost);
    }

    /** EXCLUIR ITENS **/

    $('.cart_quantity_delete').click(function () {
        if (confirm("Tem certeza que quer excluir este item do seu carrinho?")) {
            var item = $(this).attr("data-item");
            var quantity = parseInt($("input[id=" + item + "]").val());
            var price = parseFloat($('tr[id="' + item + '"]').attr('data-item-price'));

            $('tr[id="' + item + '"]').fadeOut("slow", function () {
                $(this).remove();
            });
            removeItemCart(quantity, price);
            $.post(base_url + "cart/remove", {product: item});
        }

    });

    function removeItemCart(quantity, price) {

        var total_itens = quantity * price;

        var shipping = parseFloat($('#shipping_cost').attr("data-value"));
        var sub_total = parseFloat($('#sub_total').attr("data-value"));
        var discount = parseFloat($('#coupon_discount').attr("data-value"));
        var total_products = parseInt($(".cart_total_products").attr("data-products"));
        var cupom = discount / sub_total;

        sub_total = sub_total - total_itens;
        discount = sub_total * cupom;
        total_products = total_products - quantity;

        var total_cost = shipping + sub_total - discount;

        var formatter = new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
            minimumFractionDigits: 2
        });

        $(".cart_total_products").attr("data-products", total_products);
        $(".cart_total_products span").text(total_products);
        $('#sub_total span').html(formatter.format(sub_total) + " <strong>+</strong>");
        $('#sub_total').attr("data-value", sub_total);
        $('#coupon_discount span').html(formatter.format(discount) + " <strong>-</strong>");
        $('#coupon_discount').attr("data-value", discount);
        $('#total_cost span').html(formatter.format(total_cost) + " <strong>+</strong>");
        $('#total_cost').attr("data-value", total_cost);
    }

    $('.alert-form').on('click', '.close', function () {
        $('.alert-form').fadeOut("slow");
    });

});
