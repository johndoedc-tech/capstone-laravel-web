<?php

namespace App\Http\Requests;

use App\Http\Controllers\Auth\OnboardingController;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'preferred_municipality' => [
                'nullable',
                'string',
                Rule::in(array_map('strtoupper', OnboardingController::MUNICIPALITIES)),
            ],
            'cooperative' => [
                'nullable',
                'string',
                Rule::in(OnboardingController::COOPERATIVES),
            ],
        ];
    }
}
