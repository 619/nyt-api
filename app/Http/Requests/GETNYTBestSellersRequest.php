<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GETNYTBestSellersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        Validator::extend('valid_isbns', function ($attribute, $value, $parameters, $validator) {
            // Split the string by colon
            $isbns = explode(':', $value);
    
            // Check each ISBN
            foreach ($isbns as $isbn) {
                if (!preg_match('/^\d{10}(\d{3})?$/', $isbn)) {
                    return false;
                }
            }
    
            return true;
        });

        return [
            'author' => 'string|max:256',
            // 'isbn' => 'array',
            // 'isbn.*' => 'digits_between:10,13',
            'isbn' => 'string|max:1048|valid_isbns',
            'title' => 'string|max:256',
            'offset' => 'integer|multiple_of:20'
        ];
    }
}
