<?php
/**
 * Mobile Money module exception class
 */

namespace PrestaShop\Module\MobileMoney\Exception;

class MobileMoneyException extends \PrestaShopException
{
    public function __construct($message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}