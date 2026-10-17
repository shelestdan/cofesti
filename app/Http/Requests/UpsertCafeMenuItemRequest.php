<?php

namespace App\Http\Requests;

use App\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertCafeMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, [UserRole::Administrator, UserRole::Manager], true);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $itemId = $this->route('cafe_menu_item')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('cafe_menu_items', 'slug')->ignore($itemId)],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price_minor' => ['nullable', 'integer', 'min:0'],
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
            'is_available' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'position' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
