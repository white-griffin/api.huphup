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
            'start_date' => ['nullable', 'date'],
            'interval_days' => ['nullable', 'integer', 'min:1'],
            'last_done_at' => ['nullable', 'date'],
            'next_due_at' => ['nullable', 'date'],

            'notification_enabled' => ['nullable', 'boolean'],
            'settings' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.date' => 'تاریخ شروع روتین معتبر نیست',
            'interval_days.integer' => 'بازه زمانی باید عدد باشد',
            'interval_days.min' => 'بازه زمانی باید حداقل ۱ روز باشد',
            'last_done_at.date' => 'تاریخ انجام آخرین روتین معتبر نیست',
            'next_due_at.date' => 'تاریخ موعد بعدی معتبر نیست',
            'notification_enabled.boolean' => 'وضعیت اعلان معتبر نیست',
            'settings.array' => 'تنظیمات باید آرایه باشد',
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
