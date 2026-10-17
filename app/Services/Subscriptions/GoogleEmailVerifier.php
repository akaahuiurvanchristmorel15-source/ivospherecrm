<?php

namespace App\Services\Subscriptions;

use Illuminate\Http\Client\ConnectionException;
use RuntimeException;

/**
 * Vérification de l'adresse e-mail d'un futur abonné par connexion Google (OAuth 2.0 / OpenID Connect).
 *
 * Google confirme que l'adresse existe et appartient à la personne : seul l'e-mail et le nom sont demandés.
 */
class GoogleEmailVerifier
{
    private const AUTHORIZE_URL = 'https://accounts.google.com/o/oauth2/v2/auth';

    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const USERINFO_URL = 'https://openidconnect.googleapis.com/v1/userinfo';

    public function isConfigured(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }

    public function authorizationUrl(string $state): string
    {
        return self::AUTHORIZE_URL.'?'.http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
        ]);
    }

    /**
     * Échange le code de retour contre l'identité Google vérifiée.
     *
     * @return array{email: string, name: string}
     *
     * @throws RuntimeException
     */
    public function verifiedIdentity(string $code): array
    {
        try {
            $token = PaymentHttp::request()->asForm()->post(self::TOKEN_URL, [
                'code' => $code,
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => $this->redirectUri(),
                'grant_type' => 'authorization_code',
            ]);

            if (! $token->successful() || blank($token->json('access_token'))) {
                throw new RuntimeException('Google a refusé la vérification : '.$token->json('error_description', $token->json('error', 'réponse invalide')));
            }

            $profile = PaymentHttp::request()->withToken((string) $token->json('access_token'))->get(self::USERINFO_URL);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Impossible de joindre Google : '.$exception->getMessage(), previous: $exception);
        }

        $email = (string) $profile->json('email', '');

        if (! $profile->successful() || $email === '' || $profile->json('email_verified') !== true) {
            throw new RuntimeException("Google n'a pas confirmé cette adresse e-mail.");
        }

        return ['email' => mb_strtolower($email), 'name' => (string) $profile->json('name', '')];
    }

    public function redirectUri(): string
    {
        return config('services.google.redirect') ?: route('offres.google.callback');
    }
}
