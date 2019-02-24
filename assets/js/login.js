$(function () {

    base_url = $('meta[name="base-url"]').attr("content");



    $('form[name="formLogin"]').submit(function (e) {
        e.preventDefault();
        var form = $(this).serialize();
        $.ajax({
            url: base_url + 'login/logar',
            data: form,
            type: 'POST',
            success: function (r) {
                $('#login_msg').html(r);
                if (r === 'errempty') {                    
                    $('#login_msg').html(
                            "<div class='alert alert-warning fade show' role='alert'>" +
                            "Os campos <b>Email</b> e <b>Senha</b> são obrigatórios!" +
                            "<button type='button' class='close' data-dismiss='alert' aria-label='Close'>" +
                            "<span aria-hidden='true'>&times;</span>" +
                            "</button>" +
                            "</div>"
                            ).fadeIn('slow');
                } else if (r === 'error') {
                    $('#login_msg').html(
                            "<div class='alert alert-danger fade show' role='alert'>" +
                            "Os dados de <b>Email</b> e/ou <b>Senha</b> não conferem!" +
                            "<button type='button' class='close' data-dismiss='alert' aria-label='Close'>" +
                            "<span aria-hidden='true'>&times;</span>" +
                            "</button>" +
                            "</div>"
                            ).fadeIn('slow');
                } else if (r === 'success') {
                    $('#login_msg').html(
                            "<div class='alert alert-success fade show' role='alert'>" +
                            "Login efetuado com sucesso!" +
                            "<button type='button' class='close' data-dismiss='alert' aria-label='Close'>" +
                            "<span aria-hidden='true'>&times;</span>" +
                            "</button>" +
                            "</div>"
                            ).fadeIn('slow');
                    window.setTimeout(function () {
                        window.location.replace(base_url);
                    }, 2000);
                }
            },
            error: function (e) {
                console.log(e);
            }
        });
    });

});
