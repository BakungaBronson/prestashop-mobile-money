<?php
/**
 * Validation controller for Mobile Money module
 */

use PrestaShop\Module\MobileMoney\Exception\MobileMoneyException;
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
                throw new MobileMoneyException('Payment validation failed: ' . implode(', ', $validator->getErrors()));
            }

            // Get selected payment provider
            $provider = Tools::getValue('payment_provider', 'mtn');
            if (!in_array($provider, ['mtn', 'airtel'])) {
                throw new MobileMoneyException('Invalid payment provider selected');
            }

            // Validate QR code configuration
            if (!$validator->validateQRCode($provider)) {
                throw new MobileMoneyException('QR code validation failed: ' . implode(', ', $validator->getErrors()));
            }

            // Get customer and cart info
            $customer = new Customer($cart->id_customer);
            if (!Validate::isLoadedObject($customer)) {
                throw new MobileMoneyException('Could not load customer information');
            }

            $currency = new Currency($cart->id_currency);
            if (!Validate::isLoadedObject($currency)) {
                throw new MobileMoneyException('Could not load currency information');
            }

            $total = (float)$cart->getOrderTotal(true, Cart::BOTH);

            // Validate order creation parameters
            $orderParams = [
                'cart_id' => (int)$cart->id,
                'order_status' => Configuration::get('MOBILEMONEY_OS_PENDING'),
                'amount' => $total
            ];

            if (!$validator->validateOrderCreation($orderParams)) {
                throw new MobileMoneyException('Order creation validation failed: ' . implode(', ', $validator->getErrors()));
            }

            // Create the order
            $this->module->validateOrder(
                (int)$cart->id,
                Configuration::get('MOBILEMONEY_OS_PENDING'),
                $total,
                $this->module->displayName . ' (' . strtoupper($provider) . ')',
                null,
                [
                    'transaction_id' => null,
                    'payment_provider' => $provider
                ],
                (int)$currency->id,
                false,
                $customer->secure_key
            );

            // Log successful payment initiation
            $this->module->getLogger()->info('Payment initiated', [
                'cart_id' => $cart->id,
                'customer_id' => $customer->id,
                'amount' => $total,
                'provider' => $provider
            ]);

            // Redirect to order confirmation page
            Tools::redirect($this->context->link->getPageLink(
                'order-confirmation',
                true,
                null,
                [
                    'id_cart' => (int)$cart->id,
                    'id_module' => (int)$this->module->id,
                    'id_order' => $this->module->currentOrder,
                    'key' => $customer->secure_key
                ]
            ));

        } catch (MobileMoneyException $e) {
            // Log the error
            $this->module->getLogger()->error($e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // Add error message for display
            $this->errors[] = $this->module->l('An error occurred during payment validation: ') . $e->getMessage();
            
            // Redirect back to checkout
            $this->redirectWithNotifications($this->context->link->getPageLink('order', true, null, [
                'step' => 1
            ]));
        }
    }
}