<?php

namespace App\Models\Loja;

use Illuminate\Database\Eloquent\Model;

class Reviews extends Model{
    
    protected $table = "reviews";
    protected $primaryKey = "review_id";
    protected $guarded = ['review_id', 'created_at', 'updated_at'];
    
    private $data;
    private $error;
    private $result;
    
    public function getError() {
        return $this->error;
    }

    public function getResult() {
        return $this->result;
    }  
    
    public function getReviews(int $product_id) {
        return self::join("clientes", "id_cliente", "=", "review_user")
                ->select("clientes.nome_cliente","clientes.sobrenome_cliente","reviews.*")
                ->where("review_product", $product_id)
                ->get()
                ->toArray();
    }
    
    public function getRating(int $product_id) {
        return self::where("review_product", $product_id)
                ->avg("review_rating");
    }
    
}
