<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\RedeSocial;
use Illuminate\Validation\Rule;

class RedesSociaisUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // true se o usuário autenticado puder editar
        return true;
    }

    public function rules(): array
    {
        return [
            'facebook' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'linkedin' => ['nullable', 'string', 'max:255'],
            'youtube' => ['nullable', 'string', 'max:255'],
            'tiktok' => ['nullable', 'string', 'max:255'],
            'x' => ['nullable', 'string', 'max:255'],

        ];
    }
}
