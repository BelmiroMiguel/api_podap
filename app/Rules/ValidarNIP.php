<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidarNIP implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Exemplo: PN seguido de 6 a 9 dígitos (Ajuste conforme o padrão real da corporação)
        if (!preg_match('/^PN[0-9]{6,9}$/', $value)) {
            $fail('O NIP informado não segue o padrão da Polícia Nacional (Ex: PN123456).');
        }
    }
}
