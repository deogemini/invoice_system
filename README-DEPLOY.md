# Deployment Guide for Ubuntu Server with Apache

This guide will help you deploy the Invoice System to an Ubuntu server running Apache.

## Prerequisites

- An Ubuntu Server (20.04, 22.04, or 24.04).
- Root or Sudo access.
- Domain name (optional, but recommended).

## Automatic Deployment

We have included a script `deploy-ubuntu.sh` that automates most of the process.

1.  **Upload the project** to your server (e.g., via git clone or SCP).
    ```bash
    # Example using git
    cd /var/www
    git clone <your-repo-url> invoice-system
    cd invoice-system
    ```

2.  **Make the script executable**:
    ```bash
    chmod +x deploy-ubuntu.sh
    ```

3.  **Run the script**:
    ```bash
    sudo ./deploy-ubuntu.sh
    ```

    Follow the on-screen prompts to configure your database (SQLite or MySQL).

## Manual Steps (If you prefer)

1.  **Install Dependencies**:
    - Apache2
    - PHP 8.2+ (with extensions: bcmath, ctype, fileinfo, json, mbstring, openssl, pdo, tokenizer, xml)
    - Composer
    - Node.js & NPM

2.  **Configure Apache**:
    - Copy `apache-invoice-system.conf` to `/etc/apache2/sites-available/invoice-system.conf`.
    - Edit `ServerName` in the file to match your domain/IP.
    - Enable the site: `sudo a2ensite invoice-system.conf`.
    - Enable rewrite module: `sudo a2enmod rewrite`.
    - Restart Apache: `sudo systemctl restart apache2`.

3.  **Setup Project**:
    - Run `composer install --no-dev`.
    - Run `npm install && npm run build`.
    - Copy `.env.example` to `.env` and configure DB.
    - Run `php artisan key:generate`.
    - Run `php artisan migrate --force`.

4.  **Permissions**:
    - Ensure `storage` and `bootstrap/cache` are writable by the web server (`www-data`).
    ```bash
    sudo chown -R www-data:www-data /var/www/invoice-system
    sudo chmod -R 775 storage bootstrap/cache
    ```
