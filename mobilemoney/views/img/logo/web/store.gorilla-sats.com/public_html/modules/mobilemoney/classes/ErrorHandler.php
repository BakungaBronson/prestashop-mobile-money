<?php
/**
 * Error handling trait for Mobile Money module
 */

namespace PrestaShop\Module\MobileMoney;

trait ErrorHandler
{
    protected $errors = [];
    protected $warnings = [];
    protected $successes = [];

    /**
     * Add error message
     *
     * @param string $error
     * @param array $parameters Parameters to replace in the error message
     * @param string $domain Translation domain
     * @return void
     */
    protected function addError($error, array $parameters = [], $domain = 'Admin.Notifications.Error')
    {
        $this->errors[] = $this->trans($error, $parameters, $domain);
    }

    /**
     * Add warning message
     *
     * @param string $warning
     * @param array $parameters
     * @param string $domain
     * @return void
     */
    protected function addWarning($warning, array $parameters = [], $domain = 'Admin.Notifications.Warning')
    {
        $this->warnings[] = $this->trans($warning, $parameters, $domain);
    }

    /**
     * Add success message
     *
     * @param string $success
     * @param array $parameters
     * @param string $domain
     * @return void
     */
    protected function addSuccess($success, array $parameters = [], $domain = 'Admin.Notifications.Success')
    {
        $this->successes[] = $this->trans($success, $parameters, $domain);
    }

    /**
     * Get all error messages
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get all warning messages
     *
     * @return array
     */
    public function getWarnings()
    {
        return $this->warnings;
    }

    /**
     * Get all success messages
     *
     * @return array
     */
    public function getSuccesses()
    {
        return $this->successes;
    }

    /**
     * Check if there are any errors
     *
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Check if there are any warnings
     *
     * @return bool
     */
    public function hasWarnings()
    {
        return !empty($this->warnings);
    }

    /**
     * Check if there are any success messages
     *
     * @return bool
     */
    public function hasSuccess()
    {
        return !empty($this->successes);
    }

    /**
     * Clear all messages
     *
     * @return void
     */
    protected function clearMessages()
    {
        $this->errors = [];
        $this->warnings = [];
        $this->successes = [];
    }
}