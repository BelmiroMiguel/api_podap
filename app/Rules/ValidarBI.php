<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidarBI implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Padrão: 000000000AA000
        if (!preg_match('/^[0-9]{9}[A-Z]{2}[0-9]{3}$/', $value)) {
            $fail('O campo :attribute deve ser um Bilhete de Identidade angolano válido.');
        }
    }
}
    