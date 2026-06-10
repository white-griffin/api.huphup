# مستند کلی پروژه api.huphup برای تحلیل توسط AI

این سند خلاصه‌ای از ساختار، دامنه، APIها، مدل داده و نکات مهم پروژه Laravel موجود در این ریپو است. هدفش این است که هنگام کمک گرفتن از AI، بتوان سریع کانتکست پروژه را منتقل کرد.

## معرفی پروژه

- پروژه یک API بک‌اند با Laravel 11 و PHP 8.2 برای پلتفرم حیوانات خانگی/خدمات پت با نام «هاپ هاپ» است.
- دامنه اصلی شامل کاربران، پت‌ها، تأمین‌کنندگان/Providerها، کسب‌وکارها، خدمات، زمان‌بندی، رزرو نوبت، محصولات، دسته‌بندی‌ها و برندها است.
- پنل ادمین با Filament 4 روی مسیر `/admin` پیاده‌سازی شده و guard آن `admin` است.
- احراز هویت API برای کاربران و Providerها با Laravel Sanctum انجام می‌شود.

## تکنولوژی‌ها و پکیج‌های مهم

- Laravel Framework `^11.0`
- PHP `^8.2`
- Laravel Sanctum برای API token
- Filament `^4.0` برای پنل مدیریت
- BezhanSalleh Filament Shield و Spatie Permission برای نقش/مجوز
- `ipe/smsir-php` برای ارسال OTP با SmsIr
- `morilog/jalali` و `hekmatinasser/verta` برای تاریخ شمسی
- Tailwind/Vite برای assets پنل/فرانت

فایل‌های مرجع:

- `composer.json`
- `package.json`
- `config/auth.php`
- `bootstrap/app.php`

## ساختار مسیرها

Routing در Laravel 11 داخل `bootstrap/app.php` تعریف شده و فایل‌های route جداگانه دارد:

- `api/v1/user` → `routes/user/api_v1.php`
- `api/v1/user/auth` → `routes/user/auth_v1.php`
- `api/v1/provider` → `routes/provider/api_v1.php`
- `api/v1/provider/auth` → `routes/provider/auth_v1.php`

نکته: مسیرهای provider به‌جز auth با middlewareهای `api`, `auth:provider`, `resolve.business` و `scopeBindings` محافظت می‌شوند. Provider باید هدر `X-Business-Id` بفرستد.

## احراز هویت و Guardها

در `config/auth.php` سه guard اصلی وجود دارد:

- `admin`: session + provider `admins`
- `web`: session + provider `users`
- `provider`: sanctum + provider `providers`

### User Auth

کنترلر: `app/Http/Controllers/User/Api/V1/AuthController.php`

- `POST /api/v1/user/auth/login`
  - ورودی: `mobile`
  - اگر کاربر وجود نداشته باشد ساخته می‌شود.
  - در production کد OTP تصادفی و SMS ارسال می‌شود؛ در محیط غیر production کد `111111` است.
  - توکن‌های قبلی کاربر حذف می‌شوند.
- `POST /api/v1/user/auth/check_code`
  - ورودی: `mobile`, `otp_code`
  - در صورت صحت کد، `activity_status` فعال شده و Sanctum token برمی‌گردد.
- `GET /api/v1/user/auth/logout`
  - نیازمند `auth:sanctum`
  - همه tokenهای کاربر را حذف می‌کند.

### Provider Auth

کنترلر: `app/Http/Controllers/Provider/Api/V1/AuthController.php`

- `POST /api/v1/provider/auth/login`
  - ورودی: `mobile`, `password`
  - Rate limit: سه تلاش، قفل ۵ دقیقه‌ای.
  - اگر `two_factor_status` فعال باشد، OTP دو دقیقه‌ای ارسال می‌شود.
  - در غیر این صورت Sanctum token برمی‌گردد.
- `POST /api/v1/provider/auth/check_code`
  - تأیید 2FA با rate limit جداگانه.
- `GET /api/v1/provider/auth/logout`
  - نیازمند `auth:provider`

## Middleware کسب‌وکار جاری

فایل: `app/Http/Middleware/ResolveBusiness.php`

- از guard `provider` کاربر جاری را می‌گیرد.
- هدر `X-Business-Id` را الزامی می‌داند.
- بررسی می‌کند business متعلق به provider باشد.
- business را در container با کلید `business` ثبت می‌کند.

Helper مرتبط: `app/Support/helpers.php`

- تابع `business()` نمونه business جاری را برمی‌گرداند.

Trait و Scope مرتبط:

- `app/Models/Traits/BelongsToBusiness.php`
- `app/Models/Scopes/BusinessScope.php`

مدل‌هایی مثل `Product` و `Appointment` این trait را دارند؛ هنگام create اگر business در container باشد، `business_id` اتومات ست می‌شود و queryها به business جاری scope می‌شوند.

## APIهای User

فایل route: `routes/user/api_v1.php`

### Profile و Address

کنترلر: `app/Http/Controllers/User/Api/V1/ProfileController.php`

- `GET /api/v1/user/profile`
- `POST /api/v1/user/profile`
- `POST /api/v1/user/address`
- `POST /api/v1/user/address/{address}`
- `DELETE /api/v1/user/address/{address}`

همه نیازمند `auth:sanctum` هستند.

### Species و Breeds

کنترلرها:

- `app/Http/Controllers/User/Api/V1/SpeciesController.php`
- `app/Http/Controllers/User/Api/V1/BreedsController.php`

مسیرها:

- `GET /api/v1/user/species`
- `GET /api/v1/user/breeds`

### Pets

کنترلر: `app/Http/Controllers/User/Api/V1/PetController.php`

- `GET /api/v1/user/pets`
- `POST /api/v1/user/pets`
- `GET /api/v1/user/pets/{pet}`
- `POST /api/v1/user/pets/{pet}`

همه نیازمند `auth:sanctum` هستند.

### Appointments

کنترلر: `app/Http/Controllers/User/Api/V1/AppointmentController.php`
سرویس: `app/Services/AppointmentService.php`

- `GET /api/v1/user/appointments/{businessId}/available-slots`
  - query/body: `service_id`, `date`
- `GET /api/v1/user/appointments`
- `POST /api/v1/user/appointments`
  - ورودی: `business_id`, `service_id`, `pet_id`, `starts_at`
- `GET /api/v1/user/appointments/cancel/{appointment}`

## APIهای Provider

فایل route: `routes/provider/api_v1.php`

همه این مسیرها نیازمند token provider و هدر `X-Business-Id` هستند.

### Categories

کنترلر: `app/Http/Controllers/Provider/Api/V1/CategoryController.php`

- `GET /api/v1/provider/categories`
- `GET /api/v1/provider/categories/{category}`

### Products

کنترلر: `app/Http/Controllers/Provider/Api/V1/ProductController.php`

- `GET /api/v1/provider/products`
- `GET /api/v1/provider/products/{product}`
- `POST /api/v1/provider/products`
- `POST /api/v1/provider/products/{product}`
- `POST /api/v1/provider/products/{product}/images`
- `DELETE /api/v1/provider/products/{product}/images/{image}`
- `POST /api/v1/provider/products/{product}/images/set-primary/{image}`
- `POST /api/v1/provider/products/{product}/images/re-order`

### Brands

کنترلر: `app/Http/Controllers/Provider/Api/V1/BrandController.php`

- `GET /api/v1/provider/brands`

### Schedules و Breaks

کنترلرها:

- `app/Http/Controllers/Provider/Api/V1/ScheduleController.php`
- `app/Http/Controllers/Provider/Api/V1/ScheduleBreakController.php`

مسیرها:

- `GET /api/v1/provider/schedules`
- `POST /api/v1/provider/schedules`
- `POST /api/v1/provider/schedules/{schedule}/breaks`
- `POST /api/v1/provider/schedules/{schedule}/breaks/{break}`

### Off Days

کنترلر: `app/Http/Controllers/Provider/Api/V1/BusinessOffDayController.php`

- `GET /api/v1/provider/off-days`
- `POST /api/v1/provider/off-days`
- `DELETE /api/v1/provider/off-days/{offDay}`

## مدل‌های اصلی و روابط

### User

فایل: `app/Models/User.php`

- Auth با Sanctum
- روابط:
  - `addresses()`: hasMany `UserAddress`
  - `pets()`: hasMany `Pet`
- accessor:
  - `avatar_url`
  - `full_name`
- فیلدهای مهم: `mobile`, `otp_code`, `first_name`, `last_name`, `email`, `avatar`, `birth_date`, `national_code`, `gender_type`, `bio`, `activity_status`

### Provider

فایل: `app/Models/Provider.php`

- Auth با Sanctum
- روابط:
  - `documents()`: hasMany `ProviderDocument`
  - `businesses()`: hasMany `Business`
  - `province()`, `city()`
- فیلدهای مهم: اطلاعات هویتی، `mobile`, `password`, `two_factor_status`, `two_factor_code`, وضعیت تأیید

### Business

فایل: `app/Models/Business.php`

- SoftDeletes
- روابط:
  - `provider()`
  - `province()`, `city()`
  - `services()`: belongsToMany `Service` از جدول `business_services`
  - `products()`: hasMany `Product`
- accessor:
  - `logo_url`
  - `cover_url`
- فیلدهای مهم: نوع کسب‌وکار، نام، مجوز، لوگو/کاور، تماس، موقعیت، اطلاعات بانکی، وضعیت

### Pet

فایل: `app/Models/Pet.php`

- روابط:
  - `user()`
  - `species()`
  - `breed()`
- فیلدهای مهم: `name`, `gender_type`, `birth_date`, `weight`, `color`, `avatar`, `medical_records`, `settings`, `bio`

### Species و Breed

فایل‌ها:

- `app/Models/Species.php`
- `app/Models/Breed.php`

برای نوع و نژاد حیوانات استفاده می‌شوند. Breed به Species تعلق دارد.

### Service و BusinessService

فایل‌ها:

- `app/Models/Service.php`
- `app/Models/BusinessService.php`

Service خدمات پایه را نگه می‌دارد. جدول pivot `business_services` قیمت، مدت، تنظیمات و وضعیت سرویس را برای هر business نگه می‌دارد.

### Product, Category, Brand, ProductImage

فایل‌ها:

- `app/Models/Product.php`
- `app/Models/Category.php`
- `app/Models/Brand.php`
- `app/Models/ProductImage.php`

Product به Business و Brand تعلق دارد، چند Category دارد و چند تصویر دارد. `slug` برای route key استفاده می‌شود. `Category` ساختار parent/children دارد. `Brand` و `Category` هم auto slug دارند.

### Appointment

فایل: `app/Models/Appointment.php`

- Trait `BelongsToBusiness` دارد.
- روابط:
  - `business()`
  - `user()`
  - `pet()`
  - `service()`
- فیلدهای مهم: `business_id`, `user_id`, `service_id`, `pet_id`, `date`, `start_time`, `end_time`, `service_duration`, `service_price`, `status`, `payment_status`, `note`

## سیستم رزرو نوبت

فایل: `app/Services/AppointmentService.php`

مسئولیت‌ها:

- تبدیل روز هفته به مدل ایرانی/شمسی پروژه
- بررسی تعطیلی business در `business_off_days`
- دریافت schedule فعال از `business_schedules`
- تولید slot بر اساس `slot_duration`
- حذف breakها از slotها
- حذف slotهای رزروشده بر اساس capacity
- بررسی امکان رزرو و ایجاد appointment

جداول مرتبط:

- `business_schedules`
- `schedule_breaks`
- `business_off_days`
- `appointments`

## پنل ادمین Filament

Provider پنل: `app/Providers/Filament/AdminPanelProvider.php`

- مسیر پنل: `/admin`
- guard: `admin`
- نام برند: `هاپ هاپ`
- فونت: IRANSans local
- Filament Shield فعال است.

Resourceهای اصلی:

- Admins
- Users
- Providers
- Businesses
- Species
- Breeds
- Pets
- Services
- Categories
- Brands

## Response Format

Helper: `app/Helpers/Api/ApiResponse.php`

موفق:

```json
{
  "status": true,
  "message": "...",
  "data": {}
}
```

خطا:

```json
{
  "status": false,
  "message": "...",
  "errors": {}
}
```

## Enumهای مهم

مسیر: `app/Enums`

- `ActivityStatus`: فعال/غیرفعال
- `AppointmentStatuses`: pending, confirmed, cancelled, completed
- `BusinessTypes`: clinic, barber, shopping, pension
- `CategoryTypes`: product, service, blog
- `DaysOfWeek`: شنبه تا جمعه با مقادیر `0` تا `6`
- `GenderType`
- `PublicationStatus`
- `VerificationStatuses`
- `VerificationDocumentType`
- `FileTypes`

## دیتابیس و جداول مهم

Migrationها در `database/migrations` هستند.

جداول اصلی:

- `users`
- `providers`
- `provider_documents`
- `businesses`
- `admins`
- `species`
- `breeds`
- `pets`
- `services`
- `business_services`
- `user_addresses`
- `categories`
- `brands`
- `products`
- `category_products`
- `product_images`
- `business_schedules`
- `schedule_breaks`
- `business_off_days`
- `appointments`
- جداول permission از Spatie
- `personal_access_tokens` از Sanctum

Seederهای مهم:

- `DatabaseSeeder`
- `AdminSeeder`
- `UserSeeder`
- `SpeciesSeeder`
- `BreedsSeeder`
- `PetSeeder`
- `StateCitySeeder`

## نکات مهم برای AI هنگام تحلیل/توسعه

- این پروژه API-first است و پنل Filament فقط برای ادمین/مدیریت داده استفاده می‌شود.
- برای APIهای provider همیشه باید context کسب‌وکار از `X-Business-Id` resolve شود.
- مدل‌هایی که `BelongsToBusiness` دارند، تحت global scope کسب‌وکار جاری قرار می‌گیرند؛ در job/console/test اگر business bind نشده باشد scope اعمال نمی‌شود.
- فایل‌های route پیش‌فرض Laravel مثل `routes/web.php` در bootstrap ثبت نشده‌اند؛ پروژه routeهای سفارشی را در `bootstrap/app.php` bind کرده است.
- خروجی APIها معمولاً با `ApiResponse::Success/Fail` برمی‌گردند؛ در کد هم `Success` و هم `success` دیده می‌شود. PHP case-insensitive است، ولی برای خوانایی بهتر یکدست‌سازی توصیه می‌شود.
- تاریخ‌ها در UI/منطق ممکن است شمسی باشند، اما دیتابیس عمدتاً تاریخ میلادی/Carbon نگه می‌دارد.

## ریسک‌ها و ناسازگاری‌های قابل بررسی

این‌ها الزاماً bug قطعی نیستند، ولی برای تحلیل بعدی مهم‌اند:

- در `AppointmentController::availableSlots` متد `getAvailableSlots` با امضای اشتباه صدا زده شده است: سرویس پاس داده می‌شود در حالی که متد فعلاً `string $date` و `?int $serviceDuration` می‌خواهد.
- در `AppointmentService::book` هم `getAvailableSlots($businessId, $service, $startsAt->toDateString())` با ترتیب/نوع پارامتر ناسازگار است.
- در `AppointmentController::index` از `orderByDesc('starts_at')` استفاده شده، ولی migration/model فیلدهای `start_time` و `end_time` دارند.
- در `AppointmentController::cancel` از `$appointment->starts_at` استفاده شده، اما مدل `start_time` دارد.
- در `validateAppointmentData` فیلد `note` validate نشده ولی در `book` اجباری پاس داده شده است.
- در `AppointmentService` چند جا `whereDate('start_time', $date)` استفاده شده؛ چون `start_time` در migration از نوع `time` است، date ندارد. اگر تاریخ جدا در ستون `date` است، باید queryها بر اساس `date` و `start_time` اصلاح شوند.
- در `getIranianDayOfWeek` کامنت و mapping با `Carbon::dayOfWeek` مشکوک است؛ Carbon مقدار `6` را برای Saturday می‌دهد ولی map فعلی `6 => 6` یعنی Friday در enum پروژه. احتمالاً mapping باید بازبینی شود.
- در migration `product_images` روی `['product_id','is_primary']` unique گذاشته شده؛ این باعث می‌شود هر محصول فقط یک تصویر `false` و یک تصویر `true` بتواند داشته باشد. برای چند تصویر غیراصلی احتمالاً باید unique شرطی یا منطق متفاوت استفاده شود.
- در Resourceهایی مثل `AddressResource` برای province/city داخل resource query مستقیم اجرا می‌شود که می‌تواند N+1 ایجاد کند.
- `routes/user/web.php` و `routes/user/console.php` نام‌گذاری غیرمعمول دارند و در bootstrap فعلاً route نشده‌اند.

## فایل‌های مهم برای شروع تحلیل عمیق

- `bootstrap/app.php`
- `config/auth.php`
- `routes/user/api_v1.php`
- `routes/user/auth_v1.php`
- `routes/provider/api_v1.php`
- `routes/provider/auth_v1.php`
- `app/Http/Middleware/ResolveBusiness.php`
- `app/Models/Traits/BelongsToBusiness.php`
- `app/Models/Scopes/BusinessScope.php`
- `app/Services/AppointmentService.php`
- `app/Http/Controllers/User/Api/V1/AppointmentController.php`
- `app/Http/Controllers/Provider/Api/V1/ProductController.php`
- `app/Http/Controllers/User/Api/V1/AuthController.php`
- `app/Http/Controllers/Provider/Api/V1/AuthController.php`
- `app/Providers/Filament/AdminPanelProvider.php`
- `database/migrations`

