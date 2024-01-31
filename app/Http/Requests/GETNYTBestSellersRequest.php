<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GETNYTBestSellersRequest extends FormRequest
{

    public function authorize(): bool {
        return true;
    }

    public function rules(): array {

        //This function validates the isbn field, making sure that it is either one 10/13 digit isbn or a valid list of them.
        //A valid list of isbns constitutes multiple valid isbns, joined by colons.
        Validator::extend('valid_isbns', function ($attribute, $value, $parameters, $validator) {
            $isbns = explode(';', $value);
    
            foreach ($isbns as $isbn) {
                if (!preg_match('/^\d{10}(\d{3})?$/', $isbn)) {
                    return false;
                }
            }
    
            return true;
        });
        return [
            'author' => 'string|max:256',
            'isbn' => 'string|max:1048|valid_isbns',
            'title' => 'string|max:256',
            'offset' => 'integer|min:0|multiple_of:20'
        ];
    }
}
