<?php
/**
 * Account controller for Mobile Money transactions
 */

class MobileMoneyAccountModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        if (!$this->context->customer->isLogged()) {
            Tools::redirect('index.php?controller=authentication');
            return;
        }

        $orders = Order::getCustomerOrders($this->context->customer->id);
        $mobileMoneyOrders = [];

        foreach ($orders as $order) {
            if ($order['module'] === $this->module->name) {
                // Get payment details
                $order_obj = new Order($order['id_order']);
                $payments = $order_obj->getOrderPaymentCollection();
                $payment_details = [];
                
                if ($payments && count($payments)) {
                    foreach ($payments as $payment) {
                        $payment_details[] = [
                            'amount' => $payment->amount,
                            'date' => $payment->date_add,
                            'transaction' => $payment->transaction_id,
                            'state' => $order['order_state']
                        ];
                    }
                }

                $mobileMoneyOrders[] = array_merge($order, ['payments' => $payment_details]);
            }
        }

        $this->context->smarty->assign([
            'mobileMoneyOrders' => $mobileMoneyOrders,
            'moduleDisplayName' => $this->module->displayName
        ]);

        $this->setTemplate('module:mobilemoney/views/templates/front/account.tpl');
    }
}
