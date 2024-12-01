<?php
/**
 * Logger class for Mobile Money module
 */

namespace PrestaShop\Module\MobileMoney;

class Logger
{
    const ERROR = 3;
    const WARNING = 2;
    const INFO = 1;
    const DEBUG = 0;

    private $module;
    private $debug;

    public function __construct($module, $debug = false)
    {
        $this->module = $module;
        $this->debug = $debug;
    }

    /**
     * Log a debug message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug($message, array $context = [])
    {
        if ($this->debug) {
            $this->log($message, self::DEBUG, $context);
        }
    }

    /**
     * Log an info message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info($message, array $context = [])
    {
        $this->log($message, self::INFO, $context);
    }

    /**
     * Log a warning message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function warning($message, array $context = [])
    {
        $this->log($message, self::WARNING, $context);
    }

    /**
     * Log an error message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error($message, array $context = [])
    {
        $this->log($message, self::ERROR, $context);
    }

    /**
     * Write log message
     *
     * @param string $message
     * @param int $level
     * @param array $context
     * @return void
     */
    private function log($message, $level, array $context = [])
    {
        // Format message with context
        $message = $this->interpolate($message, $context);

        // Add timestamp and level
        $levelNames = [
            self::DEBUG => 'DEBUG',
            self::INFO => 'INFO',
            self::WARNING => 'WARNING',
            self::ERROR => 'ERROR'
        ];

        $logMessage = sprintf(
            '[%s] %s: %s - %s',
            date('Y-m-d H:i:s'),
            $levelNames[$level],
            $this->module->name,
            $message
        );

        // Get log severity based on level
        $severity = $this->getSeverity($level);

        // Write to PrestaShop log
        \PrestaShopLogger::addLog(
            $logMessage,
            $severity,
            null,
            $this->module->name,
            null,
            true
        );
    }

    /**
     * Replace placeholders in message with context values
     *
     * @param string $message
     * @param array $context
     * @return string
     */
    private function interpolate($message, array $context = [])
    {
        $replace = [];
        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        return strtr($message, $replace);
    }

    /**
     * Convert log level to PrestaShop severity
     *
     * @param int $level
     * @return int
     */
    private function getSeverity($level)
    {
        switch ($level) {
            case self::ERROR:
                return 3; // Error
            case self::WARNING:
                return 2; // Warning
            case self::INFO:
                return 1; // Info
            case self::DEBUG:
            default:
                return 4; // Debug
        }
    }
}