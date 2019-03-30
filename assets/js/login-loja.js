$(function () {
    
    base_url = $('meta[name="base-url"]').attr("content");
    
    $('form[name="signup_form"]').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: base_url + "user/signup_form",
            data: new FormData(this),
            cache: false,
            contentType: false,
            processData: false,
            type: 'POST',
            dataType: 'json',
            beforeSend: function () {
                $('.signup-form .fa-fw').fadeIn("fast");
            },
            success: function (r) {
                if (r.success === false) {
                    $(".signup-form .alert-form").html("<button class='close'>&times;</button>" + r.error).fadeIn("fast");
                } else {
                    $(".signup-form .alert-form").removeClass("alert-warning").addClass("alert-success");
                    $(".signup-form .alert-form").html("<button class='close'>&times;</button>Cadastro realizado com sucesso! Aguarde...").fadeIn("fast");
                    setTimeout(function () {
                        window.location.assign(base_url + 'checkout');
                    }, 1000);
                }
            },
            complete: function () {
                $('.signup-form .fa-fw').fadeOut("fast");
            },
            error: function (e) {
                console.log(e);
            }
        });
    });
    
    $('form[name="client_login"]').submit(function(e){
        e.preventDefault();
        $.ajax({
            url: base_url + "user/login_form",
            data: new FormData(this),
            processData: false,
            contentType: false,
            cache: false,
            type: 'POST',
            dataType: 'json',
            beforeSend: function(){
                $('.login-form .fa-fw').removeClass("hide");
            },
            success: function(r) {
                $('.debug').html(r);
                if (r.success === false) {
                    $(".login-form .alert-form").html("<button class='close'>&times;</button>" + r.error).fadeIn("fast");
                } else {
                    $(".login-form .alert-form").removeClass("alert-warning").addClass("alert-success");
                    $(".login-form .alert-form").html("<button class='close'>&times;</button>Login realizado com sucesso! Aguarde...").fadeIn("fast");
                    setTimeout(function () {
                        window.location.assign(base_url + 'checkout');
                    }, 1000);
                }
            },
            complete: function(){
                $('.login-form .fa-fw').addClass("hide");
            },
            error: function(){}
        });
    });
});