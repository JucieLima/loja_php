<?php

use Illuminate\Database\Eloquent\Model;

namespace App\Models\Admin;

/**
 * Description of Vendas
 *
 * @author jucie
 */
class Vendas extends Model{
    
    protected $table = "vendas";
    protected $primaryKey = "id_venda";
    protected $guarded = ['id_venda'];

    const CREATED_AT = 'registro_usuario';    
    
}
