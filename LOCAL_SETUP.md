# 🚀 Noteds — Local Development Setup Guide

## 📋 Prerequisites

- PHP 8.2+ (via Laragon/Herd/XAMPP)
- Composer 2.x
- Node.js 18+ (recommended) or 20+ (for Herd HTTPS compatibility)
- MySQL/MariaDB
- Git

## 🛠️ Installation Steps

### 1. Clone Repository
```bash
git clone <repository-url>
cd noteds
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```env
APP_NAME="Noteds"
APP_ENV=local
APP_DEBUG=true
APP_URL=https://noteds.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=noteds_db
DB_USERNAME=root
DB_PASSWORD=

MIDTRANS_SERVER_KEY=your_sandbox_key_here
MIDTRANS_CLIENT_KEY=your_sandbox_key_here
MIDTRANS_IS_PRODUCTION=false

OLLAMA_URL=http://localhost:11434
OLLAMA_MODEL=llama3.2
```

### 4. Database Setup
```bash
php artisan migrate
php artisan db:seed
```

### 5. Storage Link
```bash
php artisan storage:link
```

### 6. Frontend Assets

#### Using Herd (HTTPS) - Recommended
```bash
npm install
npm run build
```

**Note:** `npm run dev` tidak stabil dengan Node.js 22.x + Herd HTTPS. Gunakan `npm run build` untuk development.

#### Using `php artisan serve` (HTTP)
```bash
npm install
npm run dev
```

Run in another terminal:
```bash
php artisan serve
```

Access: `http://localhost:8000`

### 7. Development Tools

#### Laravel Telescope (Debugging)
Access: `/telescope` (local only, admin only in production)

#### Laravel Debugbar
Auto-injected in local environment

#### Laravel Pint (Code Style)
```bash
composer pint
```

#### Pest Testing
```bash
./vendor/bin/pest
```

## 🔧 Common Commands

### Cache Management
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### Fresh Migration
```bash
php artisan migrate:fresh --seed
```

### Serve Development
```bash
php artisan serve
```

### Assets Development
```bash
npm run dev      # With php artisan serve
npm run build    # With Herd/Vite (production build)
```

## 🐛 Troubleshooting

### Vite ERR_EMPTY_RESPONSE
**Problem:** Node.js 22.x + Vite 6.x + Herd HTTPS incompatibility

**Solution:**
```bash
npm run build
# Or downgrade to Node.js 20
```

### Database Connection Error
- Check MySQL service is running
- Verify `.env` database credentials
- Run `php artisan config:clear`

### Composer Install Fails
```bash
composer clear-cache
composer install --ignore-platform-reqs
```

### NPM Install Fails
```bash
npm cache clean --force
rm -rf node_modules package-lock.json
npm install
```

## 📦 Tech Stack

- **Backend:** Laravel 11 (PHP 8.2+)
- **Frontend:** Blade + Tailwind CSS + Alpine.js
- **Database:** MySQL
- **Auth:** Laravel Breeze
- **Permissions:** Spatie Permission
- **Build Tool:** Vite 6.4.1
- **AI:** Ollama (local LLM)

## 🔐 Default Credentials

**Admin:**
- Email: `admin@noteds.test`
- Password: `password`

**Seller/Buyer:**
- Created via seeders (UserSeeder)
- Email format: `seller1@noteds.test`, `buyer1@noteds.test`
- Password: `password`

## 📝 Next Steps

1. Read [TASKLIST.md](TASKLIST.md) for development roadmap
2. Read [README.md](README.md) for platform overview
3. Read [VPS_SETUP.md](VPS_SETUP.md) for deployment guide

