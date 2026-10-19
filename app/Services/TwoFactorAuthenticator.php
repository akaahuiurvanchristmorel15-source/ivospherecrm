<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Double authentification par codes TOTP (RFC 6238), compatible Google Authenticator,
 * Microsoft Authenticator, Authy… : 6 chiffres renouvelés toutes les 30 secondes.
 */
class TwoFactorAuthenticator
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    private const PERIOD = 30;

    private const DIGITS = 6;

    /**
     * Nombre de périodes de 30 s tolérées avant et après l'heure du serveur (décalage d'horloge du téléphone).
     */
    private const WINDOW = 1;

    private const RECOVERY_CODES_COUNT = 8;

    public function __construct(private QrCodeService $qrCodes) {}

    /**
     * Nouvelle clé secrète de 160 bits encodée en Base32.
     */
    public function generateSecret(): string
    {
        return $this->base32Encode(random_bytes(20));
    }

    /**
     * Codes de récupération à usage unique (ex. « K7Q2M-9XWAD »).
     *
     * @return list<string>
     */
    public function generateRecoveryCodes(): array
    {
        return collect(range(1, self::RECOVERY_CODES_COUNT))
            ->map(fn (): string => $this->randomRecoveryChunk().'-'.$this->randomRecoveryChunk())
            ->all();
    }

    /**
     * Cinq caractères sans confusion possible à la recopie (ni 0/O, ni 1/I/L).
     */
    private function randomRecoveryChunk(): string
    {
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

        return implode('', array_map(fn (): string => $alphabet[random_int(0, strlen($alphabet) - 1)], range(1, 5)));
    }

    /**
     * Code attendu pour une clé à un instant donné.
     */
    public function codeAt(string $secret, ?int $timestamp = null): string
    {
        return $this->codeForTimeStep($secret, intdiv($timestamp ?? now()->getTimestamp(), self::PERIOD));
    }

    /**
     * Vérifie le code saisi par l'utilisateur et refuse qu'un même code serve deux fois.
     */
    public function verify(User $user, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code);

        if (! $user->two_factor_secret || ! preg_match('/^\d{'.self::DIGITS.'}$/', $code)) {
            return false;
        }

        $currentStep = intdiv(now()->getTimestamp(), self::PERIOD);

        for ($offset = -self::WINDOW; $offset <= self::WINDOW; $offset++) {
            $step = $currentStep + $offset;

            if (hash_equals($this->codeForTimeStep($user->two_factor_secret, $step), $code)) {
                // Un code intercepté ne peut pas être rejoué pendant sa durée de validité.
                return Cache::add("two-factor-used:{$user->id}:{$step}", true, self::PERIOD * (self::WINDOW * 2 + 2));
            }
        }

        return false;
    }

    /**
     * Consomme un code de récupération : il est retiré de la liste s'il est valide.
     */
    public function useRecoveryCode(User $user, string $recoveryCode): bool
    {
        $recoveryCode = Str::upper(trim($recoveryCode));
        $codes = $user->two_factor_recovery_codes ?? [];

        foreach ($codes as $index => $code) {
            if (hash_equals($code, $recoveryCode)) {
                unset($codes[$index]);
                $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

                return true;
            }
        }

        return false;
    }

    /**
     * Adresse otpauth:// lue par les applications d'authentification.
     */
    public function provisioningUri(User $user): string
    {
        $issuer = config('app.name', 'IVOSPHERE');

        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&digits=%d&period=%d',
            rawurlencode($issuer),
            rawurlencode($user->email),
            $user->two_factor_secret,
            rawurlencode($issuer),
            self::DIGITS,
            self::PERIOD
        );
    }

    /**
     * QR code SVG à scanner depuis l'application d'authentification.
     */
    public function qrCodeSvg(User $user): string
    {
        return $this->qrCodes->generateCompactSvg($this->provisioningUri($user));
    }

    private function codeForTimeStep(string $secret, int $timeStep): string
    {
        $hash = hash_hmac('sha1', pack('J', $timeStep), $this->base32Decode($secret), true);
        $offset = ord($hash[19]) & 0x0F;
        $binary = ((ord($hash[$offset]) & 0x7F) << 24)
            | (ord($hash[$offset + 1]) << 16)
            | (ord($hash[$offset + 2]) << 8)
            | ord($hash[$offset + 3]);

        return str_pad((string) ($binary % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    private function base32Encode(string $bytes): string
    {
        $bits = '';
        foreach (str_split($bytes) as $byte) {
            $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }

        $encoded = '';
        foreach (str_split($bits, 5) as $chunk) {
            $encoded .= self::BASE32_ALPHABET[bindec(str_pad($chunk, 5, '0'))];
        }

        return $encoded;
    }

    private function base32Decode(string $encoded): string
    {
        $bits = '';
        foreach (str_split(strtoupper(rtrim($encoded, '='))) as $character) {
            $bits .= str_pad(decbin((int) strpos(self::BASE32_ALPHABET, $character)), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';
        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $bytes .= chr(bindec($chunk));
            }
        }

        return $bytes;
    }
}
