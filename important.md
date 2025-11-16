# VujaDe Platform - Simple Setup

## 📁 Folder Structure

### Default Laravel 12 (Keep As-Is)
```
app/
├── Http/Controllers/
├── Http/Middleware/
├── Http/Requests/
├── Models/
├── Providers/
├── Console/
├── Exceptions/
└── Mail/
```

### Add These Custom Folders
```
app/
├── Traits/           # Reusable traits
├── Services/         # Business logic
├── Enums/           # Constants & statuses
└── Actions/         # Single-purpose operations
```

## 🔧 Setup Commands

### 1. Install Packages
```bash
composer require spatie/laravel-permission
composer require spatie/laravel-medialibrary
composer require laravel/ui
composer require laravel/socialite
composer require spatie/laravel-activitylog
composer require spatie/laravel-otp
```

### 2. Install UI & Publish Configs
```bash
php artisan ui bootstrap --auth
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"
php artisan vendor:publish --provider="Spatie\Otp\OtpServiceProvider"
```

### 3. Create Custom Folders
```bash
mkdir -p app/Traits
mkdir -p app/Services
mkdir -p app/Enums
mkdir -p app/Actions
```

### 4. Run Migrations
```bash
php artisan migrate
```

## 🎯 That's It!

- Use Laravel 12 defaults
- Add 4 custom folders
- Install 6 packages
- Ready to code!

---

*Simple, clean, and ready for Phase 1 development.*
