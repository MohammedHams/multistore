# MultiStore - متعدد المتاجر

<div dir="rtl">

## نظرة عامة

MultiStore هو نظام متكامل لإدارة متاجر متعددة، يتيح لكل متجر إدارة منتجاته وطلباته وموظفيه بشكل منفصل. يدعم النظام المصادقة الثنائية وإرسال إشعارات الطلبات عبر الواتساب.

### الميزات الرئيسية

1. **إدارة المتاجر والمستخدمين**
   - إنشاء متاجر جديدة مع بيانات خاصة (اسم المتجر، النطاق الفرعي، رقم الهاتف)
   - إدارة موظفي المتجر مع تحديد صلاحياتهم (إدارة الطلبات / المنتجات / إعدادات المتجر)

2. **المصادقة الثنائية**
   - إرسال رمز OTP عبر البريد الإلكتروني
   - نظام حراسة (Guard) منفصل لكل نوع مستخدم (صاحب متجر، موظف، مدير النظام)

3. **الطلبات والمنتجات**
   - إدارة المنتجات والطلبات بشكل كامل
   - ربط كل طلب بمتجر محدد ومنتجاته

4. **الربط مع خدمات خارجية**
   - إرسال تفاصيل الطلبات إلى الواتساب عبر ملف PDF
   - معالجة الإشعارات باستخدام نظام الطوابير (Queue)

</div>

## Installation

### Requirements

- PHP 8.1 or higher
- Composer
- MySQL 5.7 or higher
- Node.js and NPM
- Laravel 10

### Setup Steps

1. Clone the repository
   ```bash
   git clone https://github.com/yourusername/multistore.git
   cd multistore
   ```

2. Install PHP dependencies
   ```bash
   composer install
   ```

3. Install JavaScript dependencies
   ```bash
   npm install
   ```

4. Create a copy of the environment file
   ```bash
   cp .env.example .env
   ```

5. Generate an application key
   ```bash
   php artisan key:generate
   ```

6. Configure your database in the `.env` file
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=multistore
   DB_USERNAME=root
   DB_PASSWORD=
   ```

7. Configure WhatsApp API in the `.env` file
   ```
   WHATSAPP_ENABLED=true
   WHATSAPP_API_URL=https://graph.facebook.com/v17.0/YOUR_PHONE_NUMBER_ID/messages
   WHATSAPP_API_TOKEN=YOUR_API_TOKEN
   WHATSAPP_DEFAULT_PHONE=+1234567890
   ```

8. Configure queue settings in the `.env` file
   ```
   QUEUE_CONNECTION=database
   QUEUE_FAILED_DRIVER=database
   ```

9. Run database migrations and seed the database
   ```bash
   php artisan migrate --seed
   ```

10. Build frontend assets
    ```bash
    npm run dev
    ```

11. Start the development server
    ```bash
    php artisan serve
    ```

## Queue Worker Setup

The application uses Laravel's queue system to process WhatsApp notifications in the background. To start the queue worker:

```bash
# Start a queue worker for the default queue
php artisan queue:work

# Start a dedicated worker for WhatsApp notifications
php artisan queue:work --queue=whatsapp

# Start a worker that listens to multiple queues with priorities
php artisan queue:work --queue=whatsapp,default

# Run the worker in the background (for production)
php artisan queue:work --daemon --queue=whatsapp,default
```

### Monitoring Failed Jobs

To view and manage failed jobs:

```bash
# List all failed jobs
php artisan queue:failed

# Retry a specific failed job
php artisan queue:retry [id]

# Retry all failed jobs
php artisan queue:retry all

# Delete a failed job
php artisan queue:forget [id]

# Delete all failed jobs
php artisan queue:flush
```

## Test Credentials

### Admin Login
- URL: `/admin/login`
- Email: `admin@example.com`
- Password: `password`

### Store Owner Login
- URL: `/store-owner/login`
- Email: `owner@example.com`
- Password: `password`

### Store Staff Login
- URL: `/store-staff/login`
- Email: `staff@example.com`
- Password: `password`

## Project Structure

The application follows a modular structure using Laravel Modules:

```
Modules/
├── Order/             # Order management module
│   ├── Entities/      # Order domain models
│   ├── Events/        # Order-related events
│   ├── Http/          # Controllers and requests
│   ├── Jobs/          # Queue jobs for order processing
│   ├── Listeners/     # Event listeners
│   ├── Repositories/  # Order repositories
│   ├── Resources/     # Views and assets
│   └── Services/      # Order-related services
│
├── Product/           # Product management module
│   ├── Entities/
│   ├── Http/
│   ├── Repositories/
│   └── Resources/
│
└── Store/             # Store management module
    ├── Entities/
    ├── Http/
    ├── Repositories/
    └── Resources/
```

## Architecture

The application follows a clean architecture with:

1. **Dual Model Structure**:
   - Eloquent models in `App\Models` namespace for database operations
   - Value object entities in `Modules\*\Entities` namespace as return types from repositories

2. **Repository Pattern**:
   - Interface-based repositories for each module
   - Eloquent implementations of these repositories

3. **Service Layer**:
   - Business logic encapsulated in services
   - External integrations handled through dedicated services

4. **Event-Driven Architecture**:
   - Events dispatched for important actions
   - Listeners handle side effects (like sending notifications)
   - Queue jobs for asynchronous processing

## License

This project is licensed under the MIT License.
