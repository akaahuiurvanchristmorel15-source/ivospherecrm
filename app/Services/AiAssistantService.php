<?php

namespace App\Services;

use App\Models\CashRegister;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\StockAlert;
use App\Models\TechProject;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AiAssistantService
{
    /**
     * Process natural language ERP query and return structured data response.
     *
     * @return array{intent: string, answer: string, data: mixed, suggestions: array<string>}
     */
    public function processQuery(string $query): array
    {
        $normalized = Str::lower(trim($query));

        // 1. Chiffre d'affaires / Ventes par domaine ou global
        if (Str::contains($normalized, ['chiffre d\'affaires', 'chiffre d\'affaire', 'ca', 'revenu', 'ventes']) && ! Str::contains($normalized, ['résume', 'resume'])) {
            return $this->handleRevenueQuery($normalized);
        }

        // 2. Rupture de stock / Stock critique
        if (Str::contains($normalized, ['rupture', 'stock', 'épuisé', 'critique', 'inventaire'])) {
            return $this->handleStockQuery();
        }

        // 3. Factures en retard / Impayés / Créances
        if (Str::contains($normalized, ['facture', 'retard', 'impayé', 'impayee', 'creance', 'dette'])) {
            return $this->handleOverdueInvoicesQuery();
        }

        // 4. Clients inactifs
        if (Str::contains($normalized, ['inactif', 'pas commandé', 'depuis 3 mois', 'relance client'])) {
            return $this->handleInactiveCustomersQuery();
        }

        // 5. Résumé des ventes de la semaine / du mois
        if (Str::contains($normalized, ['résume', 'resume', 'récapitulatif', 'bilan', 'hebdo'])) {
            return $this->handleSalesSummaryQuery($normalized);
        }

        // 6. Trésorerie / Caisse
        if (Str::contains($normalized, ['trésorerie', 'tresorerie', 'caisse', 'solde', 'liquidité'])) {
            return $this->handleCashQuery();
        }

        // 7. Projets en cours
        if (Str::contains($normalized, ['projet', 'tech', 'développement', 'chantier'])) {
            return $this->handleProjectsQuery();
        }

        // Fallback / Vue d'ensemble
        return $this->handleGeneralQuery($query);
    }

    /**
     * Generate creative business content (Studio IA).
     *
     * @param  array<string, mixed>  $params
     * @return array{title: string, content: string, format: string, tags: array<string>}
     */
    public function generateContent(string $type, array $params = []): array
    {
        $domainName = $params['domain'] ?? 'IVOSPHERE';
        $topic = $params['topic'] ?? 'Excellence & Performance';
        $targetAudience = $params['target'] ?? 'Entreprises et Professionnels';

        return match ($type) {
            'commercial_pitch' => [
                'title' => "Proposition Commerciale — {$topic}",
                'format' => 'Proposition commerciale',
                'tags' => ['Commercial', $domainName, 'Vente'],
                'content' => "Madame, Monsieur,\n\n"
                    ."Dans le cadre de l'optimisation de vos activités, {$domainName} a le plaisir de vous soumettre une proposition sur-mesure dédiée à : {$topic}.\n\n"
                    ."🎯 **Vos Enjeux Identifiés :**\n"
                    ."- Accroître votre productivité opérationnelle avec des livrables de haute précision.\n"
                    ."- Bénéficier d'un accompagnement réactif certifié conforme aux normes IVOSPHERE.\n"
                    ."- Maîtriser vos coûts tout en maximisant votre retour sur investissement.\n\n"
                    ."💡 **Notre Solution Clé en Main :**\n"
                    ."Nous mobilisons l'ensemble de notre expertise {$domainName} avec un calendrier d'exécution garanti et un interlocuteur unique dédié.\n\n"
                    ."🤝 **Prochaines étapes :**\n"
                    ."Nous vous invitons à planifier un point d'étape de 15 minutes afin d'ajuster les spécifications avant contractualisation.",
            ],

            'product_description' => [
                'title' => "Fiche Produit Optimisée — {$topic}",
                'format' => 'Description catalogue & e-commerce',
                'tags' => ['Catalogue', $domainName, 'SEO'],
                'content' => "### {$topic}\n\n"
                    ."**L'excellence signée {$domainName} pour vos projets exigeants.**\n\n"
                    ."Conçu pour répondre aux standards professionnels les plus stricts, ce produit combine fiabilité éprouvée, finition premium et performance durable.\n\n"
                    ."**Points forts :**\n"
                    ."• Qualité de confection supérieure et contrôle rigoureux en atelier.\n"
                    ."• Parfaitement adapté pour : {$targetAudience}.\n"
                    ."• Garantie et support technique réactif sous 24h ouvrées.\n\n"
                    .'*Spécifications disponibles sur simple demande auprès de nos conseillers.*',
            ],

            'invoice_followup_email' => [
                'title' => 'Email de Relance — Facturation Échue',
                'format' => 'Email de relance courtois et ferme',
                'tags' => ['Finance', 'Recouvrement', 'Comptabilité'],
                'content' => "Objet : Rappel — Règlement de votre facture en attente\n\n"
                    ."Madame, Monsieur,\n\n"
                    ."Sauf erreur ou omission de notre part, nous constatons que votre facture relative à nos prestations {$domainName} demeure à ce jour impayée.\n\n"
                    ."Nous vous serions reconnaissants de bien vouloir régulariser cette situation dans les meilleurs délais ou de nous transmettre l'avis de virement bancaire correspondant.\n\n"
                    ."Si votre règlement a été émis récemment, nous vous prions de ne pas tenir compte de cette relance.\n\n"
                    ."Restant à votre entière écoute pour toute modalité particulière,\n\n"
                    ."Cordialement,\n"
                    .'La Direction Financière IVOSPHERE',
            ],

            'social_post' => [
                'title' => "Publication Réseaux Sociaux — {$domainName}",
                'format' => 'Post LinkedIn / Facebook professionnel',
                'tags' => ['Marketing', 'Réseaux Sociaux', 'Communication'],
                'content' => "🚀 Innovez avec puissance grâce à {$domainName} !\n\n"
                    ."Aujourd'hui, nous mettons en lumière : {$topic}.\n\n"
                    ."Pourquoi les leaders du marché nous font confiance ?\n"
                    ."✅ Un savoir-faire pointu axé sur la qualité sans compromis\n"
                    ."✅ Des délais tenus et une traçabilité totale à chaque étape\n"
                    ."✅ Un écosystème intégré pour booster votre croissance\n\n"
                    ."💬 Et vous, quels sont vos défis prioritaires ce trimestre ? Échangeons en commentaires ou en message privé !\n\n"
                    ."#IVOSPHERE #Innovation #Excellence #Business #{$domainName}",
            ],

            'slogans' => [
                'title' => "Slogans & Accroches de Campagne — {$topic}",
                'format' => 'Propositions d\'accroches percutantes',
                'tags' => ['Créatif', 'Branding', 'Communication'],
                'content' => "Voici 5 propositions de slogans calibrés pour {$domainName} :\n\n"
                    ."1. « {$topic} : L'excellence au cœur de chaque détail. »\n"
                    ."2. « L'ambition sans limite, la signature {$domainName}. »\n"
                    ."3. « Plus qu'une solution : votre standard de performance. »\n"
                    ."4. « Façonnez l'avenir de vos projets avec {$domainName}. »\n"
                    .'5. « Précision. Fiabilité. Impact immédiat. »',
            ],

            'video_script' => [
                'title' => "Script Vidéo 30s — {$topic}",
                'format' => 'Script de tournage audiovisuel',
                'tags' => ['Média', 'Vidéo', 'Production'],
                'content' => "**TITRE : Spot Promo 30 secondes — {$topic}**\n\n"
                    ."[00:00 - 00:05] PLAN 1 : Vue rythmée en gros plan sur l'activité en atelier/bureau. Musique entraînante et moderne.\n"
                    ."VOIX OFF : « Vous exigez le meilleur pour votre entreprise. Nous aussi. »\n\n"
                    ."[00:05 - 00:15] PLAN 2 : Démonstration dynamique du service {$topic}. Infographie textuelle épurée.\n"
                    ."VOIX OFF : « Avec {$domainName}, accédez à des standards d'excellence inédits, pensés pour accélérer vos résultats. »\n\n"
                    ."[00:15 - 00:25] PLAN 3 : Témoignage client souriant ou poignée de main professionnelle.\n"
                    ."VOIX OFF : « Plus de 500 projets livrés avec succès. Faites le choix de la certitude. »\n\n"
                    ."[00:25 - 00:30] PLAN 4 : Logo IVOSPHERE animé sur fond sombre (#0B0F14).\n"
                    ."VOIX OFF : « IVOSPHERE. Votre plateforme d'avenir. Contactez-nous dès aujourd'hui. »",
            ],

            'executive_report' => [
                'title' => "Compte Rendu Exécutif — {$topic}",
                'format' => 'Rapport de synthèse pour la Direction',
                'tags' => ['Direction', 'Gouvernance', 'Rapport'],
                'content' => "### SYNTHÈSE EXÉCUTIVE — {$topic}\n"
                    .'**Date :** '.Carbon::now()->isoFormat('D MMMM YYYY')."\n"
                    ."**Émetteur :** Direction Générale IVOSPHERE\n\n"
                    ."#### 1. Contexte & Objectifs Clés\n"
                    ."Le présent rapport a pour vocation de synthétiser les dynamiques opérationnelles et financières observées concernant {$topic}.\n\n"
                    ."#### 2. Faits Marquants & Indicateurs\n"
                    ."- Respect des délais d'engagement sur l'ensemble des axes stratégiques.\n"
                    ."- Consolidation de la synergie transversale entre les 5 pôles (PRINT, SPORT, TECH, MEDIA, ASSURANCE).\n"
                    .'- Alignement budgétaire rigoureux maintenant le taux de marge nette dans les objectifs prévisionnels.',
            ],

            default => [
                'title' => "Document Stratégique — {$topic}",
                'format' => 'Synthèse générale',
                'tags' => ['Général'],
                'content' => "Document généré pour {$topic} au sein de l'environnement {$domainName}.",
            ],
        };
    }

    protected function handleRevenueQuery(string $query): array
    {
        $domain = null;
        $domains = Domain::all();
        foreach ($domains as $d) {
            if (Str::contains($query, Str::lower($d->name)) || Str::contains($query, Str::lower($d->code))) {
                $domain = $d;
                break;
            }
        }

        $now = Carbon::now();
        $ordersQuery = Order::where('status', '!=', 'annulee');

        if ($domain) {
            $ordersQuery->where('domain_id', $domain->id);
            $domainText = "du pôle {$domain->name}";
        } else {
            $domainText = 'global (tous pôles confondus)';
        }

        if (Str::contains($query, ['aujourd\'hui', 'ce jour'])) {
            $ordersQuery->whereBetween('created_at', [$now->copy()->startOfDay(), $now->copy()->endOfDay()]);
            $periodText = "d'aujourd'hui";
        } elseif (Str::contains($query, ['cette semaine', 'semaine'])) {
            $ordersQuery->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
            $periodText = 'de cette semaine';
        } elseif (Str::contains($query, ['cette année', 'annee'])) {
            $ordersQuery->whereBetween('created_at', [$now->copy()->startOfYear(), $now->copy()->endOfYear()]);
            $periodText = 'de cette année';
        } else {
            $ordersQuery->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
            $periodText = 'de ce mois-ci ('.$now->translatedFormat('F Y').')';
        }

        $revenue = (float) $ordersQuery->sum('total');
        $count = $ordersQuery->count();

        $answer = "Le chiffre d'affaires {$domainText} {$periodText} s'élève à **"
            .number_format($revenue, 0, ',', ' ')." FCFA** sur un volume de **{$count} commande(s)**.";

        return [
            'intent' => 'revenue_inquiry',
            'answer' => $answer,
            'data' => [
                'revenue' => $revenue,
                'count' => $count,
                'domain' => $domain?->name,
                'period' => $periodText,
            ],
            'suggestions' => [
                'Quelles factures sont en retard ?',
                'Quels produits sont bientôt en rupture ?',
                'Résume les ventes de cette semaine',
            ],
        ];
    }

    protected function handleStockQuery(): array
    {
        $alerts = StockAlert::where('status', 'active')
            ->with(['product.domain', 'warehouse'])
            ->orderBy('current_quantity')
            ->take(8)
            ->get();

        if ($alerts->isEmpty()) {
            return [
                'intent' => 'stock_alerts',
                'answer' => '✅ **Excellente nouvelle !** Aucun produit n\'est actuellement sous son seuil d\'alerte. Tous les stocks sont au niveau optimal.',
                'data' => [],
                'suggestions' => [
                    'Donne-moi le chiffre d\'affaires de TECH ce mois-ci',
                    'Quelles factures sont en retard ?',
                ],
            ];
        }

        $lines = [];
        foreach ($alerts as $a) {
            $prodName = $a->product ? $a->product->name : 'Article';
            $domainCode = $a->product?->domain ? "({$a->product->domain->code})" : '';
            $lines[] = "• **{$prodName}** {$domainCode} : Stock : **{$a->current_quantity}** (seuil : {$a->min_quantity})";
        }

        $answer = '⚠️ **'.$alerts->count()." produit(s) nécessitent un réapprovisionnement urgent :**\n\n"
            .implode("\n", $lines);

        return [
            'intent' => 'stock_alerts',
            'answer' => $answer,
            'data' => $alerts->toArray(),
            'suggestions' => [
                'Créer un bon de commande fournisseur',
                'Donne-moi le chiffre d\'affaires de PRINT ce mois-ci',
            ],
        ];
    }

    protected function handleOverdueInvoicesQuery(): array
    {
        $allOverdue = Invoice::whereIn('status', ['non_payee', 'partielle'])
            ->where('due_date', '<', Carbon::now()->toDateString())
            ->with(['customer', 'domain'])
            ->orderBy('due_date')
            ->get();

        $totalOverdue = (float) $allOverdue->sum(fn ($i) => $i->total - $i->paid_amount);

        if ($allOverdue->isEmpty()) {
            return [
                'intent' => 'overdue_invoices',
                'answer' => '✅ **Aucune facture n\'est en retard d\'échéance à ce jour.** Toutes les créances clients sont à jour.',
                'data' => [],
                'suggestions' => [
                    'Résume les ventes de cette semaine',
                    'Combien avons-nous de trésorerie disponible ?',
                ],
            ];
        }

        $lines = [];
        foreach ($allOverdue->take(8) as $inv) {
            $clientName = $inv->customer ? ($inv->customer->company ?: $inv->customer->name) : 'Client';
            $balance = $inv->total - $inv->paid_amount;
            $daysLate = (int) Carbon::parse($inv->due_date)->diffInDays(Carbon::now());
            $lines[] = "• **{$inv->reference}** — {$clientName} : **".number_format($balance, 0, ',', ' ')." FCFA** (retard : {$daysLate} jours)";
        }

        $answer = '🚨 **'.$allOverdue->count()." facture(s) en retard d'échéance**, pour un total échu de **"
            .number_format($totalOverdue, 0, ',', ' ')." FCFA** :\n\n"
            .implode("\n", $lines);

        return [
            'intent' => 'overdue_invoices',
            'answer' => $answer,
            'data' => [
                'total_amount' => $totalOverdue,
                'invoices' => $allOverdue->take(8)->toArray(),
            ],
            'suggestions' => [
                'Générer un email de relance de facturation',
                'Quels clients n\'ont pas commandé depuis 3 mois ?',
            ],
        ];
    }

    protected function handleInactiveCustomersQuery(): array
    {
        $threeMonthsAgo = Carbon::now()->subMonths(3);

        $inactiveCustomers = Customer::whereDoesntHave('orders', function ($q) use ($threeMonthsAgo) {
            $q->where('created_at', '>=', $threeMonthsAgo);
        })
            ->where('status', 'actif')
            ->orderBy('created_at')
            ->take(6)
            ->get();

        if ($inactiveCustomers->isEmpty()) {
            return [
                'intent' => 'inactive_customers',
                'answer' => 'Tous vos clients actifs ont effectué au moins une commande au cours des 3 derniers mois.',
                'data' => [],
                'suggestions' => [
                    'Résume les ventes de cette semaine',
                    'Donne-moi le chiffre d\'affaires global ce mois-ci',
                ],
            ];
        }

        $lines = [];
        foreach ($inactiveCustomers as $c) {
            $name = $c->company ?: $c->name;
            $lines[] = "• **{$name}** (Tél : ".($c->phone ?: 'Non renseigné').')';
        }

        $answer = '📋 **Voici '.$inactiveCustomers->count()." clients n'ayant pas passé commande depuis plus de 3 mois :**\n\n"
            .implode("\n", $lines)
            ."\n\n💡 *Conseil IA : Lancez une campagne d'activation ou transmettez-leur une offre personnalisée via le studio.*";

        return [
            'intent' => 'inactive_customers',
            'answer' => $answer,
            'data' => $inactiveCustomers->toArray(),
            'suggestions' => [
                'Générer un email commercial de relance',
                'Quelles factures sont en retard ?',
            ],
        ];
    }

    protected function handleSalesSummaryQuery(string $query): array
    {
        $now = Carbon::now();
        $start = $now->copy()->startOfWeek();
        $end = $now->copy()->endOfWeek();

        $orders = Order::where('status', '!=', 'annulee')
            ->whereBetween('created_at', [$start, $end])
            ->with('domain')
            ->get();

        $totalSales = (float) $orders->sum('total');
        $ordersCount = $orders->count();

        $domainBreakdown = $orders->groupBy('domain.name')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => $group->sum('total'),
            ];
        });

        $breakdownLines = [];
        foreach ($domainBreakdown as $name => $stats) {
            $domainTitle = $name ?: 'Général';
            $breakdownLines[] = "• **{$domainTitle}** : {$stats['count']} commande(s) pour **".number_format($stats['total'], 0, ',', ' ').' FCFA**';
        }

        $answer = '📊 **Bilan des ventes de cette semaine ('.$start->format('d/m').' au '.$end->format('d/m/Y').") :**\n\n"
            ."• **Chiffre d'affaires total :** **".number_format($totalSales, 0, ',', ' ')." FCFA**\n"
            ."• **Nombre de commandes :** **{$ordersCount}**\n\n"
            ."**Répartition par domaine d'activité :**\n"
            .(empty($breakdownLines) ? "• Aucune commande enregistrée cette semaine.\n" : implode("\n", $breakdownLines));

        return [
            'intent' => 'sales_summary',
            'answer' => $answer,
            'data' => [
                'total' => $totalSales,
                'count' => $ordersCount,
            ],
            'suggestions' => [
                'Combien avons-nous de trésorerie disponible ?',
                'Quels produits sont bientôt en rupture ?',
            ],
        ];
    }

    protected function handleCashQuery(): array
    {
        $cashRegisters = CashRegister::where('is_active', true)->get();
        $totalBalance = (float) $cashRegisters->sum('balance');

        $lines = [];
        foreach ($cashRegisters as $reg) {
            $lines[] = "• **{$reg->name}** : **".number_format($reg->balance, 0, ',', ' ').' FCFA**';
        }

        $answer = "💰 **Trésorerie disponible dans les caisses actives :**\n\n"
            .'• **Solde global consolidé :** **'.number_format($totalBalance, 0, ',', ' ')." FCFA**\n\n"
            ."**Détail par caisse :**\n"
            .(empty($lines) ? "• Aucune caisse enregistrée.\n" : implode("\n", $lines));

        return [
            'intent' => 'cash_position',
            'answer' => $answer,
            'data' => [
                'total_balance' => $totalBalance,
                'registers' => $cashRegisters->toArray(),
            ],
            'suggestions' => [
                'Donne-moi le chiffre d\'affaires de TECH ce mois-ci',
                'Quelles factures sont en retard ?',
            ],
        ];
    }

    protected function handleProjectsQuery(): array
    {
        $projects = TechProject::whereIn('status', ['nouveau', 'en_cours', 'en_recette'])
            ->with('customer')
            ->orderBy('deadline')
            ->take(6)
            ->get();

        if ($projects->isEmpty()) {
            return [
                'intent' => 'tech_projects',
                'answer' => 'Aucun projet tech n\'est actuellement en cours de développement.',
                'data' => [],
                'suggestions' => ['Résume les ventes de cette semaine'],
            ];
        }

        $lines = [];
        foreach ($projects as $proj) {
            $client = $proj->customer ? ($proj->customer->company ?: $proj->customer->name) : 'Interne';
            $progress = $proj->progress ?? 0;
            $lines[] = "• **{$proj->name}** ({$client}) — Avancement : **{$progress}%** (Statut : {$proj->status})";
        }

        $answer = '💻 **'.$projects->count()." projet(s) TECH en cours de réalisation :**\n\n"
            .implode("\n", $lines);

        return [
            'intent' => 'tech_projects',
            'answer' => $answer,
            'data' => $projects->toArray(),
            'suggestions' => [
                'Donne-moi le chiffre d\'affaires de TECH ce mois-ci',
                'Combien avons-nous de trésorerie disponible ?',
            ],
        ];
    }

    protected function handleGeneralQuery(string $query): array
    {
        $now = Carbon::now();
        $monthRevenue = (float) Order::where('status', '!=', 'annulee')
            ->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->sum('total');

        $overdueCount = Invoice::whereIn('status', ['non_payee', 'partielle'])
            ->where('due_date', '<', $now->toDateString())
            ->count();

        $answer = "Bonjour ! Je suis **IVOSPHERE AI**, votre assistant de pilotage connecté en temps réel aux données de l'entreprise.\n\n"
            ."Pour répondre précisément à votre question (« *{$query}* »), voici un point de situation clé :\n"
            .'• **CA du mois en cours :** '.number_format($monthRevenue, 0, ',', ' ')." FCFA\n"
            ."• **Factures en retard :** {$overdueCount} alerte(s)\n\n"
            ."Vous pouvez me formuler des demandes précises en langage naturel comme :\n"
            ."1. *« Donne-moi le chiffre d'affaires de TECH ce mois-ci »*\n"
            ."2. *« Quels produits sont bientôt en rupture ? »*\n"
            ."3. *« Quelles factures sont en retard ? »*\n"
            .'4. *« Résume les ventes de cette semaine »*';

        return [
            'intent' => 'general_inquiry',
            'answer' => $answer,
            'data' => null,
            'suggestions' => [
                'Donne-moi le chiffre d\'affaires de TECH ce mois-ci',
                'Quels produits sont bientôt en rupture ?',
                'Quelles factures sont en retard ?',
                'Résume les ventes de cette semaine',
            ],
        ];
    }
}
