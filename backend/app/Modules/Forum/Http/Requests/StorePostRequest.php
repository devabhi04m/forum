<?php

namespace App\Modules\Forum\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:2'],
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:forum_posts,id'],
        ];
    }
}
