<?php

namespace App\Modules\Forum\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreThreadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:forum_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'min:10'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['integer', 'exists:forum_tags,id'],
        ];
    }
}
