#!/bin/bash

# Set module name
MODULE_NAME="mobilemoneypay"

# Create main module directory
mkdir -p $MODULE_NAME

# Create directory structure
cd $MODULE_NAME

# Controllers
mkdir -p controllers/admin
mkdir -p controllers/front
touch controllers/admin/AdminMobileMoneyPayController.php
touch controllers/front/validation.php

# Translations
mkdir -p translations
touch translations/en.php

# Views structure
mkdir -p views/img/qr
mkdir -p views/templates/admin
mkdir -p views/templates/front
mkdir -p views/templates/hook

# Create template files
touch views/templates/admin/payment_status.tpl
touch views/templates/front/payment_form.tpl
touch views/templates/front/payment_return.tpl

# Create QR placeholder files
touch views/img/qr/airtel.png
touch views/img/qr/mtn.png

# Create security files
touch index.php
find . -type d -exec touch {}/index.php \;

# Create main module files
touch mobilemoneypay.php
touch config.xml
touch composer.json
touch README.md
touch .htaccess
touch logo.png

echo "Module structure created successfully in ./$MODULE_NAME"