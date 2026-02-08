<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidarTelefone implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Padrão: Começa com 9, seguido de 8 dígitos (Total 9)
        if (!preg_match('/^9[0-9]{8}$/', $value)) {
            $fail('O número de telefone deve ser um contato válido de Angola (ex: 9XXXXXXXX).');
        }
    }
}
