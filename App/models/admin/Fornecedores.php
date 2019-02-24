<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\CpfCnpj;
use App\Models\Admin\Produtos;

/**
 * Description of Fornecedores
 *
 * @author jucie
 */
class Fornecedores extends Model {

    protected $table = "fornecedores";
    protected $primaryKey = "id_fornecedor";
    public $timestamps = false;
    protected $fillable = [
        'rasao_social_fornecedor',
        'nome_fantasia_fornecedor',
        'cpf_cnpj',
        'cep_fornecedor',
        'logradouro_fornecedor',
        'numero_fornecedor',
        'complemento_fornecedor',
        'bairro_fornecedor',
        'cidade_fornecedor',
        'estado_fornecedor',
        'pais_fornecedor',
        'observacoes_fornecedor',
        'ativo_fornecedor',
        'telefone_fornecedor'
    ];
    private $dados;
    private $result;
    private $error;

    public function getResult() {
        return $this->result;
    }

    public function getError() {
        return $this->error;
    }

    public function salvar(array $dados) {
        $this->dados = $dados;
        if ($this->validarDados() && $this->checkdados()):
            $save = self::create($this->dados);
            if ($save):
                $this->result = $save->toArray();
                return true;
            else:
                $this->error = 'error';
            endif;
        endif;
    }

    public function utualizar(array $dados) {
        $this->dados = $dados;
        if ($this->validarDados() && $this->checkUpdatedados()):
            if ($this->exeUpdate()):
                return true;
            endif;
        endif;
    }

    public function deleteFornecedor(int $id) {
        if (self::find($id)):
            $this->dados = $id;
            if ($this->checkProducts()):
                return $this->exeDelete();
            endif;
        else:
            $this->error = 'Fornecedor não encontrado!';
        endif;
    }

    public function getFornecedor($id) {
        $find = self::find($id);
        if ($find):
            return $find->toArray();
        endif;
    }

    public function getAll($limit, $offset) {
        return self::limit($limit)->offset($offset)->orderBy('id_fornecedor', 'ASC')->get()->toArray();
    }

    private function validarDados() {
        $cpfCnpj = new CpfCnpj();
        $complemento_fornecedor = $this->dados['complemento_fornecedor'];
        $observacoes_fornecedor = $this->dados['observacoes_fornecedor'];
        unset($this->dados['complemento_fornecedor'], $this->dados['observacoes_fornecedor']);

        if (in_array('', $this->dados)):
            $this->error = 'Apenas os campos observação e complemento não são obrigatórios. Preencha todos os demais campos!';
            return false;
        elseif (!$cpfCnpj->validar($this->dados['cpf_cnpj'])):
            $this->error = $cpfCnpj->getError();
            return false;
        endif;

        $this->dados['cpf_cnpj'] = preg_replace('/[^0-9]/is', '', $this->dados['cpf_cnpj']);

        $this->dados['complemento_fornecedor'] = $complemento_fornecedor;
        $this->dados['observacoes_fornecedor'] = $observacoes_fornecedor;

        return true;
    }

    private function checkDados() {
        if (self::Where('rasao_social_fornecedor', $this->dados['rasao_social_fornecedor'])->get()->toArray()):
            $this->error = 'Já existe um fornecedor cadastrado com esta <strong>Razão Social</strong>.';
        elseif (self::where('nome_fantasia_fornecedor', $this->dados['nome_fantasia_fornecedor'])->get()->toArray()):
            $this->error = 'Já existe um fornecedor cadastrado com este <strong>Nome Fantasia</strong>.';
        elseif (self::Where('cpf_cnpj', $this->dados['cpf_cnpj'])->get()->toArray()):
            $this->error = 'O <strong>CPF</strong> ou <strong>CNPJ</strong> já está sendo utilisado por outro fornecedor.';
        else:
            return true;
        endif;
    }

    private function checkUpdateDados() {
        if (self::Where('id_fornecedor', '<>', $this->dados['id_fornecedor'])->Where('rasao_social_fornecedor', '=', $this->dados['rasao_social_fornecedor'])->get()->toArray()):
            $this->error = 'Já existe um fornecedor cadastrado com esta <strong>Razão Social</strong>.';
        elseif (self::Where('id_fornecedor', '<>', $this->dados['id_fornecedor'])->where('nome_fantasia_fornecedor', $this->dados['nome_fantasia_fornecedor'])->get()->toArray()):
            $this->error = 'Já existe um fornecedor cadastrado com este <strong>Nome Fantasia</strong>.';
        elseif (self::Where('id_fornecedor', '<>', $this->dados['id_fornecedor'])->Where('cpf_cnpj', $this->dados['cpf_cnpj'])->get()->toArray()):
            $this->error = 'O <strong>CPF</strong> ou <strong>CNPJ</strong> já está sendo utilisado por outro fornecedor.';
        else:
            return true;
        endif;
    }

    private function exeUpdate() {
        $update = self::find($this->dados['id_fornecedor']);
        $update->update($this->dados);
        if ($update):
            $this->result = $update->toArray();
            return true;
        else:
            $this->error = 'Erro ao tentar atualizar dados no banco de dados!';
        endif;
    }

    private function checkProducts() {
        $produto = new Produtos;
        $check = $produto::where('fornecedor_produto', $this->dados)->get()->toArray();
        if (!$check):
            return true;
        else:
            $this->error = 'Você não pode excluir um fornecedor que possui produtos cadastrados. Remova primeiro os produtos do fornecedor!';
        endif;
    }

    private function exeDelete() {
        $delete = self::find($this->dados);
        if ($delete):
            $delete->delete($this->dados);
            $this->result = 'success';
            return true;
        else:
            $this->error = 'Erro ao tentar excluir fornecedor no banco de dados.';
        endif;
    }

}
