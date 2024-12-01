# PrestaShop Mobile Money Payment Module

This module adds MTN Mobile Money and Airtel Money payment options to your PrestaShop store.

## Features

- Supports both MTN Mobile Money and Airtel Money
- QR code-based payment system
- Manual payment confirmation by admin
- Custom order status for pending mobile money payments
- Easy configuration through PrestaShop admin panel

## Installation

1. Download the module
2. Upload the `mobilemoney` folder to your PrestaShop's `modules` directory
3. Install the module through PrestaShop's module manager
4. Configure the module by uploading your MTN and Airtel Money QR codes

## Configuration

1. Go to PrestaShop admin panel
2. Navigate to Modules > Module Manager
3. Find "Mobile Money Payment" and click Configure
4. Upload your QR code images for both MTN Mobile Money and Airtel Money
5. Save the configuration

## Usage

### For Customers

1. During checkout, customers will see "Pay with Mobile Money" as a payment option
2. After selecting Mobile Money, they can choose between MTN Mobile Money and Airtel Money
3. The corresponding QR code will be displayed
4. Customers scan the QR code using their mobile money app
5. After completing the payment in their app, they click "I have paid"
6. The order is created with a "Awaiting Mobile Money payment" status

### For Store Administrators

1. When a mobile money payment is made, the order will have "Awaiting Mobile Money payment" status
2. Once you confirm the payment in your mobile money account:
   - Go to Orders > Orders in the PrestaShop admin panel
   - Find the order and change its status to "Payment accepted"
3. The order will then be processed according to your normal workflow

## Requirements

- PrestaShop 1.7 or higher
- PHP 7.2 or higher
- SSL certificate (recommended for secure payments)

## Security

This module includes several security features:
- Input validation for all form submissions
- File upload restrictions for QR codes (only jpg/png allowed)
- XSS protection through PrestaShop's built-in security features
- CSRF protection for all forms
- Secure headers in all PHP files

## License

This module is released under the MIT License. See the LICENSE file for more details.