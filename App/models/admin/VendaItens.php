<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

/**
 * Description of VendaItens
 *
 * @author jucie
 */
class VendaItens extends Model{
    
    protected $table = "venda_itens";
    protected $guarded = ['id_vi'];
    public $timestamps = false;
    
}
