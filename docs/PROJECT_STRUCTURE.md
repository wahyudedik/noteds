# Noteds Project Structure & Organization

## Root Level Organization (December 13, 2025)

```
noteds/
├── 📁 app/                    # Application code
│   ├── Console/              # Artisan commands
│   ├── Controllers/          # Request handlers
│   ├── Events/              # Event classes
│   ├── Exceptions/          # Custom exceptions
│   ├── Jobs/                # Queue jobs
│   ├── Mail/                # Email classes
│   ├── Models/              # Eloquent models
│   ├── Policies/            # Authorization policies
│   └── Services/            # Business logic
│
├── 📁 bootstrap/             # Bootstrap files
├── 📁 config/                # Configuration files
├── 📁 database/              # Migrations & seeders
├── 📁 public/                # Web server root
├── 📁 resources/             # Views & assets
│   ├── css/                 # Tailwind CSS
│   ├── js/                  # Vue components
│   └── views/               # Blade templates
├── 📁 routes/                # Route definitions
├── 📁 storage/               # Runtime storage
├── 📁 tests/                 # Test files
│
├── 📁 docs/                  # 📚 DOCUMENTATION (NEW)
│   ├── audits/              # All audit reports
│   ├── guides/              # Setup & operational guides
│   │   ├── deployment/      # Deployment procedures
│   │   ├── quick-start/     # Quick reference
│   │   ├── security/        # Security guides
│   │   ├── webhooks/        # Webhook integration
│   │   ├── payments/        # Payment setup
│   │   └── performance/     # Performance tips
│   ├── features/            # Feature documentation
│   │   ├── admin/           # Admin features
│   │   ├── contest/         # Contest system
│   │   ├── currency/        # Currency system
│   │   ├── dashboard/       # Dashboard features
│   │   ├── forum/           # Forum features
│   │   ├── landing-page/    # Landing page
│   │   ├── marketplace/     # Marketplace
│   │   ├── notes/           # Notes features
│   │   ├── premium/         # Premium features
│   │   ├── sidebar/         # Sidebar setup
│   │   └── wallet/          # Wallet features
│   ├── progress/            # Implementation reports
│   └── INDEX.md             # Documentation index
│
├── 📁 scripts/               # 🔧 UTILITY SCRIPTS (NEW)
│   ├── update_exchange_rates.php
│   ├── verify_exchange_rates.php
│   ├── verify_wallet_routes.php
│   ├── check_user_roles.php
│   ├── check-wallet.php
│   ├── webhook-diagnostics.php
│   ├── test-webhook.php
│   ├── audit_currency_conversion.php
│   ├── test-currency-integration.bat
│   └── cleanup.bat
│
├── 📁 .github/               # GitHub configuration
├── 📁 build/                 # Build output
├── 📁 node_modules/          # NPM packages
├── 📁 vendor/                # Composer packages
│
├── .editorconfig             # Editor settings
├── .env.example              # Environment template
├── .gitattributes            # Git attributes
├── .gitignore                # Git ignore rules (UPDATED)
├── .nvmrc                     # Node version
├── artisan                    # Laravel CLI
├── composer.json             # PHP dependencies
├── composer.lock             # Locked dependencies
├── package.json              # NPM dependencies
├── package-lock.json         # Locked NPM dependencies
├── phpstan.neon.dist         # PHPStan config
├── phpunit.xml               # PHPUnit config
├── pint.json                 # PHP formatter config
├── postcss.config.js         # PostCSS config
├── README.md                 # Project overview
├── rector.php                # Code refactoring config
├── tailwind.config.js        # Tailwind CSS config
├── vite.config.js            # Vite bundler config
│
└── CHANGELOG.md              # Version history

```

## Key Organization Changes

### 📚 Documentation Structure (NEW)

All 120+ documentation files organized into:

1. **`docs/audits/`** - Audit reports
   - Security audits
   - Bug tracking
   - Feature audits
   - Verification reports

2. **`docs/guides/`** - Operational guides
   - Deployment procedures
   - Quick reference guides
   - Security best practices
   - Webhook integration
   - Payment configuration
   - Performance optimization

3. **`docs/features/`** - Feature-specific docs
   - Each feature has dedicated folder
   - Implementation guides
   - Configuration instructions
   - Security notes

4. **`docs/progress/`** - Implementation reports
   - Phase completion reports
   - Feature status
   - Bug fixes
   - Integration notes

### 🔧 Scripts Directory (NEW)

All utility PHP/Shell scripts consolidated in `scripts/`:

```bash
php scripts/update_exchange_rates.php
php scripts/verify_exchange_rates.php
php scripts/check_user_roles.php
# etc...
```

## Navigation Tips

### Finding Documentation

1. **Start with** → `docs/INDEX.md` (comprehensive guide)
2. **Quick reference** → `docs/guides/quick-start/`
3. **Feature help** → `docs/features/[feature-name]/`
4. **Deployment** → `docs/guides/deployment/`
5. **Security** → `docs/guides/security/`

### Development Workflow

```bash
# 1. Project Overview
cat README.md

# 2. Navigate documentation
cat docs/INDEX.md

# 3. Feature-specific docs
cat docs/features/[feature]/

# 4. Setup & Deployment
cat docs/guides/deployment/DEPLOYMENT_GUIDE.md

# 5. Run utility scripts
php scripts/[script-name].php
```

## Quick Stats

| Metric | Value |
|--------|-------|
| Total Documentation Files | 120+ |
| Audit Reports | 50+ |
| Feature Guides | 70+ |
| Total Utility Scripts | 10+ |
| Project Status | Production Ready |
| Last Organized | Dec 13, 2025 |

## File Conventions

### Documentation Files

```
[FEATURE/AREA]_[TYPE]_[DESCRIPTION].md

Examples:
- CURRENCY_SYSTEM_ARCHITECTURE.md
- WALLET_QUICK_REFERENCE.md
- SECURITY_AUDIT_RESULTS.md
- DEPLOYMENT_GUIDE.md
```

### Script Files

```
[ACTION]_[TARGET].php
[ACTION]_[TARGET].sh
[ACTION]_[TARGET].bat

Examples:
- update_exchange_rates.php
- verify_wallet_routes.php
- check_user_roles.php
```

## Best Practices

### Adding New Documentation
1. Place in appropriate `docs/` subfolder
2. Follow naming convention
3. Include front matter with last update date
4. Link from `docs/INDEX.md`

### Adding New Scripts
1. Place in `scripts/` folder
2. Add usage comments at top
3. Document in this file
4. Update `scripts/README.md` if needed

### Maintaining Organization
1. Keep root clean (no loose doc files)
2. Archive old reports if needed
3. Update `docs/INDEX.md` regularly
4. Review quarterly for consolidation

---

**Documentation Organization Status:** ✅ Complete  
**Last Updated:** December 13, 2025  
**Maintained By:** Development Team
