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

    $('[data-toggle="tooltip"]').tooltip();

    /** 
     * @param {string} msg
     * @param {string} tipo
     * @param {string} id
     * @returns {string}
     */

    function showModal(msg, tipo, id) {
        $('#' + id + ' .modal-body').hide().html(
                "<div class='alert alert-" + tipo + "' " +
                "role='alert'>" + msg + "</div>"
                ).fadeIn('slow');
    }

    /***CADASTRO DE NOVOS USUÁRIOS***/

    $('#choose_image').click(function (e) {
        e.preventDefault();
        $('#image_profile').click();
    });

    $('#image_profile').change(function () {
        readURL(this);
    });

    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('.image_preview').css('background-image', 'url(' + e.target.result + ')');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }    
    
    showModal("Todos os campos devem ser preenchidos!", "warning", "modal_usuarios_cad");

    $('form[name="cadastra_usuarios"]').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: base_url + 'usuarios/save',
            data: new FormData(this),
            type: 'POST',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {
                $("#modal_usuarios_cad").modal("show");
            },
            success: function (r) {
                $("#modal_cadastro .close_modal").fadeIn('slow');
                if (r === 'errempty') {
                    showModal("Todos os campos devem ser preenchidos!", "warning", "modal_usuarios_cad");
                } else if (r === 'errimage') {
                    showModal("É obrigátorio enviar uma imagem para o usuário!", "warning", "modal_usuarios_cad");
                } else if (r === 'errconfirm') {
                    showModal("As senhas não coincidem!", "warning", "modal_cadastro");
                } else if (r === 'errpassword') {
                    showModal("A senha deve ter no mínimo 6 caracteres e deve conter pelo menos uma letra e um algarismo!", "info", 'modal_usuarios_cad');
                } else if (r === 'errmail') {
                    showModal("Parece que o email que você informou não possui um formato válido!", "warning", "modal_usuarios_cad");
                } else if (r === 'errmailread') {
                    showModal("O email que você está tentando usar já está cadastrado para outro usuário!", "warning", "modal_usuarios_cad");
                } else if (r === 'errcreate') {
                    showModal("Erro ao tentar cadastrar usuário no banco de dados!", "danger", "modal_usuarios_cad");
                } else if (r === 'errupload') {
                    showModal("Erro ao realizar upload da imagem, tente um arquivo de tamanho menor! <br><br>Caso o erro persista fale com o administrador.", "danger", "modal_usuarios_cad");
                } else {
                    showModal("Usuário cadastrado com sucesso! Aguarde, estamos redirecionando... <i class='fa fa-spinner fa-spin fa-fw'>", "success", "modal_usuarios_cad");
                    setTimeout(function () {
                        window.location.assign(base_url + 'usuarios/editar/' + r);
                    }, 2000);
                }
            },
            error: function (e) {
                console.log(e);
            }
        });
    });

    /**
     * 
     * @param {Object} form
     * @param {String} action
     * @returns {undefined}
     */

    function ajaxEditUsers(form, action = null) {
        var page = action === 'profile' ? 'profile' : 'usuarios/editar/';
        $.ajax({
            url: base_url + 'usuarios/update',
            data: form,
            type: 'POST',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {
                $("#modal_cadastro").modal("show");
            },
            success: function (r) {
                $("#modal_cadastro .close_modal").fadeIn('slow');
                if (r === 'errosenhac') {
                    showModal("Preencha a senha de confirmação!", "warning", "modal_cadastro");
                } else if (r === 'errsenhaconfirm') {
                    showModal("As senhas não estão iguais!", "warning", "modal_cadastro");
                } else if (r === 'errempty') {
                    showModal("Os campos <b>nome</b>, <b>sobrenome</b> e <b>email</b> são obrigatórios!", "warning", "modal_cadastro");
                } else if (r === 'errsenha') {
                    showModal("A senha deve ter entre 6 e 16 caracteres e pelo menos uma letra e um algarismo!", "warning", "modal_cadastro");
                } else if (r === 'errmailread') {
                    showModal("O email que você digitou já está sendo usado por outra conta!", "warning", "modal_cadastro");
                } else if (r === 'errmail') {
                    showModal("O email digitado parece não ter um formato válido", "warning", "modal_cadastro");
                } else if (r === 'error') {
                    showModal("Erro ao inserir dados no banco", "danger", "modal_cadastro");
                } else {
                    r = action === 'profile' ? '' : r;
                    showModal("Usuário atualizado com sucesso! Aguarde, estamos atualizando a página... <i class='fa fa-spinner fa-spin fa-fw'>", "success", "modal_cadastro");
                    setTimeout(function () {
                        window.location.assign(base_url + page + r);
                    }, 2000);
                }
            },
            error: function (e) {
                console.log(e);
            }
        });
    }

    $('form[name="editar_usuarios"]').submit(function (e) {
        e.preventDefault();
        var form = new FormData(this);
        ajaxEditUsers(form);
    });

    $('form[name="editar_perfil"]').submit(function (e) {
        e.preventDefault();
        var form = new FormData(this);
        ajaxEditUsers(form, 'profile');
    });

    $("#modal_cadastro .close_modal").on('click', function () {
        $("#modal_cadastro .close_modal").fadeOut('slow');
        $('#modal_cadastro .modal-body').html(
                "<div class='alert alert-info' role='alert'>" +
                "<i class='fa fa-spinner fa-spin fa-fw'>" +
                "</i> Aguarde, carregando..." +
                "</div>"
                );
    });


    $('#perfil_delete').click(function (e) {
        e.preventDefault();
        $("#modal_delete").modal("show");
    });

    $("#delete_user_confirm").click(function () {
        var id = $('input[name="id_usuario"]').val();
        $.ajax({
            url: base_url + "profile/delete",
            data: {user: id},
            type: 'POST',
            beforeSend: function () {},
            success: function (r) {
                if (r === 'success') {
                    $('#modal_delete .modal-body').hide().html(
                            "<div class='alert alert-success' " +
                            "role='alert'>Seu perfil foi excluído com sucesso!</div>"
                            ).fadeIn('slow');
                    setTimeout(function () {
                        window.location.assign(base_url + 'login/logout');
                    }, 2000);
                } else if (r === 'erradmin') {
                    $('#modal_delete .modal-body').hide().html(
                            "<div class='alert alert-danger' " +
                            "role='alert'>Erro ao excluir, você não pode excluir o único <b>SuperAdmin</b> do sistema!</div>"
                            ).fadeIn('slow');
                } else if (r === 'error') {
                    $('#modal_delete .modal-body').hide().html(
                            "<div class='alert alert-danger' " +
                            "role='alert'>Erro ao tentar excluir usuário no banco de dados!</div>"
                            ).fadeIn('slow');
                }
            },
            error: function () {}
        });
    });

    $('.user_delete').click(function (e) {
        e.preventDefault();
        var userid = $(this).attr("id");
        var nome = $(this).attr('data-name');
        if (confirm('Confirma a exclusão do usuário ' + nome + '?')) {
            $.ajax({
                url: base_url + 'usuarios/delete/',
                data: {user: userid},
                type: 'POST',
                beforeSend: function () {
                    $('tr[id="user_' + userid + '"]').addClass('apagando');
                },
                success: function (r) {
                    if (r === 'success') {
                        $('tr[id="user_' + userid + '"]').fadeOut('slow');
                    } else if (r === 'erradmin') {
                        $('#modal_usuarios_del').modal('show');
                        $('#modal_usuarios_del .modal-body').hide().html(
                                "<div class='alert alert-danger' " +
                                "role='alert'>Erro ao excluir, você não pode excluir o único <b>SuperAdmin</b> do sistema!</div>"
                                ).fadeIn('slow');
                        $('#modal_usuarios_del .btn_close').show();
                        $('tr[id="user_' + userid + '"]').removeClass('apagando');
                    } else if (r === 'errthis') {
                        $('#modal_usuarios_del').modal('show');
                        $('#modal_usuarios_del .modal-body').hide().html(
                                "<div class='alert alert-danger' " +
                                "role='alert'>Erro ao excluir, você não pode excluir a sua própria conta por aqui!</div>"
                                ).fadeIn('slow');
                        $('#modal_usuarios_del .btn_close').show();
                        $('tr[id="user_' + userid + '"]').removeClass('apagando');
                    } else if (r === 'error') {
                        $('#modal_usuarios_del').modal('show');
                        $('#modal_usuarios_del .modal-body').hide().html(
                                "<div class='alert alert-danger' " +
                                "role='alert'>Erro ao tentar excluir usuário no banco de dados!</div>"
                                ).fadeIn('slow');
                        $('#modal_usuarios_del .btn_close').show();
                        $('tr[id="user_' + userid + '"]').removeClass('apagando');
                    }
                },
                erro: function (e) {
                    console.log(e);
                }
            });
        }
    });

    $('form[name="editar_clientes"]').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: base_url + 'clientes/update',
            data: new FormData(this),
            type: 'POST',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {
                $("#modal_clientes").modal("show");
            },
            success: function (r) {
                $("#modal_clientes .close_modal").fadeIn('slow');
                if (r === 'errempty') {
                    showModal("Todos os campos são obrigatórios, exceto os campos complemento e senha!", "warning", "modal_clientes");
                } else if (r === 'errmail') {
                    showModal("Parece que o email que você informou não possui um formato válido!", "warning", "modal_clientes");
                } else if (r === 'errsenha') {
                    showModal("A senha deve ter entre 6 e 16 caracteres e pelo menos uma letra e um algarismo!", "warning", "modal_clientes");
                } else if (r === 'errsconfirm') {
                    showModal("As senhas não coincidem!", "warning", "modal_clientes");
                } else if (r === 'errmailread') {
                    showModal("O email que você está tentando usar já está cadastrado para outro usuário!", "warning", "modal_clientes");
                } else if (r === 'errupdate') {
                    showModal("Erro ao tentar atualizar cliente no banco de dados!", "danger", "modal_clientes");
                } else if (r === 'errsenhac') {
                    showModal("Preencha a senha de confirmação!", "warning", "modal_clientes");
                } else {
                    showModal("Cliente atualizado com sucesso!", "success", "modal_clientes");
                    setTimeout(function () {
                        window.location.assign(base_url + 'clientes/editar/' + r);
                    }, 2000);
                }
            },
            error: function (e) {
                console.log(e);
            }
        });
    });

    /*****CATEGORIAS*****/
    $('form[name="cadastra_categorias"]').submit(function (event) {
        event.preventDefault();
        $.ajax({
            url: base_url + 'categorias/save',
            data: new FormData(this),
            type: 'POST',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {
                $("#modal_categorias_cad").modal("show");
            },
            success: function (r) {
                $(".close_modal").fadeIn('slow');
                if (r === 'errtititle') {
                    showModal("Informe um título para esta categoria!", "warning", "modal_categorias_cad");
                } else if (r === 'errcreate') {
                    showModal("Erro ao cadastrar informações no banco de dados!", "danger", "modal_categorias_cad");
                } else {
                    showModal("Categoria cadastrada com sucesso!", "success", "modal_categorias_cad");
                    setTimeout(function () {
                        window.location.assign(base_url + 'categorias/editar/' + r);
                    }, 2000);
                }

            },
            error: function (e) {
                console.log(e);
            }
        });
    });

    $('form[name="atualiza_categorias"]').submit(function (event) {
        event.preventDefault();
        $.ajax({
            url: base_url + 'categorias/update',
            data: new FormData(this),
            type: 'POST',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {
                $("#modal_categorias_cad").modal("show");
            },
            success: function (r) {
                $(".close_modal").fadeIn('slow');
                if (r === 'errtititle') {
                    showModal("Informe um título para esta categoria!", "warning", "modal_categorias_cad");
                } else if (r === 'error') {
                    showModal("Erro ao atualizar informações no banco de dados!", "danger", "modal_categorias_cad");
                } else {
                    showModal("Categoria atualizada com sucesso!", "success", "modal_categorias_cad");
                    setTimeout(function () {
                        window.location.assign(base_url + 'categorias/editar/' + r);
                    }, 2000);
                }
            },
            error: function (e) {
                console.log(e);
            }
        });
    });

    /**EXCLUIR CATEGORIAS**/
    $('.cat_delete').click(function (e) {
        e.preventDefault();
        var catid = $(this).attr("id");
        var catname = $(this).attr('data-name');
        if (confirm("Confirma a exclusão da categoria " + catname + "?")) {
            $.ajax({
                url: base_url + "categorias/delete/",
                type: 'POST',
                data: {catid: catid},
                beforeSend: function () {
                    $('tr[id="' + catid + '"]').addClass("apagando");
                },
                success: function (r) {
                    if (r === 'success') {
                        $('tr[id="' + catid + '"]').fadeOut('slow');
                        $('#modal_categorias_del').modal('show');
                        $('#modal_categorias_del .modal-body').hide().html(
                                "<div class='alert alert-success' " +
                                "role='alert'>A categoria <strong>" + catname + "</strong> foi excluída com sucesso!</div>"
                                ).fadeIn('slow');
                        $('#modal_usuarios_del .btn_close').show();
                    } else if (r === 'error') {
                        $('#modal_categorias_del').modal('show');
                        $('#modal_categorias_del .modal-body').hide().html(
                                "<div class='alert alert-danger' " +
                                "role='alert'>Erro ao tentar excluir categoria no banco de dados!</div>"
                                ).fadeIn('slow');
                        $('#modal_usuarios_del .btn_close').show();
                        $('tr[id="' + catid + '"]').removeClass('apagando');
                    } else if (r === 'errdaughters') {
                        $('#modal_categorias_del').modal('show');
                        $('#modal_categorias_del .modal-body').hide().html(
                                "<div class='alert alert-warning' " +
                                "role='alert'>Você não pode escluir uma categoria que possui subcategorias dependentes!</div>"
                                ).fadeIn('slow');
                        $('#modal_usuarios_del .btn_close').show();
                        $('tr[id="' + catid + '"]').removeClass('apagando');
                    } else if (r === 'errproducts') {
                        $('#modal_categorias_del').modal('show');
                        $('#modal_categorias_del .modal-body').hide().html(
                                "<div class='alert alert-warning' " +
                                "role='alert'>Você não pode escluir uma categoria que possui produtos cadastrados!</div>"
                                ).fadeIn('slow');
                        $('#modal_usuarios_del .btn_close').show();
                        $('tr[id="' + catid + '"]').removeClass('apagando');
                    }
                },
                error: function (error) {
                    console.log(error);
                }
            });
        }
    });

    /** MARCAS E FABRICANTES **/
    $('form[name="cadastra_marcas"]').submit(function (event) {
        event.preventDefault();
        $.ajax({
            url: base_url + 'fabricantes/save',
            data: new FormData(this),
            type: 'POST',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {
                $("#modal_fabricantes_cad").modal("show");
            },
            success: function (r) {
                $("#modal_fabricantes_cad .close_modal").fadeIn('slow');
                if (r === 'errempty') {
                    showModal("Informe um <strong>título</strong> para este fabricante!", "warning", "modal_fabricantes_cad");
                } else if (r === 'errisset') {
                    showModal("Já existe uma marca cadastrada com este título", "warning", "modal_fabricantes_cad");
                } else if (r === 'error') {
                    showModal("Não foi possível cadastrar!", "danger", "modal_fabricantes_cad");
                } else {
                    showModal("Cadastro realizado com sucesso!", "success", "modal_fabricantes_cad");
                    setTimeout(function () {
                        window.location.assign(base_url + 'fabricantes/editar/' + r);
                    }, 2000);
                }
            },
            error: function (e) {
                console.log(e);
            }
        });
    });

    $('form[name="atualiza_marcas"]').submit(function (event) {
        event.preventDefault();
        $.ajax({
            url: base_url + 'fabricantes/update',
            data: new FormData(this),
            type: 'POST',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {
                $("#modal_fabricantes_edit").modal("show");
            },
            success: function (r) {
                $("#modal_fabricantes_edit .close_modal").fadeIn('slow');
                if (r === 'errempty') {
                    showModal("Informe um <strong>título</strong> para este fabricante!", "warning", "modal_fabricantes_edit");
                } else if (r === 'errisset') {
                    showModal("Já existe uma marca cadastrada com este título", "warning", "modal_fabricantes_edit");
                } else if (r === 'error') {
                    showModal("Não foi possível atualizar os dados no banco!", "danger", "modal_fabricantes_edit");
                } else {
                    showModal("Atualização realizada com sucesso!", "success", "modal_fabricantes_edit");
                    setTimeout(function () {
                        window.location.assign(base_url + 'fabricantes/editar/' + r);
                    }, 2000);
                }
            },
            error: function (e) {
                console.log(e);
            }
        });
    });

    $('.brand_delete').click(function (event) {
        event.preventDefault();
        var brandname = $(this).attr("data-name");
        var brandid = $(this).attr("id");
        if (confirm("Deseja realmente excluir a marca " + brandname + "?")) {
            $.ajax({
                url: base_url + "fabricantes/delete/" + brandid,
                type: 'POST',
                beforeSend: function () {
                    $('tr[id="' + brandid + '"]').addClass("apagando");
                    $('#modal_fabricantes_edit').modal("show");
                },
                success: function (r) {
                    $('tr[id="' + brandid + '"]').removeClass('apagando');
                    if (r === 'errproducts') {
                        showModal("Não foi possível excluir pois esta marca possui produtos cadastrados!", "warning", "modal_fabricantes_edit");
                    } else if (r === 'error') {
                        showModal("Erro ao tentar excluir do banco de dados!", "danger", "modal_fabricantes_edit");
                    } else if (r === 'success') {
                        showModal("Marca exclída com sucesso!", "success", "modal_fabricantes_edit");
                        $('tr[id="' + brandid + '"]').fadeOut("slow");
                    }
                    $('#modal_fabricantes_edit .btn_close').show();
                },
                error: function (e) {
                    $('#modal_fabricantes_edit .close_modal').fadeIn("fast");
                    showModal("Desculpe, isto não era para estar acontecendo. Por favor, informe ao administrador!", "danger", "modal_fabricantes_edit");
                    console.log(e);
                }
            });
        }
    });

    /** FORNECEDORES **/

    $('#check_cnpj').on('click', function () {
        $('.label_cpf_cnpj').text("Número do CNPJ");
        $('.cpf_cnpj').attr("placeholder", "00.000.000/0000-00").removeClass('cpf').addClass('cnpj');
        $('.cpf_cnpj').mask('00.000.000/0000-00', {reverse: true});

    });

    $('#check_cpf').on('click', function () {
        $('.label_cpf_cnpj').text("Número do CPF");
        $('.cpf_cnpj').attr("placeholder", "000.000.000-00").removeClass('cnpj').addClass('cpf');
        $('.cpf_cnpj').mask('000.000.000-00', {reverse: true});
    });

    $('form[name="cadastro_fornecedores"]').submit(function (event) {
        event.preventDefault();
        $.ajax({
            url: base_url + 'fornecedores/save',
            data: new FormData(this),
            type: 'POST',
            contentType: false,
            cache: false,
            processData: false,
            dataType: 'json',
            beforeSend: function () {
                $("#modal_fornecedores_cad").modal("show");
            },
            success: function (r) {
                $("#modal_fornecedores_cad .close_modal").fadeIn('slow');
                if (r.response === 'success') {
                    showModal("Cadastro realizado com sucesso!", "success", "modal_fornecedores_cad");
                    setTimeout(function () {
                        window.location.assign(base_url + 'fornecedores/editar/' + r.fornecedor.id_fornecedor);
                    }, 2000);
                } else {
                    showModal(r.response, "warning", "modal_fornecedores_cad");
                }
            },
            error: function (e) {
                $('#modal_fornecedores_cad .close_modal').fadeIn("fast");
                showModal("Desculpe, isto não era para estar acontecendo. Por favor, informe ao administrador!", "danger", "modal_fornecedores_cad");
                console.log(e);
            }
        });
    });

    $('form[name="atualizar_fornecedores"]').submit(function (event) {
        event.preventDefault();
        $.ajax({
            url: base_url + 'fornecedores/update',
            data: new FormData(this),
            type: 'POST',
            contentType: false,
            cache: false,
            processData: false,
            dataType: 'json',
            beforeSend: function () {
                $("#modal_fornecedores_edit").modal("show");
            },
            success: function (r) {
                $("#modal_fornecedores_edit .close_modal").fadeIn('slow');
                if (r.response === 'success') {
                    showModal("Cadastro atualizado com sucesso!", "success", "modal_fornecedores_edit");
                    setTimeout(function () {
                        window.location.assign(base_url + 'fornecedores/editar/' + r.fornecedor.id_fornecedor);
                    }, 2000);
                } else {
                    showModal(r.response, "warning", "modal_fornecedores_edit");
                }
            },
            error: function (e) {
                $('#modal_fornecedores_edit .close_modal').fadeIn("fast");
                showModal("Desculpe, isto não era para estar acontecendo. Por favor, informe ao administrador!", "danger", "modal_fornecedores_edit");
                console.log(e);
            }
        });
    });

    $('.delete_fornecedor').click(function (e) {
        e.preventDefault();
        var idfor = $(this).attr("id");
        var namefor = $(this).attr('data-name');
        if (confirm("Tem certeza que deseja excluir o fornecedor " + namefor + "?")) {
            $.ajax({
                url: base_url + 'fornecedores/delete/' + idfor,
                type: 'POST',
                dataType: 'json',
                beforeSend: function () {
                    $('tr[id="' + idfor + '"]').addClass("apagando");
                    $("#modal_fornecedores").modal("show");
                },
                success: function (r) {
                    $("#modal_fornecedores .close_modal").fadeIn('slow');
                    if (r.response === 'success') {
                        showModal("Fornecedor excluído com sucesso!", "success", "modal_fornecedores");
                        $('tr[id="' + idfor + '"]').fadeOut("slow");
                    } else {
                        showModal(r.response, "warning", "modal_fornecedores");
                        $('tr[id="' + idfor + '"]').removeClass("apagando");
                    }
                },
                error: function (e) {
                    $('#modal_fornecedores .close_modal').fadeIn("fast");
                    showModal("Desculpe, isto não era para estar acontecendo. Por favor, informe ao administrador!", "danger", "modal_fornecedores");
                    console.log(e);
                }
            });
        }
    });

    /** GESTÂO DE PRODUTOS **/


    /* global CKEDITOR */
    if ($("#ck_editor").length > 0) {
        CKEDITOR.replace('ck_editor');
    }

    /**
     * 
     * @param {type} input
     * @param {type} placeToInsertImagePreview
     * @returns {undefined}
     */
    function imagesPreview(input, placeToInsertImagePreview) {
        $('div.product_gallery').html('');
        if (input.files) {
            var filesAmount = input.files.length;
            for (i = 0; i < filesAmount; i++) {
                var reader = new FileReader();

                reader.onload = function (event) {
                    var content = "<div class='col-md-2'><div class='product_preview'></div></div>";
                    $(content).appendTo(placeToInsertImagePreview);
                    $('.product_preview').last().css('background-image', 'url(' + event.target.result + ')');
                };

                reader.readAsDataURL(input.files[i]);
            }
        }

    }

    $('#image_products').on('change', function () {
        imagesPreview(this, 'div.product_gallery');
    });

    $('#choose_image_products').click(function (e) {
        e.preventDefault();
        $('#image_products').click();
    });

    $('form[name="cadastra_produto"]').submit(function (event) {
        event.preventDefault();

        for (var i in CKEDITOR.instances) {
            CKEDITOR.instances[i].updateElement();
        }

        $.ajax({
            url: base_url + 'produtos/save',
            data: new FormData(this),
            type: 'POST',
            contentType: false,
            cache: false,
            processData: false,
            dataType: 'json',
            beforeSend: function () {
                $("#modal_cadastro_produto .modal-body").html("<div class='alert alert-info' role='alert'>" +
                        "<i class='fa fa-spinner fa-spin fa-fw'></i> Aguarde, carregando..." +
                        "</div> ");
                $("#modal_cadastro_produto").modal("show");
            },
            success: function (r) {
                $("#modal_cadastro_produto .close_modal").fadeIn('slow');
                if (r.response === 'success') {
                    showModal("Produto cadastrado com sucesso! Aguarde, estamos redirecionando...", "success", "modal_cadastro_produto");
                    setTimeout(function () {
                        window.location.assign(base_url + 'produtos/editar/' + r.produto.id_produto);
                    }, 2000);
                } else {
                    showModal(r.response, "warning", "modal_cadastro_produto");
                }
            },
            error: function (e) {
                $('#modal_cadastro_produto .close_modal').fadeIn("fast");
                showModal("Desculpe, isto não era para estar acontecendo. Por favor, informe ao administrador!", "danger", "modal_cadastro_produto");
                console.log(e);
            }
        });
    });

    /**
     * 
     * @param {type} input
     * @param {type} placeToInsertImagePreview
     * @returns {undefined}
     */
    function imagesPreviewAdd(input, placeToInsertImagePreview) {
        $('.preview_add').remove();
        var altura = $('.product_preview').outerHeight() + 20;
        if (input.files) {
            var filesAmount = input.files.length;
            for (i = 0; i < filesAmount; i++) {
                var reader = new FileReader();
                reader.onload = function (event) {
                    var content = "<div class='col-md-2 preview_add'><div class='product_preview'></div></div>";
                    $(content).height(altura).appendTo(placeToInsertImagePreview);
                    $('.preview_add .product_preview').last().css('background-image', 'url(' + event.target.result + ')');
                };
                reader.readAsDataURL(input.files[i]);
            }
        }

    }

    $('#image_products_add').on('change', function () {
        imagesPreviewAdd(this, 'div.product_gallery');
    });

    $('#choose_image_products_add').click(function (e) {
        e.preventDefault();
        $('#image_products_add').click();
    });

    $('form[name="editar_produto"]').submit(function (e) {
        e.preventDefault();

        for (var i in CKEDITOR.instances) {
            CKEDITOR.instances[i].updateElement();
        }

        $.ajax({
            url: base_url + "produtos/update",
            data: new FormData(this),
            type: 'POST',
            contentType: false,
            cache: false,
            processData: false,
            dataType: 'json',
            beforeSend: function () {
                $("#modal_editar_produto .modal-body").html("<div class='alert alert-info' role='alert'><i class='fa fa-spinner fa-spin fa-fw'></i> Aguarde, atualizando...</div>");
                $("#modal_editar_produto").modal("show");
            },
            success: function (r) {
                $("#modal_editar_produto .close_modal").fadeIn('slow');
                if (r.response === 'success') {
                    showModal("Produto atualizado com sucesso! Aguarde, estamos atualizado a página...", "success", "modal_editar_produto");
                    setTimeout(function () {
                        window.location.assign(base_url + 'produtos/editar/' + r.produto.id_produto);
                    }, 2000);
                } else {
                    showModal(r.response, "warning", "modal_editar_produto");
                }
            },
            error: function (e) {
                $('.debug').html(e.responseText);
                $('#modal_editar_produto .close_modal').fadeIn("fast");
                showModal("Desculpe, isto não era para estar acontecendo. Por favor, informe ao administrador!", "danger", "modal_editar_produto");
                console.log(e);
            }
        });
    });

    /** EXCLUIR IMAGENS DO PRODUTO **/
    $('.product_gallery').on("click", ".btn_delete", function (e) {
        e.preventDefault();
        var imageid = $(this).attr("id");
        var dados = {id: imageid};
        $.ajax({
            url: base_url + "produtos/delete_image",
            data: dados,
            type: 'POST',
            dataType: 'json',
            success: function (r) {
                if (r.response === 'success') {
                    $.post(base_url + "produtos/read_images", {idproduto: r.result}, function (r) {
                        $('.product_gallery').fadeTo(500, 0.3, function () {
                            $(this).html(r);
                        });
                        $('.product_gallery').fadeTo(200, 1);
                    });
                } else {
                    alert(r.response);
                }
            }
        });
    });

    /** MUDAR CAPA DO PRODUTO **/
    $('.product_gallery').on("click", ".btn_image", function (e) {
        e.preventDefault();
        var imageid = $(this).attr("id");
        var dados = {id: imageid};
        $.ajax({
            url: base_url + "produtos/update_cover",
            data: dados,
            type: 'POST',
            dataType: 'json',
            success: function (r) {
                if (r.response === 'success') {
                    $.post(base_url + "produtos/read_images", {idproduto: r.result}, function (r) {
                        $('.product_gallery').fadeTo(500, 0.3, function () {
                            $(this).html(r);
                        });
                        $('.product_gallery').fadeTo(200, 1);
                    });
                } else {
                    alert(r.response);
                }
            }
        });
    });

    /** EXCLUIR PRODUTO **/
    $('.deletar_produto').click(function () {
        var productid = $(this).attr("id");
        var tituloproduto = $(this).attr("data-name");
        if (confirm("Tem certeza que quer excluir o produto " + tituloproduto)) {
            $.ajax({
                url: base_url + "produtos/delete",
                type: "POST",
                dataType: "json",
                data: {id: productid},
                beforeSend: function () {
                    $('tr[id="' + productid + '"]').addClass("apagando");
                },
                success: function (r) {
                    $('#modal_editar_produto').modal("show");
                    $('#modal_editar_produto .close_modal').fadeIn("fast");
                    if (r.response === 'success') {
                        showModal("Produto excluído com sucesso!", "success", "modal_editar_produto");
                        $('tr[id="' + productid + '"]').fadeOut("slow", function () {
                            $(this).remove();
                            if ($('tr').length < 2) {
                                location.reload(true);
                            }
                        });
                    } else {
                        showModal(r.response, "warning", "modal_editar_produto");
                        $('tr[id="' + productid + '"]').removeClass("apagando");
                    }
                },
                error: function (e) {
                    $('#modal_editar_produto .close_modal').fadeIn("fast");
                    showModal("Desculpe, isto não era para estar acontecendo. Por favor, informe ao administrador!", "danger", "modal_editar_produto");
                    console.log(e);
                }
            });
        }
    });

    /** PESQUISAR PRODUTOS **/
    $('form[name="search_products"]').submit(function (e) {
        e.preventDefault();
        var pesquisa = encodeURI($('input[name="search_products"]').val().trim());
        if (pesquisa !== '') {
            window.location.assign(base_url + 'produtos/pesquisar/pagina/1/' + pesquisa);
        }
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

    /** GERENCIA INFORMAÇÔES DA LOJA **/

    //Muda a imagem de preview da Logo
    function readURLLogo(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('.image_preview_logo').css('background-image', 'url(' + e.target.result + ')');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    $('#choose_logo').click(function (e) {
        e.preventDefault();
        $('#image_logo').click();
    });

    $('#image_logo').change(function () {
        readURLLogo(this);
    });

    $('form[name="edit_store"]').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: base_url + "loja/update",
            data: new FormData(this),
            type: 'POST',
            contentType: false,
            processData: false,
            cache: false,
            dataType: "json",
            beforeSend: function () {
                $('#modal_edit_store').modal("show");
            },
            success: function (r) {
                $('#modal_edit_store .close_modal').fadeIn("fast");
                if (r.response === 'success') {
                    showModal("Dados atualizados com sucesso! Aguarde, estamos atualizado a página...", "success", "modal_edit_store");
                    setTimeout(function () {
                        window.location.assign(base_url + 'loja');
                    }, 2000);
                } else {
                    showModal(r.response, "warning", "modal_edit_store");
                }
            },
            error: function (e) {
                $('#modal_edit_store .close_modal').fadeIn("fast");
                showModal("Desculpe, isto não era para estar acontecendo. Por favor, informe ao administrador!", "danger", "modal_edit_store");
                console.log(e);
            }
        });
    });


    $('form[name="edit_store_page"]').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: base_url + "loja/update_page",
            data: new FormData(this),
            type: 'POST',
            contentType: false,
            processData: false,
            cache: false,
            dataType: "json",
            beforeSend: function () {
                $('#modal_edit_store_page').modal("show");
            },
            success: function (r) {
                $('#modal_edit_store_page .close_modal').fadeIn("fast");
                if (r.response === 'success') {
                    showModal("Dados atualizados com sucesso! Aguarde, estamos atualizado a página...", "success", "modal_edit_store_page");
                    setTimeout(function () {
                        window.location.assign(base_url + 'loja/edit_page/' + r.result.id);
                    }, 2000);
                } else {
                    showModal(r.response, "warning", "modal_edit_store_page");
                }
            },
            error: function (e) {
                $('.debug').html(e.responseText);
                $('#modal_edit_store_page .close_modal').fadeIn("fast");
                showModal("Desculpe, isto não era para estar acontecendo. Por favor, contate o administrador!", "danger", "modal_edit_store_page");
                console.log(e);
            }
        });
    });

    $('.image_page').on('click', '.delete_image_page', function (e) {
        e.preventDefault();
        var idpage = $(this).attr("id");
        if (confirm("Confirma exclusão da imagem?")) {
            $.post(base_url + "loja/delete_image", {id: idpage}, function () {
                $('.image_page .image_preview').fadeTo(500, 0.3, function () {
                    $('.image_page .image_preview').css('background-image', 'none');
                }).fadeTo(500, 1);
            });
        }
    });

    /** CUSTOMIZAÇÃO DE PÁGINAS **/
    /**
     * 
     * @param {object} input
     * @param {int} item
     * @returns {html}
     */

    $('.choose_image_slider').click(function (e) {
        e.preventDefault();
        var itemid = $(this).attr('id');
        $('#choose_input' + itemid).click();
    });

    $(".choose_input").on('change', function () {
        var inputid = $(this).attr("id");
        var itemid = inputid.substr(12, 1);
        imagePreviewSlider(this, itemid);
    });

    function imagePreviewSlider(input, item) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#image_preview' + item).attr('src', e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    $('.choose_sticker_slider').click(function (e) {
        e.preventDefault();
        var itemid = $(this).attr('id');
        $('#choose_sticker' + itemid).click();
    });

    $(".choose_sticker").on('change', function () {
        var inputid = $(this).attr("id");
        var itemid = inputid.substr(14, 1);
        stickerPreviewSlider(this, itemid);
    });

    function stickerPreviewSlider(input, item) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#sticker_preview' + item).attr('src', e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    /** ATUALIZAR SLIDER **/
    $('form[name="update_slider"]').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: base_url + "customize/update_slider",
            data: new FormData(this),
            type: 'POST',
            contentType: false,
            processData: false,
            cache: false,
            dataType: 'json',
            beforeSend: function () {
                $('#modal_update_home').modal("show");
            },
            success: function (r) {
                $('#modal_update_home .close_modal').fadeIn("fast");
                if (r.response === 'success') {
                    showModal("Slider Atualizado com sucesso! Confira as modificações na página inicial.", "success", "modal_update_home");
                } else {
                    showModal(r.response, "warning", "modal_update_home");
                }
            },
            error: function (e) {
                $('.debug').html(e.responseText);
                $('#modal_update_home .close_modal').fadeIn("fast");
                console.log(e);
            }
        });
    });

    /** Trocar imagem do banner **/

    $('#choose_banner').click(function (e) {
        e.preventDefault();
        $('#image_banner').click();
    });

    $('#image_banner').change(function () {
        bannerPreview(this);
    });

    function bannerPreview(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('.banner_preview').css('background-image', 'url(' + e.target.result + ')');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    /** ATUALIZAR EXIBIÇÂO DE PRODUTOS E SIDEBAR **/
    $('form[name="products_display"]').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: base_url + "customize/update_display",
            type: 'POST',
            dataType: 'json',
            data: new FormData(this),
            processData: false,
            contentType: false,
            cache: false,
            beforeSend: function () {
                $('#modal_update_home').modal("show");
            },
            success: function (r) {
                $('#modal_update_home .close_modal').fadeIn("fast");
                if (r.response === 'success') {
                    showModal("A <strong>exibição dos produtos</strong> da sua loja e a <strong>barra lateral</strong> foram atualizadas om sucesso.", "success", "modal_update_home");
                } else {
                    showModal(r.response, "warning", "modal_update_home");
                }
            },
            error: function (e) {
                $('.debug').html(e.responseText);
                $('#modal_update_home .close_modal').fadeIn("fast");
                console.log(e);
            }
        });
    });

});
