<?php
/**
 * Payment validation handler for Mobile Money module
 */

namespace PrestaShop\Module\MobileMoney;

use Cart;
use Currency;
use Customer;
use Order;
use PrestaShopException;
use Tools;
use Validate;

class PaymentValidator
{
    use ErrorHandler;

    private $module;
    private $context;

    public function __construct($module)
    {
        $this->module = $module;
        $this->context = \Context::getContext();
    }

    /**
     * Validate payment before processing
     *
     * @param Cart $cart
     * @return bool
     */
    public function validatePayment($cart)
    {
        try {
            // Check if module is active
            if (!$this->module->active) {
                throw new PrestaShopException('Mobile Money module is not active');
            }

            // Check if cart exists and is valid
            if (!Validate::isLoadedObject($cart)) {
                throw new PrestaShopException('Invalid cart');
            }

            // Check if customer exists
            $customer = new Customer($cart->id_customer);
            if (!Validate::isLoadedObject($customer)) {
                throw new PrestaShopException('Invalid customer');
            }

            // Check currency
            if (!$this->validateCurrency($cart)) {
                throw new PrestaShopException('Currency is not supported');
            }

            // Check if addresses are set
            if (!$cart->id_address_delivery || !$cart->id_address_invoice) {
                throw new PrestaShopException('Delivery or invoice address not set');
            }

            // Check if cart has products
            if ($cart->nbProducts() < 1) {
                throw new PrestaShopException('Cart is empty');
            }

            // Verify cart amount
            $cartTotal = (float)$cart->getOrderTotal(true, Cart::BOTH);
            if ($cartTotal <= 0) {
                throw new PrestaShopException('Invalid cart amount');
            }

            return true;

        } catch (PrestaShopException $e) {
            $this->addError($e->getMessage());
            return false;
        }
    }

    /**
     * Validate currency
     *
     * @param Cart $cart
     * @return bool
     */
    private function validateCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->module->getCurrency($cart->id_currency);

        if (empty($currencies_module)) {
            return false;
        }

        foreach ($currencies_module as $currency_module) {
            if ($currency_order->id == $currency_module['id_currency']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify QR code configuration
     *
     * @param string $provider
     * @return bool
     */
    public function validateQRCode($provider)
    {
        try {
            $configKey = $provider === 'mtn' ? 
                'MOBILEMONEY_QR_MTN' : 
                'MOBILEMONEY_QR_AIRTEL';

            $qrCode = \Configuration::get($configKey);
            
            if (empty($qrCode)) {
                throw new PrestaShopException(
                    sprintf('QR code for %s is not configured', strtoupper($provider))
                );
            }

            $qrPath = _PS_MODULE_DIR_ . 'mobilemoney/views/img/qr/' . $qrCode;
            if (!file_exists($qrPath)) {
                throw new PrestaShopException(
                    sprintf('QR code file for %s not found', strtoupper($provider))
                );
            }

            return true;

        } catch (PrestaShopException $e) {
            $this->addError($e->getMessage());
            return false;
        }
    }

    /**
     * Verify order creation parameters
     *
     * @param array $params
     * @return bool
     */
    public function validateOrderCreation($params)
    {
        try {
            $requiredParams = ['cart_id', 'order_status', 'amount'];
            foreach ($requiredParams as $param) {
                if (!isset($params[$param])) {
                    throw new PrestaShopException(
                        sprintf('Missing required parameter: %s', $param)
                    );
                }
            }

            $cart = new Cart($params['cart_id']);
            if (!Validate::isLoadedObject($cart)) {
                throw new PrestaShopException('Invalid cart ID');
            }

            if ($params['amount'] <= 0) {
                throw new PrestaShopException('Invalid order amount');
            }

            return true;

        } catch (PrestaShopException $e) {
            $this->addError($e->getMessage());
            return false;
        }
    }

    /**
     * Validate payment confirmation
     *
     * @param Order $order
     * @return bool
     */
    public function validatePaymentConfirmation($order)
    {
        try {
            if (!Validate::isLoadedObject($order)) {
                throw new PrestaShopException('Invalid order');
            }

            if ($order->module !== $this->module->name) {
                throw new PrestaShopException('Order not from Mobile Money module');
            }

            $currentState = $order->getCurrentState();
            if ($currentState != \Configuration::get('MOBILEMONEY_OS_PENDING')) {
                throw new PrestaShopException('Order is not in pending state');
            }

            return true;

        } catch (PrestaShopException $e) {
            $this->addError($e->getMessage());
            return false;
        }
    }
}