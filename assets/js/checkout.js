$(function () {
    base_url = $('meta[name="base-url"]').attr("content");

    $('#form_checkout').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: base_url + "checkout/payment_start",
            data: new FormData(this),
            cache: false,
            contentType: false,
            processData: false,
            type: 'POST',
            dataType: 'json',
            beforeSend: function () {
                $('#modal_sale .modal-body').html('<p><i class="fa fa-spinner fa-pulse fa-fw fa-2x"></i> Só um minuto, estamos trabalhando para deixar tudo certo para você...</p>');
                $('#form_checkout .fa-fw').fadeIn("fast");
                $('#modal_sale .fa-fw').fadeIn("fast");
                $('#modal_sale').modal("show");
            },
            success: function (r) {
                if (r.result === true) {
                    $('#modal_sale .modal-body').html('<div class="alert alert-success">Obrigado por aguardar! Estamos redirecionando você para a página de pagamento!</div>');
                    setTimeout(function () {
                        location.href = base_url + "payment/" + r.sale;
                    }, 1000);
                } else {
                    $('#modal_sale .modal-body').html('<div class="alert alert-warning">' + r.error + '</div>');
                }
            },
            complete: function () {
                $('#form_checkout .fa-fw').fadeOut("fast");
                $('#modal_sale .fa-fw').fadeOut("fast");
            },
            error: function (r) {
                console.log(r);
            }
        });
    });

    /** AUTOCOMPLETA O ENDEREÇO **/
    //Quando o campo cep perde o foco.
    $("#cep").blur(function () {

        //Nova variável "cep" somente com dígitos.
        var cep = $(this).val().replace(/\D/g, '');

        //Verifica se campo cep possui valor informado.
        if (cep !== "") {

            //Expressão regular para validar o CEP.
            var validacep = /^[0-9]{8}$/;

            //Valida o formato do CEP.
            if (validacep.test(cep)) {

                //Preenche os campos com "..." enquanto consulta webservice.
                $("#rua").val("...");
                $("#bairro").val("...");
                $("#cidade").val("...");
                $("#uf").val("");

                //Consulta o webservice viacep.com.br/
                $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function (dados) {

                    if (!("erro" in dados)) {
                        //Atualiza os campos com os valores da consulta.
                        $("#rua").val(dados.logradouro);
                        $("#bairro").val(dados.bairro);
                        $("#cidade").val(dados.localidade);
                        $("#uf").val(dados.uf);
                    } //end if.
                    else {
                        //CEP pesquisado não foi encontrado.
                        limpa_formulário_cep();
                        alert("CEP não encontrado.");
                    }
                });
            } //end if.
            else {
                //cep é inválido.
                limpa_formulário_cep();
                alert("Formato de CEP inválido.");
            }
        } //end if.
        else {
            //cep sem valor, limpa formulário.
            limpa_formulário_cep();
        }

        //Limpa formulário de endereço
        function limpa_formulário_cep() {
            // Limpa valores do formulário de cep.
            $("#rua").val("");
            $("#bairro").val("");
            $("#cidade").val("");
            $("#uf").val("");
        }
    });
});