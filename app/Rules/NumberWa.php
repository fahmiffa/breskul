<?php
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class NumberWa implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (substr($value, 0, 1) === '0') {
            $to = '62' . substr($value, 1);
            $response = Http::post(env('URL_WA').'/number', [
                'number'  => env('NumberWa'),
                'to' => $to,
            ]);

            if($response->status() != 200)
            {
                $fail('Nomor WhatsApp tidak valid');
            }
        }
        else
        {
            $fail('Nomor tidak valid');
        }        
    }
}
