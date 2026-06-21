<?php

namespace App\Http\Requests\User\Api\V1\PetRoutine;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePetRoutineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pet_id' => [
                'required',
                'integer',
                Rule::exists('pets', 'id')->where(function ($query) {
                    $query->where('user_id', $this->user()->id);
                }),
            ],

            'routine_template_id' => [
                'required',
                'integer',
                Rule::exists('routine_templates', 'id')->where(function ($query) {
                    $query->where('is_active', 1);
                }),
            ],

            'interval_days' => ['nullable', 'integer', 'min:1'],
            'last_done_at' => ['nullable', 'date'],
            'next_due_at' => ['nullable', 'date', 'after_or_equal:last_done_at'],

            'notification_enabled' => ['nullable', 'boolean'],

            'settings' => ['nullable', 'array'],

            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'pet_id.required' => 'پت را انتخاب کنید',
            'pet_id.exists' => 'پت انتخاب شده معتبر نیست',

            'routine_template_id.required' => 'قالب روتین را انتخاب کنید',
            'routine_template_id.exists' => 'قالب روتین انتخاب شده معتبر نیست',

            'interval_days.integer' => 'بازه زمانی باید عدد باشد',
            'interval_days.min' => 'بازه زمانی باید حداقل ۱ روز باشد',

            'last_done_at.date' => 'تاریخ انجام آخرین روتین معتبر نیست',
            'next_due_at.date' => 'تاریخ موعد بعدی معتبر نیست',
            'next_due_at.after_or_equal' => 'موعد بعدی نمی‌تواند قبل از تاریخ انجام آخرین روتین باشد',

            'notification_enabled.boolean' => 'وضعیت اعلان معتبر نیست',
            'settings.array' => 'تنظیمات باید آرایه باشد',
            'notes.string' => 'یادداشت باید متنی باشد',
            'notes.max' => 'یادداشت نباید بیشتر از ۱۰۰۰ کاراکتر باشد',
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
