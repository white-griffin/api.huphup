# کانتکست قابل ارائه به AI برای پروژه `api.huphup`

این فایل برای کپی‌کردن در ابتدای گفتگو با AI ساخته شده است تا مدل بتواند بدون بررسی کامل ریپو، معماری، دامنه، APIها و نکات حساس پروژه را بفهمد.

## خلاصه پروژه

- پروژه یک بک‌اند API با Laravel 11 و PHP 8.2 برای پلتفرم خدمات حیوانات خانگی «هاپ‌هاپ» است.
- دامنه اصلی شامل کاربران، حیوانات خانگی، providerها، کسب‌وکارها، خدمات، رزرو نوبت، محصولات، دسته‌بندی‌ها، برندها و چت کاربری است.
- احراز هویت API با Laravel Sanctum انجام می‌شود.
- پنل مدیریت با Filament 4 روی مسیر `/admin` پیاده‌سازی شده و از guard جداگانه `admin` استفاده می‌کند.
- زبان پیام‌ها و بخش زیادی از نام‌گذاری دامنه فارسی است، اما ساختار کد بر اساس Laravel/Eloquent استاندارد است.

## تکنولوژی‌ها و وابستگی‌ها

- PHP `^8.2`
- Laravel Framework `^11.0`
- Laravel Sanctum برای token API
- Laravel Reverb و broadcasting برای چت real-time
- Filament `^4.0` برای پنل ادمین
- BezhanSalleh Filament Shield و Spatie Laravel Permission برای نقش و مجوز
- `ipe/smsir-php` برای ارسال OTP از SmsIr
- `morilog/jalali` و `hekmatinasser/verta` برای تاریخ شمسی
- Vite، Tailwind CSS و Axios در بخش assets

فایل‌های مرجع:

- `composer.json`
- `package.json`
- `bootstrap/app.php`
- `config/auth.php`
- `config/reverb.php`
- `config/sanctum.php`

## ساختار مهم پوشه‌ها

- `app/Http/Controllers/User/Api/V1`: کنترلرهای API کاربر
- `app/Http/Controllers/Provider/Api/V1`: کنترلرهای API provider
- `app/Http/Resources/V1`: resourceهای خروجی API
- `app/Models`: مدل‌های Eloquent
- `app/Services`: سرویس‌های دامنه مثل رزرو و فایل
- `app/Enums`: enumهای وضعیت‌ها و نوع‌ها
- `app/Filament/Resources`: resourceهای پنل ادمین
- `database/migrations`: schema دیتابیس
- `database/seeders`: seed اولیه کاربران، ادمین، شهرها، گونه‌ها و نژادها
- `routes/user`: routeهای API کاربر
- `routes/provider`: routeهای API provider

## Routing

در Laravel 11، routeها در `bootstrap/app.php` ثبت شده‌اند و route پیش‌فرض `routes/web.php` نقشی در API ندارد.

- `api/v1/user` → `routes/user/api_v1.php`
- `api/v1/user/auth` → `routes/user/auth_v1.php`
- `api/v1/provider` → `routes/provider/api_v1.php`
- `api/v1/provider/auth` → `routes/provider/auth_v1.php`
- broadcasting channels از `routes/channels.php`

Routeهای provider به‌جز auth با middlewareهای زیر محافظت می‌شوند:

- `api`
- `auth:provider`
- `resolve.business`
- `scopeBindings`

برای APIهای provider ارسال هدر `X-Business-Id` ضروری است.

## احراز هویت و Guardها

در `config/auth.php` سه guard اصلی وجود دارد:

- `web`: session برای `users`
- `admin`: session برای `admins`
- `provider`: Sanctum برای `providers`

### User Auth

کنترلر: `app/Http/Controllers/User/Api/V1/AuthController.php`

- `POST /api/v1/user/auth/login`
  - ورودی: `mobile`
  - اگر کاربر وجود نداشته باشد ساخته می‌شود.
  - در production کد OTP تصادفی ارسال می‌شود؛ در محیط غیر production کد `111111` است.
  - tokenهای قبلی کاربر حذف می‌شوند.
- `POST /api/v1/user/auth/check_code`
  - ورودی: `mobile`, `otp_code`
  - در صورت صحت کد، کاربر فعال می‌شود و Sanctum token برمی‌گردد.
- `GET /api/v1/user/auth/logout`
  - نیازمند `auth:sanctum`
  - همه tokenهای کاربر را حذف می‌کند.

### Provider Auth

کنترلر: `app/Http/Controllers/Provider/Api/V1/AuthController.php`

- `POST /api/v1/provider/auth/login`
  - ورودی: `mobile`, `password`
  - Rate limit: سه تلاش و قفل پنج‌دقیقه‌ای
  - اگر `two_factor_status` فعال باشد، OTP دو دقیقه‌ای ارسال می‌شود.
  - اگر 2FA فعال نباشد token مستقیم برمی‌گردد.
- `POST /api/v1/provider/auth/check_code`
  - تأیید 2FA با rate limit جداگانه
- `GET /api/v1/provider/auth/logout`
  - نیازمند `auth:provider`

## Context کسب‌وکار برای Provider

فایل‌ها:

- `app/Http/Middleware/ResolveBusiness.php`
- `app/Support/helpers.php`
- `app/Models/Traits/BelongsToBusiness.php`
- `app/Models/Scopes/BusinessScope.php`

رفتار:

- middleware از guard `provider` کاربر جاری را می‌گیرد.
- هدر `X-Business-Id` اجباری است.
- بررسی می‌شود business متعلق به provider باشد.
- business جاری در container با کلید `business` bind می‌شود.
- helper `business()` همان business جاری را برمی‌گرداند.
- مدل‌هایی که trait `BelongsToBusiness` دارند در زمان create مقدار `business_id` را از context می‌گیرند و queryها با `BusinessScope` محدود می‌شوند.

## APIهای User

همه مسیرهای نیازمند token از `auth:sanctum` استفاده می‌کنند.

### Profile و Address

کنترلر: `app/Http/Controllers/User/Api/V1/ProfileController.php`

- `GET /api/v1/user/profile`
- `POST /api/v1/user/profile`
- `POST /api/v1/user/address`
- `POST /api/v1/user/address/{address}`
- `DELETE /api/v1/user/address/{address}`

پروفایل از `MediaService` برای آپلود/جایگزینی avatar استفاده می‌کند.

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

برای تصویر pet از `MediaService` و مسیر `pet/avatars` استفاده می‌شود.

### Appointments

کنترلر: `app/Http/Controllers/User/Api/V1/AppointmentController.php`
سرویس: `app/Services/AppointmentService.php`

- `GET /api/v1/user/appointments/{businessId}/available-slots`
  - ورودی: `service_id`, `date`
- `GET /api/v1/user/appointments`
- `POST /api/v1/user/appointments`
  - ورودی: `business_id`, `service_id`, `pet_id`, `starts_at`, `note?`
- `GET /api/v1/user/appointments/cancel/{appointment}`

منطق رزرو:

- duration و price سرویس از pivot جدول `business_services` خوانده می‌شود.
- slotها بر اساس schedule کسب‌وکار، breakها، off-dayها و capacity محاسبه می‌شوند.
- appointment شامل `date`, `start_time`, `end_time`, `service_duration`, `service_price`, `status` است.

### Chat

کنترلرها:

- `app/Http/Controllers/User/Api/V1/Chat/ConversationController.php`
- `app/Http/Controllers/User/Api/V1/Chat/MessageController.php`

مسیرها:

- `GET /api/v1/user/chat/conversations`
- `POST /api/v1/user/chat/conversations`
- `GET /api/v1/user/chat/conversations/{conversation}`
- `DELETE /api/v1/user/chat/conversations/{conversation}`
- `GET /api/v1/user/chat/conversations/{conversation}/messages`
- `POST /api/v1/user/chat/conversations/{conversation}/messages`
- `POST /api/v1/user/chat/conversations/{conversation}/read`

چت private بین کاربران است. ارسال پیام event `App\Events\User\MessageSent` را broadcast می‌کند. channel خصوصی `conversation.{conversationId}` در `routes/channels.php` فقط برای participantها مجاز است.

## APIهای Provider

همه مسیرهای زیر نیازمند token provider و هدر `X-Business-Id` هستند.

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

نکته‌ها:

- route key محصول `slug` است.
- categoryها با slug دریافت و به id تبدیل می‌شوند.
- تصاویر در `product/images` ذخیره می‌شوند.
- تصویر اول در نبود تصویر primary، به‌عنوان primary ثبت می‌شود.

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

متد `upsert` برنامه هفتگی را بر اساس `business_id` و `day_of_week` ایجاد/به‌روزرسانی می‌کند.

### Off Days

کنترلر: `app/Http/Controllers/Provider/Api/V1/BusinessOffDayController.php`

- `GET /api/v1/provider/off-days`
- `POST /api/v1/provider/off-days`
- `DELETE /api/v1/provider/off-days/{offDay}`

## مدل‌های اصلی

### User

فایل: `app/Models/User.php`

- Sanctum token دارد.
- روابط: `addresses()`, `pets()`, `conversations()`, `messages()`
- accessorها: `avatar_url`, `full_name`
- فیلدهای مهم: `mobile`, `otp_code`, `first_name`, `last_name`, `email`, `avatar`, `birth_date`, `national_code`, `gender_type`, `bio`, `activity_status`

### Provider

فایل: `app/Models/Provider.php`

- Sanctum token دارد.
- روابط: `documents()`, `businesses()`, `province()`, `city()`
- accessor: `full_name`
- فیلدهای مهم: اطلاعات هویتی، `mobile`, `password`, `two_factor_status`, `two_factor_code`, وضعیت تأیید

### Business

فایل: `app/Models/Business.php`

- SoftDeletes دارد.
- روابط: `provider()`, `province()`, `city()`, `services()`, `products()`
- accessorها: `logo_url`, `cover_url`
- pivot `services()` از جدول `business_services` فیلدهای `price`, `duration`, `settings`, `activity_status` دارد.

### Pet

فایل: `app/Models/Pet.php`

- روابط: `user()`, `species()`, `breed()`
- فیلدهای مهم: `species_id`, `breed_id`, `name`, `gender_type`, `birth_date`, `weight`, `color`, `avatar`, `medical_records`, `settings`, `bio`

### Species و Breed

فایل‌ها:

- `app/Models/Species.php`
- `app/Models/Breed.php`

برای نوع و نژاد حیوانات استفاده می‌شوند. Breed به Species تعلق دارد.

### Service و BusinessService

فایل‌ها:

- `app/Models/Service.php`
- `app/Models/BusinessService.php`

`Service` خدمات پایه را نگه می‌دارد. `BusinessService` pivot بین business و service است و قیمت، مدت، تنظیمات و وضعیت سرویس را نگه می‌دارد.

### Product, Category, Brand, ProductImage

فایل‌ها:

- `app/Models/Product.php`
- `app/Models/Category.php`
- `app/Models/Brand.php`
- `app/Models/ProductImage.php`

Product به Business و Brand تعلق دارد، چند Category دارد و چند تصویر دارد. Product، Category و Brand با slug کار می‌کنند. Category ساختار parent/children دارد.

### Appointment

فایل: `app/Models/Appointment.php`

- trait `BelongsToBusiness` دارد.
- روابط: `business()`, `user()`, `pet()`, `service()`
- castها: `date`, `start_time`, `end_time`
- فیلدهای مهم: `business_id`, `user_id`, `service_id`, `pet_id`, `date`, `start_time`, `end_time`, `service_duration`, `service_price`, `status`, `payment_status`, `notes`

### Conversation و Message

فایل‌ها:

- `app/Models/Conversation.php`
- `app/Models/Message.php`
- `app/Models/ConversationParticipant.php`

Conversation با Userها رابطه many-to-many از جدول `conversation_participants` دارد. Message به Conversation و sender تعلق دارد و `read_at` دارد.

## سیستم رزرو نوبت

فایل: `app/Services/AppointmentService.php`

مسئولیت‌ها:

- تبدیل روز هفته Carbon به روز هفته ایرانی پروژه
- بررسی تعطیلی کسب‌وکار در `business_off_days`
- دریافت schedule فعال از `business_schedules`
- تولید slot بر اساس `slot_duration`
- حذف بازه‌های break از slotها
- حذف slotهای پرشده بر اساس capacity
- بررسی امکان رزرو با `canBook`
- ایجاد appointment با `book`

جداول مرتبط:

- `business_schedules`
- `schedule_breaks`
- `business_off_days`
- `appointments`
- `business_services`

## پنل ادمین Filament

Provider پنل: `app/Providers/Filament/AdminPanelProvider.php`

- مسیر پنل: `/admin`
- guard: `admin`
- برند: `هاپ هاپ`
- فونت local IRANSans
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

در کد هم `ApiResponse::Success` و هم `ApiResponse::success` دیده می‌شود. PHP نام متدها را case-insensitive پردازش می‌کند، اما بهتر است برای خوانایی یکدست شود.

## Enumهای مهم

مسیر: `app/Enums`

- `AccessStatuses`: وضعیت/نوع دسترسی مثل private
- `ActivityStatus`: فعال/غیرفعال
- `AppointmentStatuses`: وضعیت رزرو
- `BusinessTypes`: clinic, barber, shopping, pension
- `CategoryTypes`: product, service, blog
- `DaysOfWeek`: شنبه تا جمعه
- `GenderType`
- `MessageTypes`: نوع پیام
- `PublicationStatus`
- `VerificationStatuses`
- `VerificationDocumentType`
- `FileTypes`, `FolderNames`, `FileAddresses`

## دیتابیس و جداول مهم

Migrationها در `database/migrations` هستند.

جداول دامنه:

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
- `conversations`
- `conversation_participants`
- `messages`

جداول زیرساخت:

- `personal_access_tokens`
- `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions`
- `jobs`, `job_batches`, `failed_jobs`
- `cache`, `cache_locks`, `sessions`
- `countries`, `provinces`, `cities`

Seederهای مهم:

- `DatabaseSeeder`
- `AdminSeeder`
- `UserSeeder`
- `SpeciesSeeder`
- `BreedsSeeder`
- `PetSeeder`
- `StateCitySeeder`

## نکات توسعه برای AI

- این پروژه API-first است؛ پنل Filament برای مدیریت داده و ادمین است.
- برای تغییر API ابتدا route، controller، resource، model، migration و enum مرتبط را همزمان بررسی کن.
- در APIهای provider همیشه context کسب‌وکار از `X-Business-Id` مهم است.
- اگر مدلی trait `BelongsToBusiness` دارد، queryهای آن در context provider ممکن است با `BusinessScope` محدود شوند.
- در job، console و test اگر business در container bind نشده باشد، scope کسب‌وکار ممکن است اعمال نشود.
- خروجی API را با `ApiResponse` هماهنگ نگه دار.
- برای فایل‌ها از `MediaService` و disk `public` استفاده می‌شود.
- تاریخ‌های UI ممکن است شمسی باشند، اما منطق دیتابیس عمدتاً با Carbon و تاریخ میلادی کار می‌کند.
- اگر validation فارسی وجود دارد، پیام‌های جدید را هم فارسی و هم‌سبک نگه دار.

## موارد قابل بررسی و ریسک‌های احتمالی

این‌ها الزاماً bug قطعی نیستند، ولی برای توسعه بعدی باید در نظر گرفته شوند:

- `ScheduleController::validateScheduleData` از rule `after:*.start_time` استفاده کرده که ممکن است در validation آرایه‌ای Laravel درست کار نکند و نیاز به بازبینی داشته باشد.
- `AppointmentService::getIranianDayOfWeek` و enum `DaysOfWeek` باید با مقادیر دیتابیس scheduleها تطبیق داده شوند؛ هر ناسازگاری باعث پیدا نشدن schedule می‌شود.
- `Appointment::casts` برای `start_time` و `end_time` از `datetime` استفاده می‌کند، در حالی که migration احتمالاً نوع `time` دارد؛ این موضوع در format و parse باید تست شود.
- در `AppointmentService::book` فیلد `notes` ذخیره می‌شود؛ migration/model باید همین نام را داشته باشد، نه `note`.
- در migration `product_images` اگر unique روی `product_id` و `is_primary` باشد، هر محصول فقط یک تصویر غیر primary می‌تواند داشته باشد؛ برای چند تصویر باید schema بازبینی شود.
- در بعضی resourceها احتمال N+1 query وجود دارد؛ مخصوصاً اگر داخل resource برای province/city query مستقیم انجام شده باشد.
- در `ProfileController::deleteAddress` پارامتر `$address` type-hint نشده، اما route model binding مورد انتظار به نظر می‌رسد؛ این مورد باید بررسی شود.
- دسترسی مالکیت در برخی route model bindingهای user مثل pet/address باید کنترل شود تا کاربر به داده کاربر دیگر دسترسی نداشته باشد.

## فایل‌های شروع برای تحلیل عمیق

- `bootstrap/app.php`
- `config/auth.php`
- `routes/user/api_v1.php`
- `routes/user/auth_v1.php`
- `routes/provider/api_v1.php`
- `routes/provider/auth_v1.php`
- `routes/channels.php`
- `app/Http/Middleware/ResolveBusiness.php`
- `app/Models/Traits/BelongsToBusiness.php`
- `app/Models/Scopes/BusinessScope.php`
- `app/Services/AppointmentService.php`
- `app/Services/MediaService.php`
- `app/Http/Controllers/User/Api/V1/AppointmentController.php`
- `app/Http/Controllers/User/Api/V1/Chat/ConversationController.php`
- `app/Http/Controllers/User/Api/V1/Chat/MessageController.php`
- `app/Http/Controllers/Provider/Api/V1/ProductController.php`
- `app/Http/Controllers/User/Api/V1/AuthController.php`
- `app/Http/Controllers/Provider/Api/V1/AuthController.php`
- `app/Providers/Filament/AdminPanelProvider.php`
- `database/migrations`
