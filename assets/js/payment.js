/* global PagSeguroDirectPayment */

$(function () {
    base_url = $('meta[name="base-url"]').attr("content");

    var sessionCode = $('#payment-section').attr("data-session");
    PagSeguroDirectPayment.setSessionId(sessionCode);

    $('#card').on('keyup', function () {
        if ($(this).val().length === 6) {
            pagSeguroCard($(this).val());
        }
    });

    $('#card').blur(function () {
        var cardnumber = $(this).val();
        var start = cardnumber.substring(0, 6);
        pagSeguroCard(start);
    });

    function pagSeguroCard(cardnumber) {
        PagSeguroDirectPayment.getBrand({
            cardBin: cardnumber,
            success: function (r) {
                window.flag = r.brand.name;
                var cvvSize = r.brand.cvvSize;
                $('#cvv').attr('maxlength', cvvSize);
                PagSeguroDirectPayment.getInstallments({
                    amount: parseFloat($('#valor_venda').val()),
                    brand: window.flag,
                    maxInstallmentNoInterest: 0,
                    success: function (r) {
                        var parc = r.installments[window.flag];
                        var html = '';
                        var formatter = new Intl.NumberFormat('pt-BR', {
                            style: 'currency',
                            currency: 'BRL',
                            minimumFractionDigits: 2
                        });
                        for (var i in parc) {
                            var juros = '';
                            var value = parc[i].quantity + ';' + parc[i].installmentAmount + ';';
                            if (parc[i].interestFree === true) {
                                value += 'true';
                                juros = ' (Sem juros)';
                            } else {
                                value += 'false';
                                juros = ' (com juros)';
                            }
                            html += '<option value="' + value + '">' + parc[i].quantity + 'x de ' + formatter.format(parc[i].installmentAmount) + juros + '</option>';
                        }
                        $('#installments').html(html);
                        $('.installments').removeClass('hide');
                    },
                    error: function (e) {
                        console.log(e);
                        $('#installments').html('');
                        $('.installments').addClass('hide');
                    },
                    complete: function () {}
                });
            },
            error: function () {},
            complete: function () {}
        });
    }

    $('form[name="payment_form"').submit(function (e) {
        e.preventDefault();
        var id = PagSeguroDirectPayment.getSenderHash();
        var dados = $(this).serialize();

        var cardnumber = $('#card').val();
        var cvv = $('#cvv').val();
        var em = $('#expiration_month').val();
        var ey = $('#expiration_year').val();
        var ins = $('#installments').val();

        $('.pagmsg').fadeOut("fast");

        if (cardnumber !== '' && cvv !== '' && em !== '' && ey !== '' && ins !== '') {
            PagSeguroDirectPayment.createCardToken({
                cardNumber: cardnumber,
                brand: window.flag,
                cvv: cvv,
                expirationMonth: em,
                expirationYear: ey,
                success: function (r) {
                    window.cardToken = r.card.token;
                    $.ajax({
                        url: base_url + "payment/send",
                        data: dados + "&cardToken=" + window.cardToken + '&idpagseguro=' + id,
                        type: 'POST',
                        dataType: 'json',
                        success: function (r) {
                            if (r.error === false) {
                                pagmsg('Seu pagamento foi processado com sucesso!', 'success');
                                setTimeout(function () {
                                    location.href = base_url + "payment/obrigado";
                                }, 1000);
                            } else {
                                alert(r.msg);
                            }
                        },
                        error: function (r) {
                            console.log(r);
                            //$('.debug').html(r.responseText);
                            pagmsg('Desculpe não conseguimos processar o seu pagamento, favor entre em contato com a administração da loja para informar o problema!', 'warning');
                        }
                    });
                },
                error: function (e) {
                    console.log(e);
                    if (e.errors['10000']) {
                        pagmsg('Cartão inválido ou não aceito!', 'danger');
                    } else if (e.errors['10001']) {
                        pagmsg('Número do cartão não é válido!', 'warning');
                    } else if (e.errors['10002']) {
                        pagmsg('Data de expiração inválida!', 'warning');
                    } else if (e.errors['10003']) {
                        $('.workcontrol_load').fadeOut(100);
                        pagmsg('Código inválido!', 'warning');
                    } else if (e.errors['10004']) {
                        pagmsg('Código obrigatório!', 'warning');
                    } else if (e.errors['10006']) {
                        pagmsg('CVV inválido!', 'warning');
                    } else if (e.errors['30400']) {
                        pagmsg('Dados incorretos no cartão. Favor, <b>verifique os dados do cartão</b> e tente novamente!', 'warning');
                    } else {
                        pagmsg('Erro ao processar pagamento! Existe um problema com os dados, autorização ou comunicação com o cartão. Para continuar, <b>atualize a página</b> e tente novamente!', 'warning');
                    }
                },
                complete: function () {}
            });
        }
    });
    function pagmsg(msg, tipo) {
        $('.pagmsg').html(
                '<div class="alert alert-' + tipo + '">'+
                '<button type="button" class="close" data-dismiss="alert"' +
                ' aria-label="Close"><span aria-hidden="true">&times;</span>' +
                '</button>' + msg +
                '</div>');
        $('.pagmsg').fadeIn("slow");
    }

});
