<?php

namespace PrestaShop\Module\MobileMoney;

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Configuration;
use Cart;
use Customer;
use Validate;
use Tools;

class PaymentValidator
{
    private $module;
    private $context;
    private $errors = [];

    public function __construct($module)
    {
        $this->module = $module;
        $this->context = \Context::getContext();
    }

    public function validatePayment($cart)
    {
        // Check if module is active
        if (!$this->module->active) {
            $this->addError('Mobile Money module is not active');
            return false;
        }

        // Check if cart exists and is valid
        if (!Validate::isLoadedObject($cart)) {
            $this->addError('Invalid cart');
            return false;
        }

        // Check if customer exists
        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            $this->addError('Invalid customer');
            return false;
        }

        // Check if addresses are set
        if (!$cart->id_address_delivery || !$cart->id_address_invoice) {
            $this->addError('Delivery or invoice address not set');
            return false;
        }

        // Check if cart has products
        if ($cart->nbProducts() < 1) {
            $this->addError('Cart is empty');
            return false;
        }

        // Verify cart amount
        $cartTotal = (float)$cart->getOrderTotal(true, Cart::BOTH);
        if ($cartTotal <= 0) {
            $this->addError('Invalid cart amount');
            return false;
        }

        return true;
    }

    private function addError($error)
    {
        $this->errors[] = $error;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}