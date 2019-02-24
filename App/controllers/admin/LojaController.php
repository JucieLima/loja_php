<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Admin\Loja;
use App\Models\Admin\Estado;
use App\Models\Admin\Seo;

/**
 * Description of LojaController
 *
 * @author jucie
 */
class LojaController extends Controller {

    public function __construct() {
        if (!isset($_SESSION['userlogin'])):
            header('Location: ' . BASE_URL . 'admin/login');
        elseif ($_SESSION['userlogin']['permissao_usuario'] > 1):
            header('Location: ' . BASE_URL . 'forbidden');
        endif;
    }

    public function index() {
        $estados = new Estado;
        $loja = new Loja;
        $this->viewData['loja'] = $loja->find(1)->toArray();
        $this->viewData['estados'] = $estados->all()->toArray();
        $this->getView('admin/loja/configuracoes', 'admin/' . TEMPLATE);
    }

    public function update() {
        $dados = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $loja = new Loja;
        $update = $loja->atualizar($dados);
        if ($update):
            $retorno['response'] = 'success';
            $retorno['produto'] = $loja->getResult();
        else:
            $retorno['response'] = $loja->getError();
        endif;
        echo json_encode($retorno);
    }

    public function seo() {
        $seo = new Seo;
        $this->viewData = $seo->all()->toArray();
        $this->getView('admin/loja/seo', 'admin/' . TEMPLATE);
    }

    public function edit_page($id) {
        $page = new Seo;
        $this->viewData = $page->find($id)->toArray();
        $this->getView('admin/loja/edit_page', 'admin/' . TEMPLATE);
    }

    public function update_page() {
        $dados = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $seo = new Seo;
        $update = $seo->updatePage($dados);
        if ($update):
            $retorno['response'] = 'success';
            $retorno['result'] = $seo->getResult();
        else:
            $retorno['response'] = $seo->getError();
        endif;
        echo json_encode($retorno);
    }
    
    public function delete_image() {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $seo = new Seo;
        $delete = $seo->deleteImagePage($id);
    }
}
