<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmissionFormRequest extends FormRequest
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
            'name' => 'required|min:2|max:10',
            'email' => 'required|email:rfc,dns|not_regex:/gmail\.com$/i|unique:submissions,email',
            'phone' => 'required|regex:/^(\+?\d{1,3} ?\d{1,3})? ?\d{3} ?\d{3} ?\d{3}$/',
            'message' => 'required|min:10',
            'street' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'country' => 'required',
            'images.*' => 'image|mime:jpeg',
            'files.*' => 'mimes:application/pdf',
        ];
    }
}
