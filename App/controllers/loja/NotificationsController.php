<?php

namespace App\Controllers\Loja;

use App\Core\Controller;
use PagSeguro\Helpers\Xhr;
use PagSeguro\Services\Transactions\Notification;
use PagSeguro\Configuration\Configure;
use App\Models\Loja\Vendas;

/**
 * Description of NotificationsController
 *
 * @author jucie
 */
class NotificationsController extends Controller {

    public function pagseguro() {
        try {
            if (Xhr::hasPost()):
                $r = Notification::check(Configure::getAccountCredentials());
            
                $ref = $r->getReference();
                $status = $r->getStatus();
                /*
                 * 1 = Aguardando pagamento
                 * 2 = Em análise
                 * 3 = Paga
                 * 4 = Dispónivel para saque
                 * 5 = Em disputa
                 * 6 = Pagamento devolvido ao cliente
                 * 7 = Compra cancelada
                 * 8 = Debitado - Cliente recebeu o dinheiro de volta
                 * 9 = Retenção temporária = Chargeback                * 
                 */
                
                if($status == 3):
                    $vendas = new Vendas;
                    $vendas->setPaid($ref);
                endif;
            endif;
        } catch (Exception $e) {
            
        }
    }

}
