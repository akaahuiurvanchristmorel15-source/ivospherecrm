<?php

namespace App\Services;

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
     * Règle de validation : numéro réellement attribuable dans son pays.
     * Tout pays est accepté avec son indicatif (+33, +225…) ; sans indicatif, le numéro est lu comme ivoirien.
     */
    public static function rule(): Phone
    {
        return (new Phone)->international()->country([self::DEFAULT_COUNTRY]);
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

        try {
            $phone = new PhoneNumber($number, self::DEFAULT_COUNTRY);

            if (! $phone->isValid()) {
                return $number;
            }

            // Côte d'Ivoire : 10 chiffres groupés par deux (+225 07 08 09 10 11)
            if ($phone->getCountry() === self::DEFAULT_COUNTRY) {
                $nationalDigits = substr($phone->formatE164(), strlen('+225'));

                return '+225 '.implode(' ', str_split($nationalDigits, 2));
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
}
