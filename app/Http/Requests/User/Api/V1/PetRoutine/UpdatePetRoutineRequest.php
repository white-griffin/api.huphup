<?php

namespace App\Http\Requests\User\Api\V1\PetRoutine;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePetRoutineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        $routine = $this->route('pet_routine');

        return [
            'interval_days' => ['nullable', 'integer', 'min:1'],
            'last_done_at' => ['nullable', 'date'],
            'next_due_at' => ['nullable', 'date', 'after_or_equal:last_done_at'],

            'notification_enabled' => ['nullable', 'boolean'],
            'settings' => ['nullable', 'array'],
            'notes' => ['nullable', 'string', 'max:1000'],

            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'interval_days.integer' => 'بازه زمانی باید عدد باشد',
            'interval_days.min' => 'بازه زمانی باید حداقل ۱ روز باشد',
            'last_done_at.date' => 'تاریخ انجام آخرین روتین معتبر نیست',
            'next_due_at.date' => 'تاریخ موعد بعدی معتبر نیست',
            'next_due_at.after_or_equal' => 'موعد بعدی نمی‌تواند قبل از تاریخ انجام آخرین روتین باشد',
            'notification_enabled.boolean' => 'وضعیت اعلان معتبر نیست',
            'settings.array' => 'تنظیمات باید آرایه باشد',
            'notes.string' => 'یادداشت باید متنی باشد',
            'notes.max' => 'یادداشت نباید بیشتر از ۱۰۰۰ کاراکتر باشد',
            'is_active.boolean' => 'وضعیت فعال بودن معتبر نیست',
        ];
    }

    public function data($key = null, $default = null): array
    {
        return array_filter(
            $this->validated(),
            fn ($value) => ! is_null($value)
        );
    }

}
