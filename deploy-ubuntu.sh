#!/bin/bash

# Exit on error
set -e

echo "Starting Invoice System Deployment on Ubuntu..."

# 1. Update System
echo "Updating system packages..."
sudo apt-get update && sudo apt-get upgrade -y

# 2. Install Apache
echo "Installing Apache..."
sudo apt-get install -y apache2
sudo systemctl enable apache2
sudo systemctl start apache2

# 3. Install PHP and Extensions (Assuming PHP 8.2 or later for Laravel 11/12)
echo "Installing PHP and extensions..."
sudo apt-get install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt-get update
sudo apt-get install -y php8.3 libapache2-mod-php8.3 php8.3-mysql php8.3-xml php8.3-curl php8.3-mbstring php8.3-zip php8.3-bcmath php8.3-sqlite3 unzip

# Make sure php command points to 8.3
sudo update-alternatives --set php /usr/bin/php8.3

# 4. Install Composer
if ! command -v composer &> /dev/null; then
    echo "Installing Composer..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
fi

# 5. Install Node.js and NPM (Force install Node 22)
echo "Installing/Updating Node.js to v22..."
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt-get install -y nodejs

# 6. Setup Project Directory (Assuming we are inside the project root or moving to /var/www)
# We will assume the user runs this script FROM the project root, or we move it.
# Let's assume the user has cloned the repo to /var/www/invoice-system
# Allow user to override TARGET_DIR
read -p "Enter installation directory (default: /var/www/invoice-system): " USER_TARGET_DIR
TARGET_DIR=${USER_TARGET_DIR:-"/var/www/invoice-system"}

echo "Current directory: $(pwd)"
read -p "Is this the project root directory you want to deploy? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Please run this script from the project root."
    exit 1
fi

# Copy files to /var/www if not already there
if [ "$(pwd)" != "$TARGET_DIR" ]; then
    echo "Moving files to $TARGET_DIR..."
    sudo mkdir -p $TARGET_DIR
    sudo cp -r . $TARGET_DIR
fi

cd $TARGET_DIR

# 7. Install Dependencies
echo "Installing PHP dependencies..."
composer install --optimize-autoloader --no-dev

echo "Installing Node dependencies..."
npm install

# 8. Environment Setup
if [ ! -f .env ]; then
    echo "Creating .env file..."
    cp .env.example .env
    php artisan key:generate
fi

# Ask about Database
echo "Configuring Database..."
read -p "Do you want to use SQLite (s) or MySQL (m)? " -n 1 -r
echo
if [[ $REPLY =~ ^[Mm]$ ]]; then
    # MySQL Setup
    # Install MySQL Server if not present
    if ! command -v mysql &> /dev/null; then
        echo "Installing MySQL Server..."
        sudo apt-get install -y mysql-server
        sudo systemctl start mysql
        sudo systemctl enable mysql
    fi

    sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' .env
    read -p "Enter Database Name (default: invoice_system): " DB_NAME
    DB_NAME=${DB_NAME:-invoice_system}

    read -p "Enter Database User (default: invoice_user): " DB_USER
    DB_USER=${DB_USER:-invoice_user}

    read -s -p "Enter Database Password (will be generated if empty): " DB_PASS
    echo
    if [ -z "$DB_PASS" ]; then
        DB_PASS=$(openssl rand -base64 12)
        echo "Generated Password: $DB_PASS"
    fi

    sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env

    # Attempt to create database and user automatically
    read -p "Do you want to attempt to create the database and user automatically? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "Creating Database and User..."
        sudo mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;"
        sudo mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
        sudo mysql -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
        sudo mysql -e "FLUSH PRIVILEGES;"
        echo "Database setup complete."
    fi
else
    # SQLite Setup
    sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
    # Remove MySQL lines if they exist or comment them out
    # Create database.sqlite if not exists
    touch database/database.sqlite
fi

# 9. Build Frontend
echo "Building Frontend Assets..."
npm run build

# 10. Run Migrations
echo "Running Database Migrations..."
php artisan migrate --force

# 11. Permissions
echo "Setting Permissions..."
sudo chown -R www-data:www-data $TARGET_DIR
sudo chmod -R 775 $TARGET_DIR/storage
sudo chmod -R 775 $TARGET_DIR/bootstrap/cache
if [ -f database/database.sqlite ]; then
    sudo chmod 775 database/database.sqlite
    sudo chmod 775 database
fi

# 12. Apache Configuration
echo "Configuring Apache..."
sudo cp apache-invoice-system.conf /etc/apache2/sites-available/invoice-system.conf
sudo a2ensite invoice-system.conf
sudo a2enmod rewrite
sudo systemctl restart apache2

echo "Deployment Complete!"
echo "Please ensure your DNS points to this server IP."
