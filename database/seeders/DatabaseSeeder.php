<?php

namespace Database\Seeders;

use App\Enums\AccessStatuses;
use App\Enums\ActivityStatus;
use App\Enums\AppointmentStatuses;
use App\Enums\BusinessTypes;
use App\Enums\CategoryTypes;
use App\Enums\GenderType;
use App\Enums\MessageTypes;
use App\Enums\Priorities;
use App\Enums\PublicationStatus;
use App\Enums\RoutineCategoryTypes;
use App\Enums\RoutineStatuses;
use App\Enums\VerificationDocumentType;
use App\Enums\VerificationStatuses;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            $this->truncateApplicationTables();
        $this->call(StateCitySeeder::class);

        $now = now();
        $this->seedCountries();
        $password = Hash::make('password');

        $this->seedAdmins($now, $password);
        $this->seedUsers($now, $password);
        $this->seedProviders($now, $password);
        $this->seedProviderDocuments($now);
        $this->seedBusinesses($now);
        $this->seedServices($now);
        $this->seedBusinessServices($now);
        $this->seedSpecies($now);
        $this->seedBreeds($now);
        $this->seedPets($now);
        $this->seedUserAddresses($now);
        $this->seedCategories($now);
        $this->seedBrands($now);
        $this->seedAttributes($now);
        $this->seedAttributeOptions($now);
        $this->seedProducts($now);
        $this->seedCategoryProducts($now);
        $this->seedProductVariations($now);
        $this->seedProductVariationAttributes($now);
        $this->seedProductImages($now);
        $this->seedBusinessSchedules($now);
        $this->seedScheduleBreaks($now);
        $this->seedBusinessOffDays($now);
        $this->seedAppointments($now);
        $this->seedConversations($now);
        $this->seedConversationParticipants($now);
        $this->seedMessages($now);
        $this->seedRoutineTemplates($now);
        $this->seedPetRoutines($now);
        $this->seedRoutineActions($now);

            $this->seedPermissions($now);
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    private function truncateApplicationTables(): void
    {
        foreach ([
            'role_has_permissions', 'model_has_roles', 'model_has_permissions',
            'routine_actions', 'pet_routines', 'routine_templates',
            'messages', 'conversation_participants', 'conversations',
            'appointments', 'business_off_days', 'schedule_breaks', 'business_schedules',
            'product_variation_attributes', 'product_images', 'product_variations', 'category_products', 'products',
            'attribute_options', 'attributes', 'brands', 'categories',
            'user_addresses', 'pets', 'breeds', 'species',
            'business_services', 'services', 'businesses', 'provider_documents', 'providers',
            'roles', 'permissions',
            'admins', 'users', 'cities', 'provinces', 'countries',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
    }

    private function timestamps($now): array
    {
        return ['created_at' => $now, 'updated_at' => $now];
    }

    private function seedCountries(): void
    {
        if (DB::table('countries')->where('id', 1)->doesntExist()) {
            DB::table('countries')->insert([
                ['id' => 1, 'capital_city' => 87, 'name' => 'ایران', 'name_en' => 'Iran'],
            ]);
        }

        DB::table('countries')->insertOrIgnore([
            ['id' => 2, 'capital_city' => null, 'name' => 'ترکیه', 'name_en' => 'Turkey'],
            ['id' => 3, 'capital_city' => null, 'name' => 'امارات متحده عربی', 'name_en' => 'United Arab Emirates'],
            ['id' => 4, 'capital_city' => null, 'name' => 'عراق', 'name_en' => 'Iraq'],
            ['id' => 5, 'capital_city' => null, 'name' => 'افغانستان', 'name_en' => 'Afghanistan'],
            ['id' => 6, 'capital_city' => null, 'name' => 'پاکستان', 'name_en' => 'Pakistan'],
            ['id' => 7, 'capital_city' => null, 'name' => 'آذربایجان', 'name_en' => 'Azerbaijan'],
            ['id' => 8, 'capital_city' => null, 'name' => 'ارمنستان', 'name_en' => 'Armenia'],
            ['id' => 9, 'capital_city' => null, 'name' => 'قطر', 'name_en' => 'Qatar'],
            ['id' => 10, 'capital_city' => null, 'name' => 'عمان', 'name_en' => 'Oman'],
        ]);
    }

    private function seedPermissions($now): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles')) {
            return;
        }

        $permissions = [
            'view-users', 'create-users', 'update-users', 'delete-users', 'view-providers',
            'verify-providers', 'view-businesses', 'manage-products', 'manage-services', 'manage-appointments',
        ];
        $permissionRows = [];
        foreach ($permissions as $index => $permission) {
            $permissionRows[] = [
                'id' => $index + 1,
                'name' => $permission,
                'guard_name' => 'admin',
                ...$this->timestamps($now),
            ];
        }

        $roles = [
            'super-admin', 'admin', 'support', 'provider-manager', 'content-manager',
            'product-manager', 'service-manager', 'appointment-manager', 'finance-manager', 'viewer',
        ];
        $roleRows = [];
        foreach ($roles as $index => $role) {
            $roleRows[] = [
                'id' => $index + 1,
                'name' => $role,
                'guard_name' => 'admin',
                ...$this->timestamps($now),
            ];
        }

        $rolePermissionRows = [];
        for ($i = 1; $i <= 10; $i++) {
            $rolePermissionRows[] = [
                'permission_id' => $i,
                'role_id' => (($i - 1) % 10) + 1,
            ];
        }

        $modelRoleRows = [];
        $modelPermissionRows = [];
        for ($i = 1; $i <= 10; $i++) {
            $modelRoleRows[] = [
                'role_id' => $i,
                'model_type' => 'App\\Models\\Admin',
                'model_id' => $i,
            ];
            $modelPermissionRows[] = [
                'permission_id' => $i,
                'model_type' => 'App\\Models\\Admin',
                'model_id' => $i,
            ];
        }

        DB::table('permissions')->insert($permissionRows);
        DB::table('roles')->insert($roleRows);

        if (Schema::hasTable('role_has_permissions')) {
            DB::table('role_has_permissions')->insert($rolePermissionRows);
        }

        if (Schema::hasTable('model_has_roles')) {
            DB::table('model_has_roles')->insert($modelRoleRows);
        }

        if (Schema::hasTable('model_has_permissions')) {
            DB::table('model_has_permissions')->insert($modelPermissionRows);
        }
    }

    private function seedAdmins($now, string $password): void
    {
        $rows = [];
        for ($i = 1; $i <= 10; $i++) {
            $rows[] = [
                'id' => $i,
                'first_name' => ['علی', 'سارا', 'رضا', 'مریم', 'حسین', 'نرگس', 'محمد', 'زهرا', 'امیر', 'نگار'][$i - 1],
                'last_name' => ['رضایی', 'محمدی', 'احمدی', 'کریمی', 'حسینی', 'مرادی', 'صادقی', 'کاظمی', 'جعفری', 'اکبری'][$i - 1],
                'mobile' => '091000000' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'email' => "admin{$i}@huphup.test",
                'username' => "admin{$i}",
                'password' => $password,
                'avatar' => null,
                'activity_status' => ActivityStatus::ACTIVE->value,
                ...$this->timestamps($now),
            ];
        }

        DB::table('admins')->insert($rows);
    }

    private function seedUsers($now, string $password): void
    {
        $rows = [];
        for ($i = 1; $i <= 10; $i++) {
            $rows[] = [
                'id' => $i,
                'first_name' => ['محمد', 'فاطمه', 'آراد', 'رها', 'کیان', 'آوا', 'سامان', 'هستی', 'پارسا', 'ترانه'][$i - 1],
                'last_name' => ['رحیمی', 'یوسفی', 'نعمتی', 'عباسی', 'امینی', 'قاسمی', 'حیدری', 'سلطانی', 'نوری', 'زارعی'][$i - 1],
                'email' => "user{$i}@huphup.test",
                'email_verified_at' => $now,
                'mobile' => '092000000' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'otp_code' => null,
                'password' => $password,
                'avatar' => null,
                'birth_date' => now()->subYears(20 + $i)->toDateString(),
                'national_code' => '00876543' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'bio' => 'کاربر نمونه علاقه‌مند به حیوانات خانگی.',
                'latitude' => 35.7000000 + ($i / 1000),
                'longitude' => 51.4000000 + ($i / 1000),
                'gender_type' => $i % 2 === 0 ? GenderType::FEMALE->value : GenderType::MALE->value,
                'activity_status' => ActivityStatus::ACTIVE->value,
                'remember_token' => null,
                ...$this->timestamps($now),
            ];
        }

        DB::table('users')->insert($rows);
    }

    private function seedProviders($now, string $password): void
    {
        $rows = [];
        for ($i = 1; $i <= 10; $i++) {
            $rows[] = [
                'id' => $i,
                'first_name' => ['علی', 'سارا', 'بهزاد', 'مینا', 'کیوان', 'الهام', 'فرهاد', 'شقایق', 'رامین', 'پریسا'][$i - 1],
                'last_name' => ['رضایی', 'محمدی', 'کریمی', 'حسینی', 'مرادی', 'نوری', 'جعفری', 'صادقی', 'امیری', 'کاظمی'][$i - 1],
                'national_code' => '00123456' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'father_name' => ['حسن', 'رضا', 'اکبر', 'محمود', 'ناصر', 'حمید', 'مسعود', 'حسین', 'جواد', 'کریم'][$i - 1],
                'birth_date' => now()->subYears(28 + $i)->toDateString(),
                'gender_type' => $i % 2 === 0 ? GenderType::FEMALE->value : GenderType::MALE->value,
                'mobile' => '093000000' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'mobile_verified_at' => $now,
                'email' => "provider{$i}@huphup.test",
                'email_verified_at' => $now,
                'password' => $password,
                'two_factor_status' => ActivityStatus::INACTIVE->value,
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
                'remember_token' => null,
                'province_id' => $i,
                'city_id' => $i,
                'postal_code' => '12345678' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'address' => "آدرس نمونه ارائه‌دهنده {$i}",
                'shahkar_verified' => true,
                'verification_status' => VerificationStatuses::ACTIVE->value,
                'verified_at' => $now,
                'rejection_reason' => null,
                ...$this->timestamps($now),
            ];
        }

        DB::table('providers')->insert($rows);
    }

    private function seedProviderDocuments($now): void
    {
        $types = [
            VerificationDocumentType::NATIONAL_CARD_FRONT->value,
            VerificationDocumentType::NATIONAL_CARD_BACK->value,
            VerificationDocumentType::SELFIE->value,
            VerificationDocumentType::VERIFICATION_VIDEO->value,
        ];
        $rows = [];
        for ($i = 1; $i <= 10; $i++) {
            $rows[] = [
                'id' => $i,
                'provider_id' => $i,
                'document_type' => $types[($i - 1) % count($types)],
                'name' => "provider-doc-{$i}.jpg",
                'mime_type' => 'image/jpeg',
                'size' => 240000 + ($i * 1000),
                'verification_status' => VerificationStatuses::ACTIVE->value,
                ...$this->timestamps($now),
            ];
        }

        DB::table('provider_documents')->insert($rows);
    }

    private function seedBusinesses($now): void
    {
        $types = [BusinessTypes::CLINIC->value, BusinessTypes::BARBER->value, BusinessTypes::SHOPPING->value, BusinessTypes::PENSION->value];
        $names = ['کلینیک مهر', 'آرایشگاه پنجه', 'پت شاپ نارنج', 'پانسیون سبز', 'کلینیک آریا', 'آرایشگاه باران', 'پت شاپ آبی', 'پانسیون آرام', 'کلینیک نگین', 'پت شاپ سپید'];
        $rows = [];
        for ($i = 1; $i <= 10; $i++) {
            $rows[] = [
                'id' => $i,
                'provider_id' => $i,
                'business_type' => $types[($i - 1) % count($types)],
                'name' => $names[$i - 1],
                'license_code' => 'LIC-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'description' => 'کسب‌وکار نمونه برای خدمات و محصولات حیوانات خانگی.',
                'logo' => null,
                'cover_image' => null,
                'phone' => '021100000' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'email' => "business{$i}@huphup.test",
                'website' => "https://business{$i}.huphup.test",
                'province_id' => $i,
                'city_id' => $i,
                'address' => "خیابان نمونه، پلاک {$i}",
                'postal_code' => '19876543' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'latitude' => 35.7100000 + ($i / 1000),
                'longitude' => 51.4100000 + ($i / 1000),
                'bank_name' => 'بانک نمونه',
                'bank_account_holder' => $names[$i - 1],
                'bank_card' => '603799' . str_pad((string) $i, 10, '0', STR_PAD_LEFT),
                'bank_iban' => str_pad((string) $i, 26, '1', STR_PAD_LEFT),
                'settings' => json_encode(['online_booking' => true, 'commission_rate' => 10 + $i]),
                'verification_status' => VerificationStatuses::ACTIVE->value,
                'verified_at' => $now,
                'rejection_reason' => null,
                'activity_status' => ActivityStatus::ACTIVE->value,
                ...$this->timestamps($now),
            ];
        }

        DB::table('businesses')->insert($rows);
    }

    private function seedServices($now): void
    {
        $services = [
            ['معاینه عمومی', 'general-checkup'], ['واکسیناسیون', 'vaccination'], ['اصلاح و شستشو', 'grooming'], ['کوتاه کردن ناخن', 'nail-trim'], ['دندانپزشکی', 'dental-care'],
            ['سونوگرافی', 'ultrasound'], ['پانسیون روزانه', 'daycare'], ['آموزش مقدماتی', 'basic-training'], ['مشاوره تغذیه', 'nutrition-consult'], ['ویزیت اورژانسی', 'emergency-visit'],
        ];
        $rows = [];
        foreach ($services as $index => [$name, $slug]) {
            $rows[] = [
                'id' => $index + 1,
                'name' => $name,
                'name_en' => Str::headline($slug),
                'description' => "خدمت {$name} برای حیوانات خانگی.",
                'icon' => null,
                'image' => null,
                'activity_status' => ActivityStatus::ACTIVE->value,
                'sort_order' => $index + 1,
                ...$this->timestamps($now),
            ];
        }

        DB::table('services')->insert($rows);
    }

    private function seedBusinessServices($now): void
    {
        $rows = [];
        for ($i = 1; $i <= 10; $i++) {
            $rows[] = [
                'id' => $i,
                'business_id' => $i,
                'service_id' => $i,
                'price' => 150000 + ($i * 25000),
                'duration' => 30 + ($i * 5),
                'settings' => json_encode(['requires_pet_profile' => true]),
                'activity_status' => ActivityStatus::ACTIVE->value,
                ...$this->timestamps($now),
            ];
        }

        DB::table('business_services')->insert($rows);
    }

    private function seedSpecies($now): void
    {
        $species = [
            ['dog', 'سگ', '🐶'], ['cat', 'گربه', '🐱'], ['bird', 'پرنده', '🐦'], ['fish', 'ماهی', '🐟'], ['rabbit', 'خرگوش', '🐰'],
            ['hamster', 'همستر', '🐹'], ['turtle', 'لاک‌پشت', '🐢'], ['horse', 'اسب', '🐴'], ['guinea-pig', 'خوکچه هندی', '🐹'], ['reptile', 'خزنده', '🦎'],
        ];
        $rows = [];
        foreach ($species as $index => [$slug, $name, $icon]) {
            $rows[] = [
                'id' => $index + 1,
                'name_en' => Str::headline($slug),
                'name_fa' => $name,
                'slug' => $slug,
                'icon' => $icon,
                'image' => null,
                'activity_status' => ActivityStatus::ACTIVE->value,
                'order' => $index + 1,
                ...$this->timestamps($now),
            ];
        }

        DB::table('species')->insert($rows);
    }

    private function seedBreeds($now): void
    {
        $breeds = [
            [1, 'Golden Retriever', 'گلدن رتریور', 'golden-retriever'], [1, 'German Shepherd', 'ژرمن شپرد', 'german-shepherd'], [2, 'Persian', 'پرشین', 'persian'], [2, 'Maine Coon', 'مین‌کون', 'maine-coon'], [3, 'African Grey', 'کاسکو', 'african-grey'],
            [4, 'Goldfish', 'گلدفیش', 'goldfish'], [5, 'Dwarf Rabbit', 'خرگوش کوتوله', 'dwarf-rabbit'], [6, 'Syrian Hamster', 'همستر سوری', 'syrian-hamster'], [7, 'Red Eared Slider', 'لاک‌پشت گوش‌قرمز', 'red-eared-slider'], [8, 'Arabian Horse', 'اسب عرب', 'arabian-horse'],
        ];
        $rows = [];
        foreach ($breeds as $index => [$speciesId, $nameEn, $nameFa, $slug]) {
            $rows[] = [
                'id' => $index + 1,
                'species_id' => $speciesId,
                'name_en' => $nameEn,
                'name_fa' => $nameFa,
                'slug' => $slug,
                'description' => "نژاد نمونه {$nameFa}.",
                'image' => null,
                'characteristics' => json_encode(['temperament' => 'friendly', 'size' => 'medium']),
                'activity_status' => ActivityStatus::ACTIVE->value,
                ...$this->timestamps($now),
            ];
        }

        DB::table('breeds')->insert($rows);
    }

    private function seedPets($now): void
    {
        $names = ['رکس', 'لونا', 'مکس', 'میلو', 'نالا', 'کوکو', 'بانی', 'تامی', 'پرنس', 'برفی'];
        $rows = [];
        for ($i = 1; $i <= 10; $i++) {
            $rows[] = [
                'id' => $i,
                'user_id' => $i,
                'species_id' => $i,
                'breed_id' => $i,
                'name' => $names[$i - 1],
                'gender_type' => $i % 2 === 0 ? GenderType::FEMALE->value : GenderType::MALE->value,
                'birth_date' => now()->subMonths(12 + $i)->toDateString(),
                'weight' => 2 + ($i * 1.25),
                'color' => ['طلایی', 'سفید', 'مشکی', 'قهوه‌ای', 'طوسی'][$i % 5],
                'avatar' => null,
                'medical_records' => json_encode([['date' => '2026-01-01', 'note' => 'معاینه اولیه']]),
                'settings' => json_encode(['public_profile' => true]),
                'bio' => 'حیوان خانگی نمونه.',
                ...$this->timestamps($now),
            ];
        }

        DB::table('pets')->insert($rows);
    }

    private function seedUserAddresses($now): void
    {
        $rows = [];
        for ($i = 1; $i <= 10; $i++) {
            $rows[] = [
                'id' => $i,
                'user_id' => $i,
                'province_id' => $i,
                'city_id' => $i,
                'address' => "آدرس کاربر {$i}، خیابان نمونه، پلاک {$i}",
                'postal_code' => '14567890' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'latitude' => 35.7200000 + ($i / 1000),
                'longitude' => 51.4200000 + ($i / 1000),
                ...$this->timestamps($now),
            ];
        }

        DB::table('user_addresses')->insert($rows);
    }

    private function seedCategories($now): void
    {
        $categories = [
            [null, 'غذای حیوانات', 'pet-food'], [1, 'غذای سگ', 'dog-food'], [1, 'غذای گربه', 'cat-food'], [null, 'لوازم نگهداری', 'pet-supplies'], [4, 'قلاده و بند', 'collar-leash'],
            [4, 'اسباب‌بازی', 'toys'], [null, 'خدمات سلامتی', 'health-services'], [7, 'واکسیناسیون', 'vaccination-services'], [null, 'آرایش و شستشو', 'grooming-services'], [null, 'پانسیون', 'boarding-services'],
        ];
        $rows = [];
        foreach ($categories as $index => [$parentId, $name, $slug]) {
            $rows[] = [
                'id' => $index + 1,
                'parent_id' => $parentId,
                'name' => $name,
                'slug' => $slug,
                'image' => null,
                'type' => $index >= 6 ? CategoryTypes::SERVICE->value : CategoryTypes::PRODUCT->value,
                'activity_status' => ActivityStatus::ACTIVE->value,
                ...$this->timestamps($now),
            ];
        }

        DB::table('categories')->insert($rows);
    }

    private function seedBrands($now): void
    {
        $brands = ['Royal Canin', 'Reflex', 'Trixie', 'Josera', 'Happy Cat', 'Happy Dog', 'Catsan', 'Bioline', 'Ferplast', 'Beaphar'];
        $rows = [];
        foreach ($brands as $index => $brand) {
            $rows[] = [
                'id' => $index + 1,
                'name' => $brand,
                'slug' => Str::slug($brand),
                'image' => null,
                ...$this->timestamps($now),
            ];
        }

        DB::table('brands')->insert($rows);
    }

    private function seedAttributes($now): void
    {
        $attributes = [
            ['رنگ', 'color'], ['وزن', 'weight'], ['سایز', 'size'], ['طعم', 'flavor'], ['سن', 'age'],
            ['جنس', 'material'], ['نژاد', 'breed'], ['نوع بسته‌بندی', 'package-type'], ['کشور سازنده', 'origin-country'], ['مناسب برای', 'suitable-for'],
        ];
        $rows = [];
        foreach ($attributes as $index => [$name, $slug]) {
            $rows[] = [
                'id' => $index + 1,
                'name' => $name,
                'slug' => $slug,
                'is_filterable' => true,
                'display_order' => $index + 1,
                'activity_status' => ActivityStatus::ACTIVE->value,
                ...$this->timestamps($now),
            ];
        }

        DB::table('attributes')->insert($rows);
    }

    private function seedAttributeOptions($now): void
    {
        $labels = [
            1 => ['قرمز', 'آبی', 'سبز', 'زرد', 'مشکی', 'سفید', 'طوسی', 'قهوه‌ای', 'نارنجی', 'صورتی'],
            2 => ['500 گرم', '1 کیلوگرم', '2 کیلوگرم', '3 کیلوگرم', '5 کیلوگرم', '7 کیلوگرم', '10 کیلوگرم', '12 کیلوگرم', '15 کیلوگرم', '20 کیلوگرم'],
            3 => ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'کوچک', 'متوسط', 'بزرگ', 'خیلی بزرگ'],
            4 => ['مرغ', 'گوشت', 'ماهی', 'بوقلمون', 'بره', 'اردک', 'سالمون', 'تن', 'سبزیجات', 'میکس'],
            5 => ['توله', 'جونیور', 'بالغ', 'مسن', 'همه سنین', 'زیر یک سال', 'بالای هفت سال', 'نوزاد', 'مادر', 'حساس'],
            6 => ['پارچه', 'پلاستیک', 'سیلیکون', 'فلز', 'چرم', 'استیل', 'چوب', 'نایلون', 'پنبه', 'سرامیک'],
            7 => ['همه نژادها', 'نژاد کوچک', 'نژاد متوسط', 'نژاد بزرگ', 'پرشین', 'گلدن', 'ژرمن', 'مین‌کون', 'کاسکو', 'خرگوش'],
            8 => ['کیسه', 'قوطی', 'کنسرو', 'پاکت', 'بسته', 'بطری', 'جعبه', 'ساشه', 'وکیوم', 'تکی'],
            9 => ['ایران', 'آلمان', 'فرانسه', 'ترکیه', 'ایتالیا', 'چین', 'هلند', 'اسپانیا', 'کانادا', 'برزیل'],
            10 => ['سگ', 'گربه', 'پرنده', 'ماهی', 'خرگوش', 'همستر', 'لاک‌پشت', 'اسب', 'خزنده', 'همه حیوانات'],
        ];
        $rows = [];
        $id = 1;
        foreach ($labels as $attributeId => $items) {
            foreach ($items as $index => $label) {
                $rows[] = [
                    'id' => $id++,
                    'attribute_id' => $attributeId,
                    'value' => $label,
                    'label' => $label,
                    'slug' => "attr-{$attributeId}-option-" . ($index + 1),
                    'sort_order' => $index + 1,
                    'activity_status' => ActivityStatus::ACTIVE->value,
                    ...$this->timestamps($now),
                ];
            }
        }

        DB::table('attribute_options')->insert($rows);
    }

    private function seedProducts($now): void
    {
        $products = [
            ['غذای خشک سگ Mini Adult', 1, 2], ['غذای خشک گربه Adult', 2, 3], ['تشویقی سگ مرغ', 2, 2], ['قلاده چرمی سگ', 3, 5], ['توپ بازی سگ', 3, 6],
            ['خاک گربه کلامپ', 7, 4], ['شامپو حیوانات', 8, 4], ['باکس حمل حیوان', 9, 4], ['مکمل ویتامین', 10, 1], ['کنسرو گربه سالمون', 5, 3],
        ];
        $rows = [];
        foreach ($products as $index => [$name, $brandId]) {
            $rows[] = [
                'id' => $index + 1,
                'business_id' => (($index + 2) % 10) + 1,
                'brand_id' => $brandId,
                'name' => $name,
                'slug' => 'product-' . ($index + 1),
                'description' => "توضیحات محصول {$name}.",
                'publication_status' => PublicationStatus::PUBLISHED->value,
                'reject_reason' => null,
                ...$this->timestamps($now),
            ];
        }

        DB::table('products')->insert($rows);
    }

    private function seedCategoryProducts($now): void
    {
        $rows = [];
        for ($i = 1; $i <= 10; $i++) {
            $rows[] = [
                'id' => $i,
                'category_id' => (($i - 1) % 6) + 1,
                'product_id' => $i,
                ...$this->timestamps($now),
            ];
        }

        DB::table('category_products')->insert($rows);
    }

    private function seedProductVariations($now): void
    {
        $rows = [];
        $id = 1;
        for ($productId = 1; $productId <= 10; $productId++) {
            foreach ([1, 2] as $variant) {
                $rows[] = [
                    'id' => $id,
                    'product_id' => $productId,
                    'price' => 120000 + ($productId * 50000) + ($variant * 25000),
                    'discount_price' => $variant === 1 ? 110000 + ($productId * 45000) : null,
                    'stock' => 10 + $id,
                    'sku' => 'HP-' . str_pad((string) $productId, 2, '0', STR_PAD_LEFT) . '-' . $variant,
                    'is_default' => $variant === 1,
                    'activity_status' => ActivityStatus::ACTIVE->value,
                    ...$this->timestamps($now),
                ];
                $id++;
            }
        }

        DB::table('product_variations')->insert($rows);
    }

    private function seedProductVariationAttributes($now): void
    {
        $rows = [];
        for ($i = 1; $i <= 20; $i++) {
            $attributeId = $i % 2 === 0 ? 2 : 3;
            $rows[] = [
                'id' => $i,
                'product_variation_id' => $i,
                'attribute_id' => $attributeId,
                'attribute_option_id' => (($attributeId - 1) * 10) + (($i - 1) % 10) + 1,
                ...$this->timestamps($now),
            ];
        }

        DB::table('product_variation_attributes')->insert($rows);
    }

    private function seedProductImages($now): void
    {
        $rows = [];
        $id = 1;
        for ($productId = 1; $productId <= 10; $productId++) {
            foreach ([1, 2] as $order) {
                $rows[] = [
                    'id' => $id++,
                    'product_id' => $productId,
                    'name' => "products/product-{$productId}-{$order}.jpg",
                    'is_primary' => $order === 1,
                    'order' => $order,
                    ...$this->timestamps($now),
                ];
            }
        }

        DB::table('product_images')->insert($rows);
    }

    private function seedBusinessSchedules($now): void
    {
        $rows = [];
        for ($i = 1; $i <= 10; $i++) {
            $rows[] = [
                'id' => $i,
                'business_id' => $i,
                'day_of_week' => (($i - 1) % 7) + 1,
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'slot_duration' => 30,
                'capacity' => 2,
                'activity_status' => ActivityStatus::ACTIVE->value,
                ...$this->timestamps($now),
            ];
        }

        DB::table('business_schedules')->insert($rows);
    }

    private function seedScheduleBreaks($now): void
    {
        $rows = [];
        for ($i = 1; $i <= 10; $i++) {
            $rows[] = [
                'id' => $i,
                'schedule_id' => $i,
                'start_time' => '13:00:00',
                'end_time' => '14:00:00',
                ...$this->timestamps($now),
            ];
        }

        DB::table('schedule_breaks')->insert($rows);
    }

    private function seedBusinessOffDays($now): void
    {
        $rows = [];
        for ($i = 1; $i <= 10; $i++) {
            $rows[] = [
                'id' => $i,
                'business_id' => $i,
                'date' => now()->addDays($i * 3)->toDateString(),
                'reason' => 'تعطیلی مناسبتی',
                ...$this->timestamps($now),
            ];
        }

        DB::table('business_off_days')->insert($rows);
    }

    private function seedAppointments($now): void
    {
        $statuses = [AppointmentStatuses::PENDING->value, AppointmentStatuses::CONFIRMED->value, AppointmentStatuses::COMPLETED->value, AppointmentStatuses::CANCELLED->value];
        $rows = [];
        for ($i = 1; $i <= 10; $i++) {
            $rows[] = [
                'id' => $i,
                'business_id' => $i,
                'user_id' => $i,
                'service_id' => $i,
                'pet_id' => $i,
                'date' => now()->addDays($i)->toDateString(),
                'start_time' => '10:00:00',
                'end_time' => '10:30:00',
                'service_price' => 150000 + ($i * 25000),
                'service_duration' => 30,
                'status' => $statuses[($i - 1) % count($statuses)],
                'notes' => "نوبت نمونه {$i}",
                ...$this->timestamps($now),
            ];
        }

        DB::table('appointments')->insert($rows);
    }

    private function seedConversations($now): void
    {
        $rows = [];
        for ($i = 1; $i <= 10; $i++) {
            $rows[] = [
                'id' => $i,
                'type' => $i % 3 === 0 ? AccessStatuses::PUBLIC->value : AccessStatuses::PRIVATE->value,
                'name' => $i % 3 === 0 ? "گروه پشتیبانی {$i}" : null,
                'image' => null,
                'created_by' => (($i - 1) % 10) + 1,
                'activity_status' => ActivityStatus::ACTIVE->value,
                ...$this->timestamps($now),
            ];
        }

        DB::table('conversations')->insert($rows);
    }

    private function seedConversationParticipants($now): void
    {
        $rows = [];
        $id = 1;
        for ($conversationId = 1; $conversationId <= 10; $conversationId++) {
            foreach ([0, 1] as $offset) {
                $rows[] = [
                    'id' => $id++,
                    'conversation_id' => $conversationId,
                    'user_id' => (($conversationId + $offset - 1) % 10) + 1,
                    'joined_at' => $now,
                    'last_read_at' => $offset === 0 ? $now : null,
                    ...$this->timestamps($now),
                ];
            }
        }

        DB::table('conversation_participants')->insert($rows);
    }

    private function seedMessages($now): void
    {
        $rows = [];
        $id = 1;
        for ($conversationId = 1; $conversationId <= 10; $conversationId++) {
            for ($message = 1; $message <= 3; $message++) {
                $rows[] = [
                    'id' => $id++,
                    'conversation_id' => $conversationId,
                    'sender_id' => (($conversationId + $message - 2) % 10) + 1,
                    'body' => "پیام نمونه {$message} در گفتگو {$conversationId}",
                    'type' => MessageTypes::TEXT->value,
                    'read_at' => $message === 1 ? $now : null,
                    ...$this->timestamps($now),
                ];
            }
        }

        DB::table('messages')->insert($rows);
    }

    private function seedRoutineTemplates($now): void
    {
        $titles = ['واکسیناسیون', 'شستشو', 'کوتاهی ناخن', 'ضد انگل', 'برنامه تغذیه', 'پیاده‌روی', 'معاینه چشم', 'تمیز کردن گوش', 'تعویض آب', 'برس کشیدن'];
        $categories = [RoutineCategoryTypes::HEALTH->value, RoutineCategoryTypes::CARE->value, RoutineCategoryTypes::NUTRITION->value, RoutineCategoryTypes::ACTIVITY->value, RoutineCategoryTypes::MAINTENANCE->value];
        $rows = [];
        for ($i = 1; $i <= 10; $i++) {
            $rows[] = [
                'id' => $i,
                'title' => $titles[$i - 1],
                'species_id' => $i,
                'routine_category' => $categories[($i - 1) % count($categories)],
                'default_interval_days' => 7 * $i,
                'reminder_days_before' => min(7, $i),
                'image' => null,
                'description' => "روتین نمونه {$titles[$i - 1]}.",
                'activity_status' => ActivityStatus::ACTIVE->value,
                ...$this->timestamps($now),
            ];
        }

        DB::table('routine_templates')->insert($rows);
    }

    private function seedPetRoutines($now): void
    {
        $statuses = [RoutineStatuses::UPCOMING->value, RoutineStatuses::DUE_SOON->value, RoutineStatuses::DUE_TODAY->value, RoutineStatuses::OVERDUE->value, RoutineStatuses::PAUSED->value];
        $rows = [];
        for ($i = 1; $i <= 10; $i++) {
            $rows[] = [
                'id' => $i,
                'pet_id' => $i,
                'routine_template_id' => $i,
                'interval_days' => 7 * $i,
                'start_date' => now()->subDays($i)->toDateString(),
                'last_done_at' => $i % 2 === 0 ? now()->subDays($i) : null,
                'next_due_at' => now()->addDays($i),
                'notification_enabled' => true,
                'routine_status' => $statuses[($i - 1) % count($statuses)],
                'settings' => json_encode(['auto_reschedule' => true]),
                ...$this->timestamps($now),
            ];
        }

        DB::table('pet_routines')->insert($rows);
    }

    private function seedRoutineActions($now): void
    {
        $rows = [];
        for ($i = 1; $i <= 10; $i++) {
            $rows[] = [
                'id' => $i,
                'routine_template_id' => $i,
                'target_type' => $i % 2 === 0 ? 'service' : 'product',
                'target_id' => $i,
                'priority' => [Priorities::LOW->value, Priorities::NORMAL->value, Priorities::NECESSARY->value][($i - 1) % 3],
                ...$this->timestamps($now),
            ];
        }

        DB::table('routine_actions')->insert($rows);
    }
}
