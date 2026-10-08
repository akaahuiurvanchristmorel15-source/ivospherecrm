<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class StockRevenueBoosterService
{
    /**
     * Analyse complète des produits les plus vendus et génération des leviers de fructification des revenus.
     *
     * @param  Collection<int, Product>  $products
     * @return array<string, mixed>
     */
    public function analyze(Collection $products): array
    {
        if ($products->isEmpty()) {
            return [
                'top_sellers' => collect(),
                'portfolio_metrics' => [
                    'total_top_revenue' => 0,
                    'total_top_profit' => 0,
                    'total_potential_gain_price' => 0,
                    'total_dormant_cash_unlockable' => 0,
                    'at_risk_turnover' => 0,
                    'top_sellers_count' => 0,
                ],
                'dormant_candidates' => collect(),
            ];
        }

        // 1. Détecter les produits dormants disponibles pour ventes croisées (sans sortie récente et avec du stock)
        $dormantCandidates = $products->filter(function (Product $p) {
            return $p->current_stock > 0 && ($p->days_without_movement >= 60 || ($p->exit_volume ?? 0) <= 2);
        })->sortByDesc(function (Product $p) {
            $cost = (float) ($p->purchase_price ?: ($p->selling_price * 0.65));

            return $p->current_stock * $cost;
        })->values();

        // 2. Classer les produits par performance de vente (volume de sorties et CA)
        $sortedProducts = $products->sortByDesc(function (Product $p) {
            $volume = (int) ($p->exit_volume ?? 0);
            $sellingPrice = (float) ($p->selling_price ?: 0);

            // Pondération volume + CA généré
            return ($volume * 1000) + ($volume * $sellingPrice);
        })->values();

        // Sélectionner les 6 meilleurs produits (ou les premiers du catalogue si peu de sorties)
        $topRaw = $sortedProducts->take(6);
        $usedDormantIds = [];
        $analyzedTopSellers = collect();

        $portfolioTopRevenue = 0.0;
        $portfolioTopProfit = 0.0;
        $portfolioPotentialPriceGain = 0.0;
        $portfolioDormantCashUnlockable = 0.0;
        $portfolioAtRiskTurnover = 0.0;

        foreach ($topRaw as $index => $product) {
            $rank = $index + 1;
            $exitVolume = (int) ($product->exit_volume ?? 0);
            $sellingPrice = (float) ($product->selling_price ?: 0);
            $purchasePrice = (float) ($product->purchase_price ?: ($sellingPrice * 0.65));
            $currentStock = (int) $product->current_stock;

            $marginUnit = max(0, $sellingPrice - $purchasePrice);
            $marginPercent = $sellingPrice > 0 ? round(($marginUnit / $sellingPrice) * 100, 1) : 0.0;

            $revenueGenerated = $exitVolume * $sellingPrice;
            $profitGenerated = $exitVolume * $marginUnit;

            // Vélocité journalière estimée (sur base 30 jours, minimum 0.2 unité/jour si produit vendu)
            $dailyVelocity = $exitVolume > 0 ? max(0.1, round($exitVolume / 30, 2)) : 0.2;
            $daysOfCoverage = $dailyVelocity > 0 ? (int) round($currentStock / $dailyVelocity) : 999;

            // Définition du risque de rupture
            if ($currentStock <= 0) {
                $riskLevel = 'rupture';
                $riskLabel = 'Rupture Immédiate';
                $riskBadgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
            } elseif ($daysOfCoverage <= 7) {
                $riskLevel = 'critique';
                $riskLabel = "Rupture imminente ({$daysOfCoverage}j)";
                $riskBadgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
            } elseif ($daysOfCoverage <= 15) {
                $riskLevel = 'attention';
                $riskLabel = "Attention ({$daysOfCoverage}j restants)";
                $riskBadgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
            } else {
                $riskLevel = 'optimal';
                $riskLabel = "Stock sain ({$daysOfCoverage}j)";
                $riskBadgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            }

            // Manque à gagner potentiel si rupture de stock pendant 14 jours
            $potentialStockoutLoss = round($dailyVelocity * 14 * $sellingPrice);
            if (in_array($riskLevel, ['rupture', 'critique', 'attention'])) {
                $portfolioAtRiskTurnover += $potentialStockoutLoss;
            }

            // ── LEVIER 1 : OPTIMISATION TARIFAIRE (+5% et +10%) ──
            // Arrondi supérieur à la centaine de FCFA
            $optPrice5 = (int) (ceil(($sellingPrice * 1.05) / 100) * 100);
            if ($optPrice5 <= $sellingPrice) {
                $optPrice5 = (int) ($sellingPrice + 100);
            }
            $optPrice10 = (int) (ceil(($sellingPrice * 1.10) / 100) * 100);
            if ($optPrice10 <= $optPrice5) {
                $optPrice10 = (int) ($sellingPrice + 200);
            }

            $gainUnit5 = $optPrice5 - $sellingPrice;
            $gainUnit10 = $optPrice10 - $sellingPrice;

            // Gain potentiel mensuel calculé sur le volume vendu
            $projectedMonthlyGain5 = round($exitVolume * $gainUnit5);
            $projectedMonthlyGain10 = round($exitVolume * $gainUnit10);

            $newMarginPercent5 = $optPrice5 > 0 ? round((($optPrice5 - $purchasePrice) / $optPrice5) * 100, 1) : 0.0;
            $newMarginPercent10 = $optPrice10 > 0 ? round((($optPrice10 - $purchasePrice) / $optPrice10) * 100, 1) : 0.0;

            // ── LEVIER 2 : OFFRE PACK & VENTE CROISÉE SYNERGIE ──
            // Trouver le meilleur produit dormant complémentaire (même domaine en priorité)
            $matchedDormant = $dormantCandidates
                ->where('id', '!=', $product->id)
                ->reject(fn (Product $d) => in_array($d->id, $usedDormantIds))
                ->sortByDesc(fn (Product $d) => ($d->domain_id === $product->domain_id ? 1000 : 0) + $d->current_stock)
                ->first();

            // Si tous les dormants ont été appariés, réutiliser le plus fort stock dormant
            if (! $matchedDormant && $dormantCandidates->isNotEmpty()) {
                $matchedDormant = $dormantCandidates->where('id', '!=', $product->id)->first();
            }

            $bundleData = null;
            if ($matchedDormant) {
                $usedDormantIds[] = $matchedDormant->id;

                $dormantPrice = (float) ($matchedDormant->selling_price ?: 0);
                $dormantCost = (float) ($matchedDormant->purchase_price ?: ($dormantPrice * 0.65));
                $combinedNormalPrice = $sellingPrice + $dormantPrice;

                // Remise pack de 8% sur le combo
                $packDiscountPercent = 8;
                $bundlePrice = (int) (floor(($combinedNormalPrice * (1 - ($packDiscountPercent / 100))) / 100) * 100);
                $packSavings = $combinedNormalPrice - $bundlePrice;

                // Revenu net additionnel par panier vendu
                $additionalCartRevenue = max(0, $bundlePrice - $sellingPrice);
                $dormantCashFreed = $dormantCost;

                $topCleanSku = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string) ($product->sku ?: 'TOP')), 0, 4));
                $dormantCleanSku = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string) ($matchedDormant->sku ?: 'ACC')), 0, 4));
                $suggestedPromoCode = 'PACK-'.$topCleanSku.'-'.$dormantCleanSku;

                $bundleData = [
                    'dormant_product' => $matchedDormant,
                    'bundle_name' => "Pack Synergie : {$product->name} + {$matchedDormant->name}",
                    'suggested_code' => $suggestedPromoCode,
                    'normal_total_price' => $combinedNormalPrice,
                    'bundle_price' => $bundlePrice,
                    'pack_savings' => $packSavings,
                    'discount_percent' => $packDiscountPercent,
                    'additional_cart_revenue' => $additionalCartRevenue,
                    'dormant_cash_freed' => $dormantCashFreed,
                ];

                $portfolioDormantCashUnlockable += ($dormantCost * min(10, $matchedDormant->current_stock));
            }

            // ── LEVIER 3 : BOUCLIER ANTI-RUPTURE & APPROVISIONNEMENT PRIORITAIRE ──
            // Réapprovisionnement de sécurité ciblant 45 jours de ventes + marge de sécurité
            $safetyStockBuffer = (int) ceil($dailyVelocity * 45);
            $recommendedReorderQty = max($safetyStockBuffer - $currentStock, (int) ($product->min_stock * 2), 10);
            $estimatedPurchaseCost = $recommendedReorderQty * $purchasePrice;
            $securedRevenueForecast = $recommendedReorderQty * $sellingPrice;

            $portfolioTopRevenue += $revenueGenerated;
            $portfolioTopProfit += $profitGenerated;
            $portfolioPotentialPriceGain += $projectedMonthlyGain5;

            $analyzedTopSellers->push([
                'rank' => $rank,
                'product' => $product,
                'exit_volume' => $exitVolume,
                'selling_price' => $sellingPrice,
                'purchase_price' => $purchasePrice,
                'margin_unit' => $marginUnit,
                'margin_percent' => $marginPercent,
                'revenue_generated' => $revenueGenerated,
                'profit_generated' => $profitGenerated,
                'daily_velocity' => $dailyVelocity,
                'days_of_coverage' => $daysOfCoverage,
                'risk_level' => $riskLevel,
                'risk_label' => $riskLabel,
                'risk_badge_class' => $riskBadgeClass,
                'potential_stockout_loss' => $potentialStockoutLoss,
                'pricing_lever' => [
                    'opt_price_5' => $optPrice5,
                    'gain_unit_5' => $gainUnit5,
                    'projected_monthly_gain_5' => $projectedMonthlyGain5,
                    'new_margin_percent_5' => $newMarginPercent5,
                    'opt_price_10' => $optPrice10,
                    'gain_unit_10' => $gainUnit10,
                    'projected_monthly_gain_10' => $projectedMonthlyGain10,
                    'new_margin_percent_10' => $newMarginPercent10,
                ],
                'bundle_lever' => $bundleData,
                'anti_stockout_lever' => [
                    'recommended_reorder_qty' => $recommendedReorderQty,
                    'estimated_purchase_cost' => $estimatedPurchaseCost,
                    'secured_revenue_forecast' => $securedRevenueForecast,
                    'days_target' => 45,
                ],
            ]);
        }

        return [
            'top_sellers' => $analyzedTopSellers,
            'portfolio_metrics' => [
                'total_top_revenue' => $portfolioTopRevenue,
                'total_top_profit' => $portfolioTopProfit,
                'total_potential_gain_price' => $portfolioPotentialPriceGain,
                'total_dormant_cash_unlockable' => $portfolioDormantCashUnlockable,
                'at_risk_turnover' => $portfolioAtRiskTurnover,
                'top_sellers_count' => $analyzedTopSellers->count(),
            ],
            'dormant_candidates' => $dormantCandidates,
        ];
    }
}
