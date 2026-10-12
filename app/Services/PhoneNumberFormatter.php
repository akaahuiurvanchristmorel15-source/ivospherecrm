<?php

namespace App\Services;

use Closure;
use Propaganistas\LaravelPhone\PhoneNumber;
use Propaganistas\LaravelPhone\Rules\Phone;
use Throwable;

class PhoneNumberFormatter
{
    /**
     * Pays utilisé pour interpréter un numéro saisi sans indicatif (Côte d'Ivoire).
     */
    public const DEFAULT_COUNTRY = 'CI';

    /**
     * Indicatif téléphonique de la Côte d'Ivoire.
     */
    public const DEFAULT_DIAL_CODE = '225';

    /**
     * Forçage (tests) de l'utilisation ou non de la librairie propaganistas/laravel-phone.
     */
    protected static ?bool $libraryEnabled = null;

    /**
     * Active ou désactive l'utilisation de la librairie (null = détection automatique).
     */
    public static function useLibrary(?bool $enabled): void
    {
        static::$libraryEnabled = $enabled;
    }

    /**
     * Indique si la librairie propaganistas/laravel-phone est disponible.
     */
    public static function libraryAvailable(): bool
    {
        if (static::$libraryEnabled !== null) {
            return static::$libraryEnabled;
        }

        return class_exists(Phone::class) && class_exists(PhoneNumber::class);
    }

    /**
     * Règle de validation : numéro réellement attribuable dans son pays.
     * Tout pays est accepté avec son indicatif (+33, +225…) ; sans indicatif, le numéro est lu comme ivoirien.
     * Si la librairie n'est pas installée, une validation de secours (format E.164) est appliquée.
     */
    public static function rule(): Phone|Closure
    {
        if (static::libraryAvailable()) {
            return (new Phone)->international()->country([self::DEFAULT_COUNTRY]);
        }

        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_string($value) || self::fallbackNormalize($value) === null) {
                $fail(self::invalidMessage());
            }
        };
    }

    /**
     * Message d'erreur affiché lorsqu'un numéro n'est pas valide.
     */
    public static function invalidMessage(): string
    {
        return 'Le numéro :attribute n\'est pas un numéro de téléphone valide. Indiquez l\'indicatif du pays (ex. +225 07 08 09 10 11 ou +33 6 12 34 56 78).';
    }

    /**
     * Formate un numéro au format international lisible (ex. « +225 07 08 09 10 11 »).
     * Retourne la valeur d'origine si elle ne peut pas être interprétée.
     */
    public static function format(?string $number): ?string
    {
        $number = trim((string) $number);

        if ($number === '') {
            return null;
        }

        if (! static::libraryAvailable()) {
            return self::fallbackFormat($number);
        }

        try {
            $phone = new PhoneNumber($number, self::DEFAULT_COUNTRY);

            if (! $phone->isValid()) {
                return $number;
            }

            // Côte d'Ivoire : 10 chiffres groupés par deux (+225 07 08 09 10 11)
            if ($phone->getCountry() === self::DEFAULT_COUNTRY) {
                return self::formatIvorian(substr($phone->formatE164(), strlen('+'.self::DEFAULT_DIAL_CODE)));
            }

            return $phone->formatInternational();
        } catch (Throwable) {
            return $number;
        }
    }

    /**
     * Chiffres du numéro au format E.164 sans « + » (pour wa.me et tel:).
     */
    public static function digits(?string $number): ?string
    {
        $formatted = self::format($number);

        if ($formatted === null) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $formatted);

        return $digits !== '' ? $digits : null;
    }

    /**
     * Formate un numéro national ivoirien (10 chiffres) en « +225 07 08 09 10 11 ».
     */
    protected static function formatIvorian(string $nationalDigits): string
    {
        return '+'.self::DEFAULT_DIAL_CODE.' '.implode(' ', str_split($nationalDigits, 2));
    }

    /**
     * Formatage de secours utilisé lorsque la librairie n'est pas installée.
     */
    protected static function fallbackFormat(string $number): string
    {
        $normalized = self::fallbackNormalize($number);

        if ($normalized === null) {
            return $number;
        }

        if ($normalized['country'] === self::DEFAULT_COUNTRY) {
            return self::formatIvorian($normalized['national']);
        }

        return '+'.$normalized['digits'];
    }

    /**
     * Analyse basique d'un numéro sans librairie : ivoirien (10 chiffres) ou international E.164.
     *
     * @return array{country: string|null, digits: string, national: string}|null
     */
    protected static function fallbackNormalize(string $number): ?array
    {
        $number = trim($number);

        if ($number === '' || preg_match('/^\+?[\d\s.\-()\/]+$/', $number) !== 1) {
            return null;
        }

        $hasPlus = str_starts_with($number, '+');
        $digits = preg_replace('/\D/', '', $number);

        if (! $hasPlus && str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
            $hasPlus = true;
        }

        $dialCode = self::DEFAULT_DIAL_CODE;

        // Numéro ivoirien saisi sans indicatif : 10 chiffres commençant par 01, 05, 07, 21, 25 ou 27
        if (! $hasPlus && strlen($digits) === 10) {
            return self::ivorianNationalIsValid($digits)
                ? ['country' => self::DEFAULT_COUNTRY, 'digits' => $dialCode.$digits, 'national' => $digits]
                : null;
        }

        // Numéro ivoirien avec indicatif (+225 ou 225)
        if (str_starts_with($digits, $dialCode)) {
            $national = substr($digits, strlen($dialCode));

            return strlen($national) === 10 && self::ivorianNationalIsValid($national)
                ? ['country' => self::DEFAULT_COUNTRY, 'digits' => $digits, 'national' => $national]
                : null;
        }

        // Autre pays : indicatif obligatoire, 8 à 15 chiffres (norme E.164)
        if ($hasPlus && preg_match('/^[1-9]\d{7,14}$/', $digits) === 1) {
            return ['country' => null, 'digits' => $digits, 'national' => $digits];
        }

        return null;
    }

    /**
     * Vérifie le préfixe d'un numéro national ivoirien (mobiles 01/05/07, fixes 21/25/27).
     */
    protected static function ivorianNationalIsValid(string $national): bool
    {
        return preg_match('/^(01|05|07|21|25|27)\d{8}$/', $national) === 1;
    }
}
