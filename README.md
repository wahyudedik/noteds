# Noteds.com

> A business-focused social network platform for entrepreneurs, creators, and professionals to share ideas, validate business concepts, and collaborate.

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-green.svg)](https://vuejs.org)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Project Structure](#project-structure)
- [Key Features](#key-features)
- [Development](#development)
- [Testing](#testing)
- [Deployment](#deployment)
- [Documentation](#documentation)
- [Contributing](#contributing)
- [License](#license)

## 🎯 Overview

Noteds.com is a modern business-focused social networking platform built with Laravel and Vue.js. Unlike traditional social media, Noteds focuses on business networking, idea validation, and knowledge sharing.

### Core Philosophy

- **Business-Focused**: Designed for entrepreneurs, creators, and professionals
- **Purpose-Driven Content**: Posts categorized by purpose (ideas, questions, experiences, partnerships, tools, validations)
- **Community Validation**: Built-in idea validation system with expert feedback
- **Knowledge Sharing**: Threaded discussions with voting and best answer features

## ✨ Features

### ✅ Implemented Features

#### Core Social Features
- **Purpose-Based Posts**: 6 post types (business ideas, questions, experiences, partnerships, tools, validations)
- **Threaded Comments**: Nested comment system with replies
- **Voting System**: Upvote/downvote for posts and comments
- **Best Answer**: Mark comments as best answers
- **Idea Validation**: Expert validation system with capital estimation, BEP analysis, and risk assessment
- **Business Profiles**: Extended user profiles with business information, skills, goals, and portfolio
- **Content Moderation**: Automatic content filtering and moderation system
- **User Roles**: Admin and regular user roles

#### Explorer (News Integration)
- **MediaStack Integration**: News articles from MediaStack API
- **Article Discovery**: Browse and search business-related news articles
- **Article Sharing**: Share articles to the platform

#### User Experience
- **Modern UI/UX**: Clean, modern social media-inspired design
- **Responsive Design**: Mobile-friendly interface
- **Infinite Scroll**: Smooth infinite scroll for feeds
- **Floating Action Button**: Quick access to create posts from anywhere
- **Dashboard Analytics**: Personal analytics dashboard for users
- **Profile Tabs**: Organized profile with posts, analytics, and about sections

### 🚧 Planned Features

- **Global Search**: Unified search across posts, users, products, and articles
- **Notification Center**: Real-time notifications for interactions
- **User Settings**: Comprehensive settings for privacy, notifications, and preferences
- **Follow System**: Follow/unfollow users and see their content in feed
- **Report Content**: Report inappropriate content with admin review
- **Bookmarks**: Save posts for later reading
- **Admin User Management**: User banning, role management, and moderation tools
- **FAQ & Documentation**: Admin-managed FAQ and documentation system
## 🛠 Tech Stack

### Backend
- **Framework**: Laravel 12.x
- **PHP**: 8.2+
- **Database**: MySQL/PostgreSQL
- **Authentication**: Laravel Breeze + Sanctum
- **Queue**: Database queues (configurable to Redis)
- **File Storage**: Local filesystem (configurable to S3/Cloud Storage)

### Frontend
- **Framework**: Vue.js 3.x
- **Build Tool**: Vite
- **UI Framework**: Inertia.js (SPA-like experience)
- **Styling**: Tailwind CSS
- **Forms**: @tailwindcss/forms

### Third-Party Integrations
- **News API**: MediaStack API
- **Email**: Laravel Mail (SMTP)

### Development Tools
- **Code Quality**: Laravel Pint
- **Testing**: PHPUnit
- **Logging**: Laravel Pail (development)

## 📦 Requirements

- PHP 8.2 or higher
- Composer
- Node.js 18+ and NPM
- MySQL 8.0+ or PostgreSQL 13+
- Web server (Apache/Nginx) or PHP built-in server
- MediaStack API key (for news explorer)

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/noteds.git
cd noteds
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Environment

Edit `.env` file with your configuration:

```env
APP_NAME=Noteds
APP_URL=http://localhost:8000
APP_ENV=local

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=noteds
DB_USERNAME=root
DB_PASSWORD=

# MediaStack Configuration
MEDIASTACK_API_KEY=your_api_key

# Queue Configuration
QUEUE_CONNECTION=database
```

### 5. Database Setup

```bash
# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed
```

### 6. Storage Link

```bash
# Create symbolic link for storage
php artisan storage:link
```

### 7. Build Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 8. Start Development Server

```bash
# Using Laravel's built-in server
php artisan serve

# Or use the dev script (includes queue worker and logs)
composer run dev
```

The application will be available at `http://localhost:8000`

## ⚙️ Configuration

### File Upload Limits

The platform supports file uploads up to **50MB** for media. Configure PHP settings:

**`.user.ini`** (in project root):
```ini
upload_max_filesize = 50M
post_max_size = 52M
max_file_uploads = 20
memory_limit = 256M
```

### Queue Workers

For production, set up queue workers using Supervisor or systemd:

```bash
# Development
php artisan queue:work

# Production (with Supervisor)
php artisan queue:work --daemon --tries=3
```

## 📁 Project Structure

```
noteds/
├── app/
│   ├── Console/Commands/      # Artisan commands
│   ├── Exceptions/            # Custom exceptions
│   ├── Http/
│   │   ├── Controllers/      # Application controllers
│   │   ├── Middleware/       # Custom middleware
│   │   └── Requests/         # Form request validation
│   ├── Jobs/                 # Queue jobs
│   ├── Mail/                 # Email classes
│   ├── Models/               # Eloquent models
│   ├── Notifications/        # Laravel notifications
│   ├── Policies/             # Authorization policies
│   └── Services/             # Business logic services
├── database/
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── resources/
│   ├── js/
│   │   ├── Components/       # Vue components
│   │   ├── Layouts/          # Layout components
│   │   ├── Pages/           # Inertia pages
│   │   └── Utils/           # Utilities
│   └── views/               # Blade templates
├── routes/
│   ├── web.php              # Web routes
│   ├── auth.php             # Authentication routes
│   └── console.php          # Console routes
├── storage/                 # File storage
└── tests/                   # PHPUnit tests
```

## 🔑 Key Features

### Purpose-Based Posts

Posts are categorized by purpose to help users find relevant content:

1. **Business Idea** (`idea_business`): Share business ideas
2. **Ask Question** (`ask_question`): Ask questions to the community
3. **Share Experience** (`share_experience`): Share business experiences
4. **Find Partner** (`find_partner`): Find business partners
5. **Find Tools** (`find_tools`): Discover business tools
6. **Validate Idea** (`validate_idea`): Get expert validation for ideas

### Idea Validation System

For posts with `validate_idea` purpose, experts can provide:

- **Validation Status**: Layak (feasible) or Tidak Layak (not feasible)
- **Estimated Capital**: Required initial investment
- **Estimated BEP**: Break-even point estimation
- **Risks**: Identified risks (JSON array)
- **Feedback**: Detailed feedback and suggestions

Results are aggregated and displayed with approval percentages.

### Content Moderation

- **Automatic Filtering**: Forbidden words/phrases detection
- **Status Management**: Active, moderated, archived
- **Moderation Logs**: Track all moderation actions
- **Admin Review**: Manual content review system

### User Roles

- **Admin**: Full platform access, moderation, analytics
- **User**: Standard user with posting, commenting, voting

## 💻 Development

### Development Scripts

```bash
# Start all development services (server, queue, logs, vite)
composer run dev

# Run tests
composer run test

# Code formatting
./vendor/bin/pint
```

### Code Style

This project uses Laravel Pint for code formatting:

```bash
# Format code
./vendor/bin/pint

# Check code style
./vendor/bin/pint --test
```

### Database Migrations

```bash
# Create new migration
php artisan make:migration create_example_table

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Fresh migration with seeders
php artisan migrate:fresh --seed
```

### Creating Components

```bash
# Create controller
php artisan make:controller ExampleController

# Create model with migration
php artisan make:model Example -m

# Create service
php artisan make:service ExampleService

# Create form request
php artisan make:request StoreExampleRequest
```

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

## 🚢 Deployment

> **💡 Troubleshooting:** Jika mengalami error 503 pada assets setelah deployment, lihat [TROUBLESHOOTING.md](TROUBLESHOOTING.md) untuk panduan lengkap.

### Quick Deployment

Use the provided deployment script for easy production deployments:

```bash
# Make script executable (first time only, on Linux/Mac)
chmod +x deploy.sh

# Run deployment script
./deploy.sh
```

The script will automatically execute:
1. `git pull origin main` - Pull latest changes
2. `composer install --no-dev` - Install production dependencies
3. `npm install` - Install NPM dependencies
4. `npm run build` - Build production assets
5. `php artisan storage:link --force` - Ensure storage symlink exists
6. `php artisan migrate --force` - Run database migrations
7. `php artisan optimize` - Optimize and cache application

**Alternative:** You can also run the commands manually in one line:
```bash
git pull origin main && composer install --no-dev && npm install && npm run build && php artisan storage:link --force && php artisan migrate --force && php artisan optimize
```

**Note:** For Windows development, use Git Bash or WSL. On production Linux servers, run the script directly.

### Manual Deployment

If you prefer to run commands manually:

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan optimize
```

### Production Checklist

- [ ] Update `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure production database
- [ ] Set up queue workers (Supervisor/systemd)
- [ ] Configure file storage (S3/CDN if needed)
- [ ] Enable HTTPS/SSL
- [ ] Set up database backups
- [ ] Configure logging and monitoring (Sentry)
- [ ] Run deployment script or manual deployment commands
- [ ] **Check Nginx rate limiting** (if using Nginx/aaPanel):
  - [ ] Check `/www/server/panel/vhost/nginx/noteds.com.conf` for `limit_req` or `limit_conn`
  - [ ] Increase `burst` value or disable temporarily if getting 503 errors on assets
  - [ ] Check error logs: `tail -f /www/wwwlogs/noteds.com.error.log`

### Environment Variables (Production)

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://noteds.com

QUEUE_CONNECTION=redis  # or database
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## 📚 Documentation

- [Development Plans](.cursor/plans/) - Detailed implementation plans for features

### Available Plans

- `noteds.com_mvp_development_c95b8414.plan.md` - MVP core features
- `mediastack_explorer_feature_ae872b89.plan.md` - News explorer feature
- `fitur_lengkap_untuk_platform_noteds.com_1ad05aae.plan.md` - Additional features
- `implementasi_throttling_untuk_semua_endpoint_bc945ecd.plan.md` - Rate limiting
- `noteds.com_modern_social_media_redesign_31ed4c57.plan.md` - UI/UX redesign
- `infinite_scroll_&_enhanced_floating_action_button_5bf1d131.plan.md` - UX improvements
- `faq_&_documentation_crud_admin_panel_1e25e1ed.plan.md` - FAQ system (planned)

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Coding Standards

- Follow PSR-12 coding standards
- Use Laravel Pint for code formatting
- Write tests for new features
- Update documentation as needed
- Follow existing code structure and patterns

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP Framework
- [Vue.js](https://vuejs.org) - The Progressive JavaScript Framework
- [Inertia.js](https://inertiajs.com) - The Modern Monolith
- [MediaStack](https://mediastack.com) - News API
- [Tailwind CSS](https://tailwindcss.com) - Utility-first CSS Framework

## 📞 Support

For support, email support@noteds.com or open an issue in the repository.

---

**Built with ❤️ for the business community**
