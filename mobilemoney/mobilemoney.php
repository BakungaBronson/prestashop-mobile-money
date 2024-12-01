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
            || !Configuration::updateValue('MOBILEMONEY_QR_MTN', '')
            || !Configuration::updateValue('MOBILEMONEY_QR_AIRTEL', '')
        ) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        return parent::uninstall() && 
            Configuration::deleteByName('MOBILEMONEY_WAITING_PAYMENT') &&
            Configuration::deleteByName('MOBILEMONEY_QR_MTN') &&
            Configuration::deleteByName('MOBILEMONEY_QR_AIRTEL');
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return [];
        }

        // Add module path and amount to Smarty
        $this->context->smarty->assign([
            'module_dir' => $this->_path,
            'amount' => $params['cart']->getOrderTotal(),
            'mtn_qr' => Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/qr/'.Configuration::get('MOBILEMONEY_QR_MTN')),
            'airtel_qr' => Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/qr/'.Configuration::get('MOBILEMONEY_QR_AIRTEL')),
        ]);

        $airtelTemplate = $this->context->smarty->createTemplate('module:mobilemoney/views/templates/front/payment_form.tpl');
        $airtelTemplate->assign('provider', 'airtel');
        
        $mtnTemplate = $this->context->smarty->createTemplate('module:mobilemoney/views/templates/front/payment_form.tpl');
        $mtnTemplate->assign('provider', 'mtn');

        $airtelOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $airtelOption
            ->setCallToActionText($this->l('Pay with Airtel Money'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array('provider' => 'airtel'), true))
            ->setAdditionalInformation($airtelTemplate->fetch())
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/airtel.png'));

        $mtnOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $mtnOption
            ->setCallToActionText($this->l('Pay with MTN Mobile Money'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array('provider' => 'mtn'), true))
            ->setAdditionalInformation($mtnTemplate->fetch())
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/mtn.png'));

        return [$airtelOption, $mtnOption];
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        $order = isset($params['order']) ? $params['order'] : null;
        
        if (!$order || $order->module !== $this->name) {
            return;
        }

        $pendingStatus = Configuration::get('MOBILEMONEY_WAITING_PAYMENT');
        $currentState = $order->getCurrentState();

        $this->smarty->assign([
            'status' => ($currentState == $pendingStatus) ? 'ok' : 'failed',
            'shop_name' => $this->context->shop->name,
            'reference' => $order->reference,
            'contact_url' => $this->context->link->getPageLink('contact', true)
        ]);

        return $this->display(__FILE__, 'views/templates/hook/payment_return.tpl');
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
        $orderState->paid = false;
        
        if ($orderState->add()) {
            Configuration::updateValue('MOBILEMONEY_WAITING_PAYMENT', $orderState->id);
            // Copy template icon
            $source = _PS_MODULE_DIR_.$this->name.'/views/img/orderstate.gif';
            $destination = _PS_ORDER_STATE_IMG_DIR_.$orderState->id.'.gif';
            copy($source, $destination);
        }
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitMobilemoney')) {
            // Handle MTN QR Upload
            if (isset($_FILES['MOBILEMONEY_QR_MTN']) && !empty($_FILES['MOBILEMONEY_QR_MTN']['name'])) {
                if ($this->handleQRUpload('MOBILEMONEY_QR_MTN')) {
                    $output .= $this->displayConfirmation($this->l('MTN QR code updated successfully'));
                } else {
                    $output .= $this->displayError($this->l('Error uploading MTN QR code'));
                }
            }
            
            // Handle Airtel QR Upload
            if (isset($_FILES['MOBILEMONEY_QR_AIRTEL']) && !empty($_FILES['MOBILEMONEY_QR_AIRTEL']['name'])) {
                if ($this->handleQRUpload('MOBILEMONEY_QR_AIRTEL')) {
                    $output .= $this->displayConfirmation($this->l('Airtel QR code updated successfully'));
                } else {
                    $output .= $this->displayError($this->l('Error uploading Airtel QR code'));
                }
            }
        }

        return $output . $this->renderForm();
    }

    private function handleQRUpload($fieldName)
    {
        $uploadDir = _PS_MODULE_DIR_.$this->name.'/views/img/qr/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $file = $_FILES[$fieldName];
        $fileName = $fieldName . '.png';
        $filePath = $uploadDir . $fileName;

        // Verify it's an image
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false || !in_array($imageInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG])) {
            return false;
        }

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            Configuration::updateValue($fieldName, $fileName);
            return true;
        }
        return false;
    }

    private function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Mobile Money Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'file',
                        'label' => $this->l('MTN Mobile Money QR Code'),
                        'name' => 'MOBILEMONEY_QR_MTN',
                        'desc' => $this->l('Upload QR code for MTN Mobile Money (PNG or JPG)')
                    ),
                    array(
                        'type' => 'file',
                        'label' => $this->l('Airtel Money QR Code'),
                        'name' => 'MOBILEMONEY_QR_AIRTEL',
                        'desc' => $this->l('Upload QR code for Airtel Money (PNG or JPG)')
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
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitMobilemoney';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => array(
                'MOBILEMONEY_QR_MTN' => Configuration::get('MOBILEMONEY_QR_MTN'),
                'MOBILEMONEY_QR_AIRTEL' => Configuration::get('MOBILEMONEY_QR_AIRTEL'),
            ),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }
}