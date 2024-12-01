<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class MobileMoneyPay extends PaymentModule
{
    private $html = '';
    private $postErrors = array();

    public function __construct()
    {
        $this->name = 'mobilemoneypay';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Your Name';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Mobile Money Payment');
        $this->description = $this->l('Accept Mobile Money payments through Airtel Money and MTN Mobile Money');

        // Add custom order state for pending mobile money payments
        if (!Configuration::get('MOBILEMONEY_WAITING_PAYMENT')) {
            $this->createOrderState();
        }
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('paymentOptions')
            || !$this->registerHook('paymentReturn')
            || !$this->registerHook('displayAdminOrder')
            || !Configuration::updateValue('MOBILEMONEY_AIRTEL_NUMBER', '123456')
            || !Configuration::updateValue('MOBILEMONEY_MTN_NUMBER', '123456')
        ) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()
            || !Configuration::deleteByName('MOBILEMONEY_AIRTEL_NUMBER')
            || !Configuration::deleteByName('MOBILEMONEY_MTN_NUMBER')
        ) {
            return false;
        }
        return true;
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        $airtelOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $airtelOption
            ->setModuleName($this->name)
            ->setCallToActionText($this->l('Pay with Airtel Money'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array('provider' => 'airtel'), true))
            ->setAdditionalInformation($this->context->smarty->fetch('module:mobilemoneypay/views/templates/front/payment_form.tpl'));

        $mtnOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $mtnOption
            ->setModuleName($this->name)
            ->setCallToActionText($this->l('Pay with MTN Mobile Money'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array('provider' => 'mtn'), true))
            ->setAdditionalInformation($this->context->smarty->fetch('module:mobilemoneypay/views/templates/front/payment_form.tpl'));

        return [$airtelOption, $mtnOption];
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        $state = $params['order']->getCurrentState();
        if ($state == Configuration::get('MOBILEMONEY_WAITING_PAYMENT')) {
            $this->smarty->assign(array(
                'status' => 'pending',
                'shop_name' => $this->context->shop->name,
                'reference' => $params['order']->reference,
                'contact_url' => $this->context->link->getPageLink('contact', true)
            ));
        }

        return $this->display(__FILE__, 'views/templates/front/payment_return.tpl');
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
            copy(dirname(__FILE__).'/views/img/orderstate.gif', _PS_ORDER_STATE_IMG_DIR_.$orderState->id.'.gif');
        }
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            $airtelNumber = Tools::getValue('MOBILEMONEY_AIRTEL_NUMBER');
            $mtnNumber = Tools::getValue('MOBILEMONEY_MTN_NUMBER');

            if (empty($airtelNumber) || empty($mtnNumber)) {
                $output .= $this->displayError($this->l('Payment numbers are required.'));
            } else {
                Configuration::updateValue('MOBILEMONEY_AIRTEL_NUMBER', $airtelNumber);
                Configuration::updateValue('MOBILEMONEY_MTN_NUMBER', $mtnNumber);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        return $output . $this->displayForm();
    }

    public function displayForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Mobile Money Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Airtel Money Number'),
                        'name' => 'MOBILEMONEY_AIRTEL_NUMBER',
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('MTN Mobile Money Number'),
                        'name' => 'MOBILEMONEY_MTN_NUMBER',
                        'required' => true
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right'
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit' . $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => array(
                'MOBILEMONEY_AIRTEL_NUMBER' => Configuration::get('MOBILEMONEY_AIRTEL_NUMBER'),
                'MOBILEMONEY_MTN_NUMBER' => Configuration::get('MOBILEMONEY_MTN_NUMBER'),
            ),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }
}