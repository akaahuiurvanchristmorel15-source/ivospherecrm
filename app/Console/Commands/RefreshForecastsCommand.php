<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\BusinessForecastService;
use App\Support\TenantContext;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ai:forecast')]
#[Description('Recalcule les prévisions IA (ventes et ruptures de stock) de chaque espace.')]
class RefreshForecastsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(BusinessForecastService $forecasts): int
    {
        foreach (Tenant::query()->pluck('id') as $tenantId) {
            $forecast = TenantContext::run((int) $tenantId, fn () => $forecasts->refresh());

            $this->info(sprintf(
                'Espace %d : %s FCFA prévus sur 30 jours, %d produit(s) suivis (moteur %s).',
                $tenantId,
                number_format((float) $forecast['sales']['next_30_days'], 0, ',', ' '),
                count($forecast['stockouts']),
                $forecast['engine'],
            ));
        }

        return self::SUCCESS;
    }
}
