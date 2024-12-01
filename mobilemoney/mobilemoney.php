<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Mobilemoney extends PaymentModule
{
    private $html = '';
    private $postErrors = array();

    public function __construct()
    {
        $this->name = 'mobilemoney';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Your Name';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Mobile Money Payment');
        $this->description = $this->l('Accept Mobile Money payments through Airtel Money and MTN Mobile Money');

        if (!Configuration::get('MOBILEMONEY_WAITING_PAYMENT')) {
            $this->createOrderState();
        }
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('paymentOptions')
            || !$this->registerHook('paymentReturn')
            || !Configuration::updateValue('MOBILEMONEY_WAITING_PAYMENT', '')
        ) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        return parent::uninstall() && 
            Configuration::deleteByName('MOBILEMONEY_WAITING_PAYMENT');
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return [];
        }

        // Add module path to Smarty
        $this->context->smarty->assign([
            'module_dir' => $this->_path,
            'amount' => $params['cart']->getOrderTotal()
        ]);

        $airtelOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $airtelOption
            ->setCallToActionText($this->l('Pay with Airtel Money'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array('provider' => 'airtel'), true))
            ->setAdditionalInformation($this->context->smarty->fetch('module:mobilemoney/views/templates/front/payment_form.tpl'))
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/airtel.png'));

        $mtnOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $mtnOption
            ->setCallToActionText($this->l('Pay with MTN Mobile Money'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array('provider' => 'mtn'), true))
            ->setAdditionalInformation($this->context->smarty->fetch('module:mobilemoney/views/templates/front/payment_form.tpl'))
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/mtn.png'));

        return [$airtelOption, $mtnOption];
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        $state = $params['order']->getCurrentState();
        if ($state == Configuration::get('MOBILEMONEY_WAITING_PAYMENT')) {
            $this->smarty->assign([
                'status' => 'ok',
                'shop_name' => $this->context->shop->name,
                'reference' => $params['order']->reference,
                'contact_url' => $this->context->link->getPageLink('contact', true)
            ]);
        }

        return $this->display(__FILE__, 'payment_return.tpl');
    }

    private function createOrderState()
    {
        $orderState = new OrderState();
        $orderState->name = array();
        foreach (Language::getLanguages() as $language) {
            $orderState->name[$language['id_lang']] = 'Awaiting Mobile Money Payment';
        }

        $orderState->send_email = false;
        $orderState->color = '#FF8C00';
        $orderState->hidden = false;
        $orderState->delivery = false;
        $orderState->logable = true;
        $orderState->invoice = false;
        
        if ($orderState->add()) {
            Configuration::updateValue('MOBILEMONEY_WAITING_PAYMENT', $orderState->id);
        }
    }
}