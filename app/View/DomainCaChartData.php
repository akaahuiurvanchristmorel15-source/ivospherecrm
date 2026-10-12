<?php

namespace App\View;

/**
 * Segments du graphique « Chiffre d'affaires par domaine ».
 * Utilisé au premier affichage (composant Blade) et à chaque actualisation Livewire du tableau de bord.
 */
class DomainCaChartData
{
    /**
     * Couleur de chaque pôle.
     *
     * @var array<string, string>
     */
    private const PALETTE = [
        'print' => '#0066FF',
        'sport' => '#10B981',
        'tech' => '#6366F1',
        'media' => '#F59E0B',
        'assurance' => '#EC4899',
        'finance' => '#0D9488',
        'rh' => '#8B5CF6',
        'ged' => '#0284C7',
    ];

    /**
     * @param  iterable<array-key, array<string, mixed>>  $domainPerformance
     * @return list<array{key: array-key, code: string, cleanName: string, fullName: string, ca: float, caFormatted: string, percent: float, color: string, dash: float, gap: float, offset: float, orders: int}>
     */
    public static function items(iterable $domainPerformance): array
    {
        $circumference = 2 * M_PI * 60;
        $accumulatedPercent = 0;
        $items = [];

        foreach ($domainPerformance as $key => $performance) {
            $cleanName = trim((string) preg_replace('/^IVOSPHERE\s+/i', '', $performance['name'] ?? (string) $key));
            $percent = (float) ($performance['percent'] ?? 0);
            $dash = ($percent / 100) * $circumference;

            $items[] = [
                'key' => $key,
                'code' => $performance['code'] ?? (string) $key,
                'cleanName' => $cleanName,
                'fullName' => 'IVOSPHERE '.$cleanName,
                'ca' => (float) ($performance['ca'] ?? 0),
                'caFormatted' => self::money((float) ($performance['ca'] ?? 0)),
                'percent' => $percent,
                'color' => self::PALETTE[strtolower((string) ($performance['code'] ?? $key))] ?? ($performance['color'] ?? '#64748B'),
                'dash' => round($dash, 2),
                'gap' => round($circumference - $dash, 2),
                'offset' => round(-($accumulatedPercent / 100) * $circumference, 2),
                'orders' => (int) ($performance['orders'] ?? 0),
            ];

            $accumulatedPercent += $percent;
        }

        return $items;
    }

    public static function money(float $amount): string
    {
        return number_format($amount, 0, ',', ' ');
    }
}
