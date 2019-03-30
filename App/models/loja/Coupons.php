<?php

namespace App\Models\Loja;

use Illuminate\Database\Eloquent\Model;

class Coupons extends Model {

    protected $table = 'coupons';
    protected $primaryKey = 'coupon_id';
    protected $guarded = ['coupon_id', 'created_at', 'updated_at'];
    private $code;
    private $result;

    public function getDiscount($code) {
        $this->code = $code;
        $this->readCode();
        return $this->result;
    }

    private function readCode() {
        $timestamp = date("Y-m-d H:i:s");
        $read = self::where("coupon_code", $this->code)
                ->where("coupon_start", "<=", $timestamp)
                ->where("coupon_end", ">=", $timestamp)
                ->get()
                ->toArray();
        if ($read):
            $this->result = $read[0];
        endif;
        $this->setResult();
    }

    private function setResult() {
        if ($this->result):
            $this->result['error'] = false;;
            $this->result['discount_text'] = ($this->result['coupon_discount'] * 100) . '% OFF';
            $this->result['discount_value'] = $this->result['coupon_discount'];
            $_SESSION['coupon'] = $this->result;
        else:
            $this->result['error'] = 'Código inválido!';
        endif;
    }

}
