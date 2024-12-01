<?php
class MobileMoneyPayValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (!$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $cart = $this->context->cart;

        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        // Check that this payment option is still available
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'mobilemoneypay') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            die($this->trans('This payment method is not available.', [], 'Modules.Mobilemoneypay.Shop'));
        }

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $provider = Tools::getValue('provider');
        if (!in_array($provider, ['airtel', 'mtn'])) {
            Tools::redirect('index.php?controller=order&step=3');
        }

        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
        $this->module->validateOrder(
            (int)$cart->id,
            Configuration::get('MOBILEMONEY_WAITING_PAYMENT'),
            $total,
            $this->module->displayName . ' - ' . ucfirst($provider),
            null,
            array('provider' => $provider),
            (int)$cart->id_currency,
            false,
            $customer->secure_key
        );

        Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
    }
}