<?php
/**
 * Validation controller for Mobile Money module
 */

require_once _PS_MODULE_DIR_ . 'mobilemoney/classes/PaymentValidator.php';

use PrestaShop\Module\MobileMoney\PaymentValidator;

class MobileMoneyValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        try {
            $cart = $this->context->cart;
            
            // Create validator instance
            $validator = new PaymentValidator($this->module);

            // Validate basic payment requirements
            if (!$validator->validatePayment($cart)) {
                $errors = $validator->getErrors();
                $this->errors = array_merge($this->errors, $errors);
                $this->redirectWithNotifications($this->context->link->getPageLink('order', true, null, ['step' => 3]));
                return;
            }

            // Get selected payment provider
            $provider = Tools::getValue('provider', 'mtn');
            if (!in_array($provider, ['mtn', 'airtel'])) {
                $this->errors[] = $this->l('Invalid payment provider selected');
                $this->redirectWithNotifications($this->context->link->getPageLink('order', true, null, ['step' => 3]));
                return;
            }

            // Get customer info
            $customer = new Customer($cart->id_customer);
            if (!Validate::isLoadedObject($customer)) {
                $this->errors[] = $this->l('Could not load customer information');
                $this->redirectWithNotifications($this->context->link->getPageLink('order', true, null, ['step' => 3]));
                return;
            }

            $currency = new Currency($cart->id_currency);
            $total = (float)$cart->getOrderTotal(true, Cart::BOTH);

            // Create the order with our custom state
            $this->module->validateOrder(
                (int)$cart->id,
                Configuration::get('MOBILEMONEY_WAITING_PAYMENT'),
                $total,
                $this->module->displayName . ' (' . strtoupper($provider) . ')',
                null,
                array(
                    'transaction_id' => null,
                    'payment_provider' => $provider
                ),
                (int)$currency->id,
                false,
                $customer->secure_key
            );

            Tools::redirect('index.php?controller=order-confirmation&id_cart='
                .(int)$cart->id
                .'&id_module='.(int)$this->module->id
                .'&id_order='.$this->module->currentOrder
                .'&key='.$customer->secure_key
            );

        } catch (Exception $e) {
            PrestaShopLogger::addLog('Mobile Money - Payment Error: ' . $e->getMessage(), 3);
            $this->errors[] = $this->l('An error occurred during payment validation: ') . $e->getMessage();
            $this->redirectWithNotifications($this->context->link->getPageLink('order', true, null, ['step' => 3]));
        }
    }
}