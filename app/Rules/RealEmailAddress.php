<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Adresse e-mail réellement joignable : domaine qui reçoit du courrier (DNS) et pas une boîte jetable.
 */
class RealEmailAddress implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = Str::lower(Str::afterLast((string) $value, '@'));

        if ($domain === '' || ! str_contains((string) $value, '@')) {
            return;
        }

        if ($this->isBlocked($domain)) {
            $fail('Les adresses e-mail temporaires ou de test ne sont pas acceptées. Utilisez une adresse e-mail réelle.');

            return;
        }

        if (config('subscriptions.email.verify_dns') && ! $this->receivesMail($domain)) {
            $fail("Le domaine « {$domain} » ne reçoit pas d'e-mails. Vérifiez l'adresse saisie.");
        }
    }

    private function isBlocked(string $domain): bool
    {
        foreach (config('subscriptions.email.blocked_domains', []) as $blockedDomain) {
            if ($domain === $blockedDomain || str_ends_with($domain, '.'.$blockedDomain)) {
                return true;
            }
        }

        return false;
    }

    private function receivesMail(string $domain): bool
    {
        $asciiDomain = function_exists('idn_to_ascii') ? (idn_to_ascii($domain) ?: $domain) : $domain;
        $host = rtrim($asciiDomain, '.').'.';

        return checkdnsrr($host, 'MX') || checkdnsrr($host, 'A') || checkdnsrr($host, 'AAAA');
    }
}
