<?php
class AdminMobileMoneyPayController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'order';
        $this->className = 'Order';
        $this->lang = false;
        $this->addRowAction('view');
        $this->explicitSelect = true;
        $this->allow_export = true;
        $this->deleted = false;
        $this->context = Context::getContext();

        parent::__construct();

        $this->_select = '
            a.id_order AS id_pdf,
            CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`,
            osl.`name` AS `osname`,
            os.`color`,
            IF((SELECT COUNT(*) FROM '._DB_PREFIX_.'orders WHERE id_customer = a.id_customer) > 1, 0, 1) as new,
            (SELECT payment FROM '._DB_PREFIX_.'order_payment WHERE order_reference = a.reference) as payment
        ';

        $this->_join = '
            LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = a.`id_customer`)
            LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = a.`current_state`)
            LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.(int)$this->context->language->id.')
        ';

        $this->_orderBy = 'id_order';
        $this->_orderWay = 'DESC';

        $this->fields_list = array(
            'id_order' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'reference' => array(
                'title' => $this->l('Reference')
            ),
            'customer' => array(
                'title' => $this->l('Customer'),
                'havingFilter' => true,
            ),
            'total_paid_tax_incl' => array(
                'title' => $this->l('Total'),
                'align' => 'text-right',
                'type' => 'price',
                'currency' => true
            ),
            'payment' => array(
                'title' => $this->l('Payment Method')
            ),
            'osname' => array(
                'title' => $this->l('Status'),
                'color' => 'color'
            ),
            'date_add' => array(
                'title' => $this->l('Date'),
                'type' => 'datetime'
            )
        );
    }

    public function renderView()
    {
        $order = new Order(Tools::getValue('id_order'));
        if (!Validate::isLoadedObject($order)) {
            $this->errors[] = $this->l('The order cannot be found within your database.');
        }

        $this->context->smarty->assign(array(
            'order' => $order,
            'customer' => new Customer($order->id_customer),
            'mobilemoney_waiting_state' => Configuration::get('MOBILEMONEY_WAITING_PAYMENT'),
            'order_states' => OrderState::getOrderStates($this->context->language->id)
        ));

        return parent::renderView();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitUpdateOrderStatus')) {
            $order = new Order((int)Tools::getValue('id_order'));
            if (Validate::isLoadedObject($order)) {
                $new_order_status_id = (int)Tools::getValue('new_order_status');
                
                // Update order status
                $order_state = new OrderState($new_order_status_id);
                if (Validate::isLoadedObject($order_state)) {
                    $current_order_state = $order->getCurrentOrderState();
                    if ($current_order_state->id != $new_order_status_id) {
                        // Create new OrderHistory
                        $history = new OrderHistory();
                        $history->id_order = $order->id;
                        $history->id_employee = (int)$this->context->employee->id;
                        $history->changeIdOrderState($new_order_status_id, $order->id);
                        $history->add();
                        
                        Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.$this->token);
                    } else {
                        $this->errors[] = $this->l('The order has already been assigned this status.');
                    }
                } else {
                    $this->errors[] = $this->l('The new order status is invalid.');
                }
            } else {
                $this->errors[] = $this->l('The order cannot be found within your database.');
            }
        }

        return parent::postProcess();
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
    }
}