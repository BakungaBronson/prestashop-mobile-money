# Development Guide for Mobile Money Module

## Git Setup Commands
```bash
# Initialize repository
git init

# Add remote repository
git remote add origin https://github.com/BakungaBronson/prestashop-mobile-money.git

# Create and checkout a new branch
git checkout -b feature/your-feature-name

# Stage all changes
git add .

# Commit changes
git commit -m "Your commit message"

# Push changes to remote branch
git push origin feature/your-feature-name

# Pull latest changes from main branch
git pull origin main
```

## Required Image Sizes

### Module Logo
- Standard logo: 57x57 pixels
- Small logo: 32x32 pixels
- Format: PNG with transparency
- Location: `/views/img/logo.png`

### QR Codes
- Recommended size: 300x300 pixels
- Minimum size: 200x200 pixels
- Maximum size: 500x500 pixels
- Format: PNG or JPG
- Location: `/views/img/qr/`

### Order State Icons
- Size: 16x16 pixels
- Format: GIF
- Location: `/views/img/orderstate/`
- Default payment icons: 24x24 pixels
- Location: `/views/img/option/`

## PrestaShop Module Standards

### File Structure
```
mobilemoney/
├── config/
│   └── config.xml
├── controllers/
│   ├── front/
│   │   ├── validation.php
│   │   └── payment.php
│   └── admin/
│       └── AdminMobileMoneyController.php
├── views/
│   ├── templates/
│   │   ├── front/
│   │   │   ├── payment.tpl
│   │   │   └── payment_confirmation.tpl
│   │   ├── hook/
│   │   │   └── payment.tpl
│   │   └── admin/
│   │       └── configure.tpl
│   └── img/
│       ├── logo.png
│       └── qr/
│           ├── mtn.png
│           └── airtel.png
├── classes/
│   ├── ErrorHandler.php
│   ├── Logger.php
│   └── Exception/
│       └── MobileMoneyException.php
├── mobilemoney.php
├── index.php
└── composer.json
```

### Required Files in Each Directory
- Every directory must contain an index.php file
- Main module class must match the module's name
- Config.xml must contain all module configuration
- License.md must be included for distribution

### Coding Standards
- PSR-1 and PSR-2 coding standards
- PHP 7.2+ compatibility
- Use namespaces for all classes
- Proper error handling and logging
- Input validation for all user inputs

### Security Requirements
- Validate all input data
- Escape all output
- Use PrestaShop's built-in security functions
- Implement CSRF protection
- Secure file uploads

## Development Commands

### Composer Setup
```bash
# Install dependencies
composer install

# Update autoloader
composer dump-autoload
```

### Module Installation
```bash
# Clear PrestaShop cache
rm -rf ../var/cache/*

# Set proper permissions
chmod -R 755 *
chmod -R 777 views/img/qr/
```

### Testing
- Create test orders with different amounts
- Test with different currencies
- Verify QR code display
- Check admin order management
- Validate payment confirmation flow

## Common Issues & Solutions

### QR Code Upload Issues
- Verify directory permissions (777 for qr directory)
- Check file size limits in PHP configuration
- Validate image dimensions before upload

### Payment Validation Issues
- Enable debug logging for troubleshooting
- Check currency configuration
- Verify order state creation
- Monitor PrestaShop error logs

### Admin Configuration Issues
- Clear PrestaShop cache after configuration changes
- Check admin controller registration
- Verify tab installation

## Useful Development Links
- [PrestaShop Dev Docs](https://devdocs.prestashop-project.org/)
- [Payment Module Guide](https://devdocs.prestashop-project.org/8/modules/payment/)
- [Coding Standards](https://devdocs.prestashop-project.org/8/development/coding-standards/)
- [Module Validation Rules](https://devdocs.prestashop-project.org/8/modules/core-updates/8.0/)