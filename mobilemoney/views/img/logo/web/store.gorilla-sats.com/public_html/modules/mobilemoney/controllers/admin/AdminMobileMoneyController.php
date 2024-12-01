<?php

class AdminMobileMoneyController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        $this->table = 'configuration';
        $this->className = 'Configuration';
        $this->identifier = 'id_configuration';
        
        $this->toolbar_title = $this->l('Mobile Money Configuration');
    }

    public function renderForm()
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
                        'required' => true,
                        'desc' => $this->l('Upload the QR code image for MTN Mobile Money payments'),
                        'current_value' => Configuration::get('MOBILEMONEY_QR_MTN')
                    ),
                    array(
                        'type' => 'file',
                        'label' => $this->l('Airtel Money QR Code'),
                        'name' => 'MOBILEMONEY_QR_AIRTEL',
                        'required' => true,
                        'desc' => $this->l('Upload the QR code image for Airtel Money payments'),
                        'current_value' => Configuration::get('MOBILEMONEY_QR_AIRTEL')
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitMobileMoneyModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name
            .'&tab_module='.$this->tab
            .'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'uri' => $this->context->link->getBaseLink() . 'modules/' . $this->name . '/'
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'MOBILEMONEY_QR_MTN' => Tools::getValue('MOBILEMONEY_QR_MTN', Configuration::get('MOBILEMONEY_QR_MTN')),
            'MOBILEMONEY_QR_AIRTEL' => Tools::getValue('MOBILEMONEY_QR_AIRTEL', Configuration::get('MOBILEMONEY_QR_AIRTEL')),
        );
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitMobileMoneyModule')) {
            // Handle MTN QR Upload
            if (isset($_FILES['MOBILEMONEY_QR_MTN']) && !empty($_FILES['MOBILEMONEY_QR_MTN']['name'])) {
                $this->uploadQRCode('MOBILEMONEY_QR_MTN');
            }
            
            // Handle Airtel QR Upload
            if (isset($_FILES['MOBILEMONEY_QR_AIRTEL']) && !empty($_FILES['MOBILEMONEY_QR_AIRTEL']['name'])) {
                $this->uploadQRCode('MOBILEMONEY_QR_AIRTEL');
            }
            
            $this->confirmations[] = $this->l('Settings updated successfully');
            return true;
        }
        
        return parent::postProcess();
    }

    private function uploadQRCode($fieldName)
    {
        if (!isset($_FILES[$fieldName]['tmp_name'])) {
            return false;
        }

        $file = $_FILES[$fieldName];
        $type = Tools::strtolower(Tools::substr(strrchr($file['name'], '.'), 1));
        $imageSize = @getimagesize($file['tmp_name']);
        
        if (!$imageSize || !in_array($type, array('jpg', 'jpeg', 'png'))) {
            $this->errors[] = sprintf($this->l('Image format not recognized for %s, allowed formats are: .jpg, .png'), $fieldName);
            return false;
        }

        $uploadDir = _PS_MODULE_DIR_ . $this->module->name . '/views/img/qr/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = $fieldName . '.' . $type;
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            Configuration::updateValue($fieldName, $fileName);
            return true;
        }

        $this->errors[] = sprintf($this->l('Error uploading %s'), $fieldName);
        return false;
    }
}
