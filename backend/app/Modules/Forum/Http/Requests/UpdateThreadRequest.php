<?php

namespace App\Modules\Forum\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateThreadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string', 'min:10'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['integer', 'exists:forum_tags,id'],
        ];
    }
}
