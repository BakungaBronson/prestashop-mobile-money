<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 */

declare(strict_types=1);

namespace PrestaShop\Module\MobileMoneyPay\Helper;

class MobileMoneyQrHelper
{
    private const QR_PATH = _PS_MODULE_DIR_ . 'mobilemoneypay/views/img/qr/';
    
    /**
     * Get the QR code URL for a specific provider
     *
     * @param string $provider The provider (airtel or mtn)
     * @return string|null The URL to the QR code image or null if not found
     */
    public static function getQrCodeUrl(string $provider): ?string
    {
        $filename = self::QR_PATH . strtolower($provider) . '.png';
        
        if (file_exists($filename)) {
            return _PS_BASE_URL_ . '/modules/mobilemoneypay/views/img/qr/' . strtolower($provider) . '.png';
        }
        
        return null;
    }
    
    /**
     * Check if QR code exists for a provider
     *
     * @param string $provider The provider to check
     * @return bool Whether the QR code exists
     */
    public static function hasQrCode(string $provider): bool
    {
        return file_exists(self::QR_PATH . strtolower($provider) . '.png');
    }
    
    /**
     * Upload a new QR code for a provider
     *
     * @param string $provider The provider (airtel or mtn)
     * @param array $file The uploaded file ($_FILES array element)
     * @return bool Whether the upload was successful
     */
    public static function uploadQrCode(string $provider, array $file): bool
    {
        // Validate provider
        if (!in_array(strtolower($provider), ['airtel', 'mtn'])) {
            return false;
        }
        
        // Create directory if it doesn't exist
        if (!file_exists(self::QR_PATH)) {
            mkdir(self::QR_PATH, 0777, true);
        }
        
        // Check if it's a valid image file
        if (!self::isValidImage($file)) {
            return false;
        }
        
        $filename = self::QR_PATH . strtolower($provider) . '.png';
        
        // Move uploaded file
        return move_uploaded_file($file['tmp_name'], $filename);
    }
    
    /**
     * Delete a provider's QR code
     *
     * @param string $provider The provider whose QR code should be deleted
     * @return bool Whether the deletion was successful
     */
    public static function deleteQrCode(string $provider): bool
    {
        $filename = self::QR_PATH . strtolower($provider) . '.png';
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return true;
    }
    
    /**
     * Validate an uploaded image file
     *
     * @param array $file The uploaded file ($_FILES array element)
     * @return bool Whether the file is a valid image
     */
    private static function isValidImage(array $file): bool
    {
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return false;
        }
        
        // Check file type
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return false;
        }
        
        // Only allow PNG files
        if ($imageInfo[2] !== IMAGETYPE_PNG) {
            return false;
        }
        
        // Check file size (max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get the absolute path to a provider's QR code
     *
     * @param string $provider The provider
     * @return string The absolute path to the QR code
     */
    public static function getQrCodePath(string $provider): string
    {
        return self::QR_PATH . strtolower($provider) . '.png';
    }
}