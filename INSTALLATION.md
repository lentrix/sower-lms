# Sower LMS — Local Installation Guide (Windows)

> **Target:** Run the application locally at **http://lms.local** using Apache (XAMPP), MySQL, Composer, and NPM.

---

> **Legend:** &nbsp; 🌐 = Internet connection **required** for this step &nbsp;&nbsp; 💻 = Can be completed **offline**

---

## Table of Contents

1. [Prerequisites Overview](#1-prerequisites-overview)
2. [Install XAMPP](#2-install-xampp)
3. [Install Composer](#3-install-composer)
4. [Install Node.js and NPM](#4-install-nodejs-and-npm)
5. [Install Git](#5-install-git)
6. [Get the Project Files](#6-get-the-project-files)
7. [Create the MySQL Database](#7-create-the-mysql-database)
8. [Configure the Environment File](#8-configure-the-environment-file)
9. [Install PHP Dependencies](#9-install-php-dependencies)
10. [Install JavaScript Dependencies](#10-install-javascript-dependencies)
11. [Generate Application Key](#11-generate-application-key)
12. [Run Migrations and Seeders](#12-run-migrations-and-seeders)
13. [Build Frontend Assets](#13-build-frontend-assets)
14. [Configure Apache Virtual Host](#14-configure-apache-virtual-host)
15. [Edit the Windows Hosts File](#15-edit-the-windows-hosts-file)
16. [Enable PHP Extensions](#16-enable-php-extensions)
17. [Set Directory Permissions](#17-set-directory-permissions)
18. [Final Verification](#18-final-verification)
19. [Default Login Credentials](#19-default-login-credentials)

---

## 1. Prerequisites Overview

The following tools are required before installation:

| Tool | Minimum Version | Purpose |
|------|----------------|---------|
| XAMPP | 8.2.x | Apache web server + MySQL |
| PHP | 8.2+ | Runtime (bundled with XAMPP) |
| Composer | 2.x | PHP dependency manager |
| Node.js | 18.x LTS or higher | JavaScript runtime |
| NPM | 9.x or higher | JavaScript package manager (bundled with Node.js) |
| Git | Latest | Version control / cloning |

---

## 2. Install XAMPP 🌐

> 🌐 **Internet required** — You will need to download the XAMPP installer from the Apache Friends website.

XAMPP bundles **Apache** and **MySQL** along with **PHP 8.2**.

1. Go to [https://www.apachefriends.org/download.html](https://www.apachefriends.org/download.html)
2. Download **XAMPP for Windows — PHP 8.2.x** (choose the latest 8.2.x installer).
3. Run the installer with default settings. When prompted, select at least:
   - ✅ Apache
   - ✅ MySQL
   - ✅ phpMyAdmin
4. Install to the default path: `C:\xampp`
5. After installation finishes, open the **XAMPP Control Panel** and start both **Apache** and **MySQL**.

> **Tip:** Pin the XAMPP Control Panel to your taskbar for easy access.

---

## 3. Install Composer 🌐

> 🌐 **Internet required** — You will need to download the Composer installer from getcomposer.org.

Composer is the PHP dependency manager used by Laravel.

1. Go to [https://getcomposer.org/download/](https://getcomposer.org/download/)
2. Download and run **Composer-Setup.exe**.
3. During setup, point the PHP executable to XAMPP's PHP:
   ```
   C:\xampp\php\php.exe
   ```
4. Complete the installation with default settings.
5. Verify the installation by opening a new **Command Prompt** and running:
   ```cmd
   composer --version
   ```
   Expected output: `Composer version 2.x.x ...`

---

## 4. Install Node.js and NPM 🌐

> 🌐 **Internet required** — You will need to download the Node.js installer from nodejs.org.

Node.js is required to build Vue 3 + Vite frontend assets.

1. Go to [https://nodejs.org/](https://nodejs.org/)
2. Download the **LTS** installer (18.x or higher).
3. Run the installer with default settings. Ensure the option **"Add to PATH"** is checked.
4. Verify the installation by opening a new **Command Prompt** and running:
   ```cmd
   node --version
   npm --version
   ```
   Both commands should print version numbers.

---

## 5. Install Git 🌐

> 🌐 **Internet required** — You will need to download the Git installer from git-scm.com. *(Only required if using Option B in Step 6.)*

Git is needed to clone the project repository.

1. Go to [https://git-scm.com/download/win](https://git-scm.com/download/win)
2. Download and run the installer.
3. Use the recommended defaults. Ensure **"Git from the command line and also from 3rd-party software"** is selected.
4. Verify:
   ```cmd
   git --version
   ```

---

## 6. Get the Project Files

Choose **one** of the two options below. Both result in the project being available at `C:\xampp\htdocs\sower-lms`.

---

### Option A — Copy the Project Folder *(Recommended if you already have the files)*

> 💻 **No internet required** — All project files are already available locally on your machine.

Since this installation guide is stored inside the project folder itself, you likely already have all the project files. Simply copy the entire project folder into XAMPP's web root.

1. Locate the project folder on your computer (the folder that contains this guide).
2. Copy the entire folder:
   - Right-click the folder → **Copy** (or press `Ctrl + C`)
3. Paste it into XAMPP's htdocs directory:
   - Navigate to `C:\xampp\htdocs\` in File Explorer
   - Paste the folder (right-click → **Paste**, or `Ctrl + V`)
4. Rename the folder to `sower-lms` if it is not already named that.

The final path should be:
```
C:\xampp\htdocs\sower-lms\
```

5. Open **Command Prompt** and navigate into the project directory:
   ```cmd
   cd C:\xampp\htdocs\sower-lms
   ```

---

### Option B — Clone from Git *(If you have a remote repository)* 🌐

> 🌐 **Internet required** — Cloning requires an active connection to reach the remote Git repository.

1. Open **Command Prompt** and navigate to XAMPP's htdocs folder:
   ```cmd
   cd C:\xampp\htdocs
   ```
2. Clone the repository:
   ```cmd
   git clone <YOUR_REPOSITORY_URL> sower-lms
   ```
   Replace `<YOUR_REPOSITORY_URL>` with the actual Git remote URL of the project.

3. Enter the project directory:
   ```cmd
   cd sower-lms
   ```

> **Note:** Option B requires Git to be installed (see Step 5). If using Option A, Git is not required and Step 5 can be skipped.

---

## 7. Create the MySQL Database

1. Make sure **MySQL** is running in the XAMPP Control Panel.
2. Open a browser and go to: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
3. Click **"New"** in the left sidebar.
4. Enter the database name:
   ```
   sower_lms
   ```
5. Select **Collation:** `utf8mb4_unicode_ci`
6. Click **"Create"**.

---

## 8. Configure the Environment File

The `.env` file stores all environment-specific configuration.

1. In the project directory (`C:\xampp\htdocs\sower-lms`), copy the example file:
   ```cmd
   copy .env.example .env
   ```
2. Open `.env` with a text editor (Notepad, VS Code, etc.) and update the following values:

   ```dotenv
   APP_NAME="Sower LMS"
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://lms.local

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sower_lms
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   > **Note:** XAMPP's default MySQL root user has **no password**. If you set a password via phpMyAdmin, enter it in `DB_PASSWORD`.

3. Save the file.

---

## 9. Install PHP Dependencies 🌐

> 🌐 **Internet required** — Composer will connect to the Packagist registry to download all PHP packages. Ensure you have a stable internet connection before running this command.

In the project directory, run Composer to download all Laravel dependencies:

```cmd
cd C:\xampp\htdocs\sower-lms
composer install
```

This may take several minutes on the first run. You should see packages being downloaded into the `vendor/` directory.

---

## 10. Install JavaScript Dependencies 🌐

> 🌐 **Internet required** — NPM will connect to the npm registry to download all JavaScript packages. Ensure you have a stable internet connection before running this command.

Install all Node.js packages (Vue 3, Vite, Tailwind CSS, etc.):

```cmd
npm install
```

---

## 11. Generate Application Key

Laravel requires a unique application key for encryption. Generate it with:

```cmd
php artisan key:generate
```

This will automatically populate the `APP_KEY=` field in your `.env` file.

---

## 12. Run Migrations and Seeders

Create all database tables and populate them with initial data.

### Run Migrations

```cmd
php artisan migrate
```

This will create all tables:
- `users`
- `borrowers`
- `categories`
- `loan_plans`
- `loans`
- `payment_schedules`
- `payments`
- `loan_payments`
- `penalties`
- `penalty_payments`
- `permissions` / `roles` (Spatie)
- And other supporting tables

### Run Seeders

```cmd
php artisan db:seed
```

This creates the default users, permissions, and categories required to use the application.

---

## 13. Build Frontend Assets

Compile the Vue 3 + Tailwind CSS front-end into production-ready files:

```cmd
npm run build
```

After this completes, a `public/build/` directory will be populated with compiled assets.

---

## 14. Configure Apache Virtual Host

A virtual host lets Apache serve the app at `http://lms.local` instead of a subdirectory path.

### 14.1 — Enable Virtual Hosts in Apache

1. Open the file:
   ```
   C:\xampp\apache\conf\httpd.conf
   ```
2. Find the following line and **uncomment it** (remove the `#`):
   ```apache
   # Include conf/extra/httpd-vhosts.conf
   ```
   It should become:
   ```apache
   Include conf/extra/httpd-vhosts.conf
   ```
3. Save the file.

### 14.2 — Add the Virtual Host Entry

1. Open the file:
   ```
   C:\xampp\apache\conf\extra\httpd-vhosts.conf
   ```
2. Scroll to the bottom and add the following block:

   ```apache
   <VirtualHost *:80>
       ServerName lms.local
       DocumentRoot "C:/xampp/htdocs/sower-lms/public"
       <Directory "C:/xampp/htdocs/sower-lms/public">
           Options Indexes FollowSymLinks MultiViews
           AllowOverride All
           Require all granted
       </Directory>
       ErrorLog "C:/xampp/logs/lms-error.log"
       CustomLog "C:/xampp/logs/lms-access.log" combined
   </VirtualHost>
   ```

3. Save the file.

### 14.3 — Enable mod_rewrite

Laravel requires Apache's `mod_rewrite` module for URL routing.

1. Open `C:\xampp\apache\conf\httpd.conf`.
2. Find the following line and make sure it is **uncommented**:
   ```apache
   LoadModule rewrite_module modules/mod_rewrite.so
   ```
3. Save and close the file.

### 14.4 — Restart Apache

In the XAMPP Control Panel, click **Stop** then **Start** for Apache.

---

## 15. Edit the Windows Hosts File

The hosts file maps the custom domain `lms.local` to your local machine.

1. Open **Notepad as Administrator**:
   - Press `Windows Key`, type `Notepad`
   - Right-click → **"Run as administrator"**
2. Open the file:
   ```
   C:\Windows\System32\drivers\etc\hosts
   ```
3. Add the following line at the bottom:
   ```
   127.0.0.1   lms.local
   ```
4. Save the file (you must be running as Administrator to save).

---

## 16. Enable PHP Extensions

Some required PHP extensions may be disabled by default in XAMPP.

1. Open the file:
   ```
   C:\xampp\php\php.ini
   ```
2. Search for and **uncomment** (remove the `;`) the following extensions if they are commented out:
   ```ini
   extension=pdo_mysql
   extension=mbstring
   extension=openssl
   extension=tokenizer
   extension=xml
   extension=ctype
   extension=json
   extension=curl
   extension=fileinfo
   extension=zip
   ```
3. Save `php.ini`.
4. **Restart Apache** in the XAMPP Control Panel.

---

## 17. Set Directory Permissions

Laravel needs write access to the `storage` and `bootstrap/cache` directories.

On Windows with XAMPP, permissions are generally not restrictive. However, if you encounter permission errors, do the following:

1. In File Explorer, navigate to:
   ```
   C:\xampp\htdocs\sower-lms
   ```
2. Right-click the **`storage`** folder → **Properties** → **Security** tab.
3. Make sure the current user (or `Everyone`) has **Full control** or at least **Modify** access.
4. Repeat for the **`bootstrap\cache`** folder.

Alternatively, run the following command from the project directory to clear any stale cache:

```cmd
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 18. Final Verification

Perform a complete end-to-end check before opening the browser.

### Checklist

- [ ] XAMPP Apache is running (green indicator in Control Panel)
- [ ] XAMPP MySQL is running (green indicator in Control Panel)
- [ ] `.env` file exists with correct `DB_*` and `APP_URL=http://lms.local`
- [ ] `vendor/` directory exists (Composer install was run)
- [ ] `node_modules/` directory exists (npm install was run)
- [ ] `public/build/` directory exists (npm run build was run)
- [ ] `APP_KEY` is set in `.env` (artisan key:generate was run)
- [ ] Database `sower_lms` exists in MySQL
- [ ] Migrations ran without errors
- [ ] Seeders ran without errors
- [ ] Apache virtual host is configured
- [ ] Windows hosts file has the `127.0.0.1 lms.local` entry
- [ ] `mod_rewrite` is enabled in Apache

### Open the Application

Open a browser and navigate to:

```
http://lms.local
```

You should see the Sower LMS login page.

---

## 19. Default Login Credentials

The database seeder creates the following default accounts:

| Full Name | Username | Email | Password | Permissions |
|-----------|----------|-------|----------|-------------|
| Benjie B. Lenteria | `lentrix` | lentrix@materdeicollege.com | `password` | manage users, manage system |
| Rowerna Lauron | `ahwen` | ahwen@email.com | `password` | manage users, manage system |
| Clerk 1 | `clerk1` | clerk@sower.com | `password` | — |

> **Security Notice:** Change all default passwords immediately after the first login, especially in any environment accessible to others.

---

## Troubleshooting

### "No application encryption key has been specified"
Run `php artisan key:generate` and make sure `APP_KEY` is set in `.env`.

### "SQLSTATE[HY000] [1049] Unknown database 'sower_lms'"
Create the database in phpMyAdmin first, then re-run `php artisan migrate`.

### "Class 'PDO' not found" or MySQL connection errors
Enable the `extension=pdo_mysql` line in `C:\xampp\php\php.ini` and restart Apache.

### Apache won't start (port conflict)
Port 80 may be in use by another program (e.g., IIS, Skype). In the XAMPP Control Panel, click **Config** next to Apache → **httpd.conf** and change `Listen 80` to another port, or stop the conflicting application.

### "mix: Vite manifest not found" / blank page after login
Run `npm run build` to generate the production assets in `public/build/`.

### "The page isn't working" / 500 error
Check `storage/logs/laravel.log` for detailed error messages.

### Virtual host not loading (still shows XAMPP default page)
Make sure the `Include conf/extra/httpd-vhosts.conf` line in `httpd.conf` is uncommented and Apache was restarted after the change.

---

*Last updated: April 2026*
