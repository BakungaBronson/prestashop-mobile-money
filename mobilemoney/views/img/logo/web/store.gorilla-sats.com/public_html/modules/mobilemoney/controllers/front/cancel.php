<?php
/**
 * Cancel controller for Mobile Money module
 */

class MobileMoneyCancel extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $orderId = Tools::getValue('id_order');
        $order = new Order((int)$orderId);

        // Verify the order belongs to the current customer
        if (!Validate::isLoadedObject($order) || 
            $order->id_customer != $this->context->customer->id || 
            $order->module != $this->module->name) {
            Tools::redirect('index.php?controller=order&step=1');
            return;
        }

        // Update order state to cancelled
        $history = new OrderHistory();
        $history->id_order = $order->id;
        $history->changeIdOrderState(
            Configuration::get('PS_OS_CANCELED'),
            $order
        );
        $history->addWithemail();

        Tools::redirect($this->context->link->getPageLink(
            'order',
            true,
            null,
            array(
                'id_cart' => $order->id_cart,
                'id_module' => (int)$this->module->id,
                'id_order' => $order->id,
                'key' => $order->secure_key
            )
        ));
    }
}
