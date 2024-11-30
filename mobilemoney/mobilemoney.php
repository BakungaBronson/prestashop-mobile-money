<?php
/**
 * Mobile Money Payment Module for PrestaShop
 * Integrates MTN Mobile Money and Airtel Money payment options
 *
 * @author    YourName
 * @copyright 2024
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class MobileMoney extends PaymentModule
{
    const CONFIG_MM_OS_PENDING = 'MOBILEMONEY_OS_PENDING';
    const CONFIG_QR_MTN = 'MOBILEMONEY_QR_MTN';
    const CONFIG_QR_AIRTEL = 'MOBILEMONEY_QR_AIRTEL';
    const MODULE_ADMIN_CONTROLLER = 'AdminMobileMoney';

    protected $hooks = [
        'actionPaymentCCAdd',
        'actionObjectShopAddAfter',
        'paymentOptions',
        'displayAdminOrderLeft',
        'displayAdminOrderMainBottom',
        'displayCustomerAccount',
        'displayOrderConfirmation',
        'displayOrderDetail',
        'displayPaymentByBinaries',
        'displayPaymentReturn',
        'displayPDFInvoice',
    ];

    public function __construct()
    {
        $this->name = 'mobilemoney';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'YourName';
        $this->need_instance = 1;
        $this->bootstrap = true;
        
        // Add support for currencies
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_,
        ];

        $this->controllers = [
            'account',
            'cancel',
            'validation',
        ];
        
        parent::__construct();

        $this->displayName = $this->l('Mobile Money Payment');
        $this->description = $this->l('Accept payments via MTN Mobile Money and Airtel Money');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
    }

    public function install()
    {
        // Create directory for QR codes if it doesn't exist
        $qr_dir = _PS_MODULE_DIR_ . $this->name . '/views/img/qr/';
        if (!file_exists($qr_dir)) {
            mkdir($qr_dir, 0777, true);
        }

        // Create directory for order state icons
        $orderstate_dir = _PS_MODULE_DIR_ . $this->name . '/views/img/orderstate/';
        if (!file_exists($orderstate_dir)) {
            mkdir($orderstate_dir, 0777, true);
        }

        return parent::install() &&
            $this->registerHook($this->hooks) &&
            $this->installOrderState() &&
            $this->installConfiguration() &&
            $this->installTabs();
    }

    public function uninstall()
    {
        return parent::uninstall() &&
            $this->deleteOrderState() &&
            $this->uninstallConfiguration() &&
            $this->uninstallTabs();
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active || !$this->checkCurrency($params['cart'])) {
            return [];
        }

        $payment_options = [];

        $option = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $option->setModuleName($this->name)
            ->setCallToActionText($this->l('Pay with Mobile Money'))
            ->setAction($this->context->link->getModuleLink($this->name, 'payment', [], true))
            ->setAdditionalInformation($this->context->smarty->fetch('module:mobilemoney/views/templates/hook/payment.tpl'));

        $payment_options[] = $option;

        return $payment_options;
    }

    public function hookActionPaymentCCAdd($params)
    {
        if (empty($params['paymentCC'])) {
            return;
        }

        $orderPayment = $params['paymentCC'];
        if (!Validate::isLoadedObject($orderPayment) || empty($orderPayment->order_reference)) {
            return;
        }

        $orderCollection = Order::getByReference($orderPayment->order_reference);
        foreach ($orderCollection as $order) {
            if ($this->name !== $order->module) {
                return;
            }
        }

        // Store mobile money provider type
        $provider = Tools::getValue('payment_provider');
        if ($provider && in_array($provider, ['mtn', 'airtel'])) {
            $orderPayment->payment_method = 'Mobile Money (' . strtoupper($provider) . ')';
            $orderPayment->save();
        }
    }

    public function hookActionObjectShopAddAfter($params)
    {
        if (empty($params['object'])) {
            return;
        }

        $shop = $params['object'];
        if (!Validate::isLoadedObject($shop)) {
            return;
        }

        $this->addCheckboxCarrierRestrictionsForModule([(int)$shop->id]);
        $this->addCheckboxCountryRestrictionsForModule([(int)$shop->id]);
        $this->addCheckboxCurrencyRestrictionsForModule([(int)$shop->id]);
    }

    public function hookDisplayCustomerAccount($params)
    {
        $this->context->smarty->assign([
            'moduleDisplayName' => $this->displayName,
            'moduleLogoSrc' => $this->getPathUri() . 'views/img/logo.png',
            'transactionsLink' => $this->context->link->getModuleLink($this->name, 'account')
        ]);

        return $this->context->smarty->fetch('module:mobilemoney/views/templates/hook/customer_account.tpl');
    }

    public function hookDisplayOrderDetail($params)
    {
        if (empty($params['order']) || $params['order']->module !== $this->name) {
            return '';
        }

        $order = $params['order'];
        $payments = $order->getOrderPaymentCollection();
        $transaction = '';
        
        if ($payments && count($payments)) {
            $payment = $payments->getFirst();
            $transaction = $payment->transaction_id;
        }

        $this->context->smarty->assign([
            'transaction' => $transaction,
            'moduleDisplayName' => $this->displayName
        ]);

        return $this->context->smarty->fetch('module:mobilemoney/views/templates/hook/order_detail.tpl');
    }

    public function hookDisplayPDFInvoice($params)
    {
        if (empty($params['object'])) {
            return '';
        }

        $orderInvoice = $params['object'];
        $order = $orderInvoice->getOrder();

        if (!Validate::isLoadedObject($order) || $order->module !== $this->name) {
            return '';
        }

        $payments = $order->getOrderPaymentCollection();
        $transaction = '';
        
        if ($payments && count($payments)) {
            $payment = $payments->getFirst();
            $transaction = $payment->transaction_id;
        }

        $this->context->smarty->assign([
            'transaction' => $transaction,
            'moduleDisplayName' => $this->displayName
        ]);

        return $this->context->smarty->fetch('module:mobilemoney/views/templates/hook/pdf_invoice.tpl');
    }

    private function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

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

    private function installConfiguration()
    {
        return Configuration::updateValue(self::CONFIG_QR_MTN, '') &&
               Configuration::updateValue(self::CONFIG_QR_AIRTEL, '');
    }

    private function uninstallConfiguration()
    {
        return Configuration::deleteByName(self::CONFIG_QR_MTN) &&
               Configuration::deleteByName(self::CONFIG_QR_AIRTEL);
    }

    private function installOrderState()
    {
        // Create new order state for pending mobile money payment
        return $this->createOrderState(
            self::CONFIG_MM_OS_PENDING,
            [
                'en' => 'Awaiting Mobile Money payment',
                'fr' => 'En attente du paiement Mobile Money'
            ],
            '#FF8C00', // Orange color
            false,     // logable
            false,     // paid
            false,     // invoice
            false,     // shipped
            false,     // delivery
            false,     // pdf_delivery
            false,     // pdf_invoice
            true,      // send_email
            'mobile_money_pending'
        );
    }

    private function createOrderState(
        $configKey,
        array $names,
        $color,
        $logable = false,
        $paid = false,
        $invoice = false,
        $shipped = false,
        $delivery = false,
        $pdf_delivery = false,
        $pdf_invoice = false,
        $send_email = false,
        $template = ''
    ) {
        $orderState = new OrderState();
        $orderState->module_name = $this->name;
        
        foreach (Language::getLanguages() as $language) {
            $iso = $language['iso_code'];
            $orderState->name[$language['id_lang']] = isset($names[$iso]) ? $names[$iso] : $names['en'];
        }

        $orderState->color = $color;
        $orderState->logable = $logable;
        $orderState->paid = $paid;
        $orderState->invoice = $invoice;
        $orderState->shipped = $shipped;
        $orderState->delivery = $delivery;
        $orderState->pdf_delivery = $pdf_delivery;
        $orderState->pdf_invoice = $pdf_invoice;
        $orderState->send_email = $send_email;
        $orderState->template = $template;

        if (!$orderState->add()) {
            return false;
        }

        // Create the order state icon
        $source = $this->getLocalPath() . 'views/img/orderstate/' . $configKey . '.gif';
        $destination = _PS_ORDER_STATE_IMG_DIR_ . $orderState->id . '.gif';
        
        if (!copy($source, $destination)) {
            $orderState->delete();
            return false;
        }

        return Configuration::updateValue($configKey, (int)$orderState->id);
    }

    private function deleteOrderState()
    {
        $states = [self::CONFIG_MM_OS_PENDING];
        $result = true;

        foreach ($states as $state) {
            $order_state_id = Configuration::get($state);
            if ($order_state_id) {
                $order_state = new OrderState($order_state_id);
                if (Validate::isLoadedObject($order_state)) {
                    $result &= $order_state->delete();
                }
                $result &= Configuration::deleteByName($state);
            }
        }

        return $result;
    }

    private function installTabs()
    {
        if (Tab::getIdFromClassName(self::MODULE_ADMIN_CONTROLLER)) {
            return true;
        }

        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = self::MODULE_ADMIN_CONTROLLER;
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->displayName;
        }
        $tab->id_parent = -1;
        $tab->module = $this->name;

        return $tab->add();
    }

    private function uninstallTabs()
    {
        $id_tab = Tab::getIdFromClassName(self::MODULE_ADMIN_CONTROLLER);
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }
        return true;
    }

    private function addCheckboxCarrierRestrictionsForModule($shop_list)
    {
        if (!$shop_list) {
            return false;
        }

        $carriers = Carrier::getCarriers($this->context->language->id, false, false, false, null, Carrier::ALL_CARRIERS);
        $carrier_list = array_column($carriers, 'id_reference');
        
        foreach ($shop_list as $id_shop) {
            foreach ($carrier_list as $id_carrier) {
                Db::getInstance()->insert(
                    'module_carrier',
                    array(
                        'id_module' => (int)$this->id,
                        'id_shop' => (int)$id_shop,
                        'id_reference' => (int)$id_carrier,
                    ),
                    false,
                    true,
                    Db::INSERT_IGNORE
                );
            }
        }
        return true;
    }

    private function addCheckboxCountryRestrictionsForModule($shop_list)
    {
        if (!$shop_list) {
            return false;
        }

        $countries = Country::getCountries($this->context->language->id, true);
        foreach ($shop_list as $id_shop) {
            foreach ($countries as $country) {
                Db::getInstance()->insert(
                    'module_country',
                    array(
                        'id_module' => (int)$this->id,
                        'id_shop' => (int)$id_shop,
                        'id_country' => (int)$country['id_country'],
                    ),
                    false,
                    true,
                    Db::INSERT_IGNORE
                );
            }
        }
        return true;
    }

    private function addCheckboxCurrencyRestrictionsForModule($shop_list)
    {
        if (!$shop_list) {
            return false;
        }

        $currencies = Currency::getCurrencies();
        foreach ($shop_list as $id_shop) {
            foreach ($currencies as $currency) {
                Db::getInstance()->insert(
                    'module_currency',
                    array(
                        'id_module' => (int)$this->id,
                        'id_shop' => (int)$id_shop,
                        'id_currency' => (int)$currency['id_currency'],
                    ),
                    false,
                    true,
                    Db::INSERT_IGNORE
                );
            }
        }
        return true;
    }
}