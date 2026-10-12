<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Promotion;
use App\Services\ActivityLogger;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('promo:assign {code : Code promo (ex: VIP10)} {--customer=* : ID ou code de client à attribuer (ex: --customer=1 --customer=CLI-001)} {--all : Attribuer à tous les clients actifs} {--clear : Supprimer les restrictions (accessible à tous les clients)}')]
#[Description('Attribue un code promo à un ou plusieurs clients (réservé à un usage unique par client en caisse).')]
class AssignPromotionCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $code = strtoupper(trim((string) $this->argument('code')));
        $promotion = Promotion::where('code', $code)->first();

        if (! $promotion) {
            $this->error("Le code promo \"{$code}\" n'existe pas.");

            return self::FAILURE;
        }

        if ($this->option('clear')) {
            $promotion->customers()->detach();
            $this->info("Toutes les restrictions du code \"{$promotion->code}\" ont été supprimées. Il est maintenant accessible à tous.");

            ActivityLogger::log('assigned_promotion', "Restrictions supprimées sur le code {$promotion->code} (accessible à tous)", $promotion);

            return self::SUCCESS;
        }

        $customerQuery = Customer::query();

        if ($this->option('all')) {
            $customerIds = $customerQuery->active()->pluck('id')->all();
        } else {
            $inputs = (array) $this->option('customer');

            if (empty($inputs)) {
                $this->error('Veuillez spécifier au moins un client avec --customer=<id|code>, ou utiliser --all ou --clear.');

                return self::FAILURE;
            }

            $customerIds = Customer::where(function ($q) use ($inputs) {
                $q->whereIn('id', $inputs)
                    ->orWhereIn('code', $inputs);
            })->pluck('id')->all();

            if (empty($customerIds)) {
                $this->error('Aucun client correspondant trouvé.');

                return self::FAILURE;
            }
        }

        $promotion->customers()->syncWithoutDetaching($customerIds);

        $count = count($customerIds);
        $this->info("Le code promo \"{$promotion->code}\" a été attribué avec succès à {$count} client(s).");

        ActivityLogger::log('assigned_promotion', "Attribution CLI du code promo {$promotion->code} à {$count} client(s)", $promotion);

        return self::SUCCESS;
    }
}
