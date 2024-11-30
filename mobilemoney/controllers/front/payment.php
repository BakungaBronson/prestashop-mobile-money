<?php
/**
 * Payment controller for Mobile Money module
 */

class MobileMoneyPaymentModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;
        if (!$this->module->active || !$cart->id_customer || !$cart->id_address_delivery || !$cart->id_address_invoice) {
            Tools::redirect('index.php?controller=order&step=1');
            return;
        }

        // Load QR code paths
        $mtnQr = Configuration::get('MOBILEMONEY_QR_MTN');
        $airtelQr = Configuration::get('MOBILEMONEY_QR_AIRTEL');

        $this->context->smarty->assign([
            'mtn_qr' => $mtnQr ? _MODULE_DIR_ . 'mobilemoney/views/img/qr/' . $mtnQr : false,
            'airtel_qr' => $airtelQr ? _MODULE_DIR_ . 'mobilemoney/views/img/qr/' . $airtelQr : false,
            'amount' => $cart->getOrderTotal(),
            'validation_url' => $this->context->link->getModuleLink(
                $this->module->name,
                'validation',
                [],
                true
            ),
        ]);

        $this->setTemplate('module:mobilemoney/views/templates/front/payment.tpl');
    }
}
