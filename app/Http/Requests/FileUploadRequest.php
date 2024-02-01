<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\FileUploadSequence;
use Illuminate\Validation\Rules\Enum;

class FileUploadRequest extends FormRequest
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
            'name' => [Rule::requiredIf(fn () => $this->sequence != 'first')],
            'file' => ['required', 'file'],
            'sequence' => [
                'required',
                new Enum(FileUploadSequence::class)
            ],
        ];
    }
}
