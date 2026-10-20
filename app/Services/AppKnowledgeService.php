<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\CommercialAppointment;
use App\Models\CyberPrintRequest;
use App\Models\Employee;
use App\Models\InsuranceContract;
use App\Models\LeaveRequest;
use App\Models\Order;
use App\Models\PrintJob;
use App\Models\Product;
use App\Models\Prospect;
use App\Models\Quotation;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Connaissances de l'IA générale sur le fonctionnement de l'entreprise au-delà des chiffres de vente :
 * présences, congés, rendez-vous, prospects, devis, cyber, stock, fournisseurs, paramètres…
 * Chaque sujet n'est donné qu'aux utilisateurs qui ont accès à la page correspondante.
 */
class AppKnowledgeService
{
    /**
     * Sujets reconnus : motif de détection, page dont l'accès est requis, permission IA éventuelle.
     *
     * @var array<string, array{pattern: string, route: ?string, permission: ?string}>
     */
    private const TOPICS = [
        'my_role' => ['pattern' => '/ (mon role|mes roles|mes droits|mes permissions|qui suis je|a quoi ai je acces) /', 'route' => null, 'permission' => null],
        'my_day' => ['pattern' => '/ (ma journee|qu ai je fait|mon activite du jour|mon bilan du jour|mes ventes du jour) /', 'route' => 'my-day', 'permission' => null],
        'company' => ['pattern' => '/ (rccm|cnps|compte contribuable|coordonnees|nom de l entreprise|raison sociale|adresse de l entreprise|telephone de l entreprise|email de l entreprise|e mail de contact|email de contact|forme juridique|capital social|regime fiscal|centre des impots|taux de tva|tva par defaut|devise|rib|banque de l entreprise|infos de l entreprise|informations de l entreprise|parametres de l entreprise|identite de l entreprise) /', 'route' => null, 'permission' => null],
        'cyber_performance' => ['pattern' => '/ (meilleur agent|meilleurs agents|performance des agents|classement des agents|agents? (du )?cyber|agent le plus performant) /', 'route' => 'commercial.cyber.performance', 'permission' => null],
        'cyber_deposits' => ['pattern' => '/ (depots? d impression|documents? (a imprimer|recus|deposes)|depots? (du )?cyber|depots? en attente) /', 'route' => 'commercial.cyber.index', 'permission' => null],
        'leaves' => ['pattern' => '/ (conge|conges|en conge|demandes? de conge) /', 'route' => 'rh.leaves.index', 'permission' => null],
        'workforce' => ['pattern' => '/ (effectif|effectifs|combien d employes|nombre d employes|combien de salaries|employes actifs|combien de personnes travaillent) /', 'route' => 'rh.employees.index', 'permission' => null],
        'appointments' => ['pattern' => '/ (rendez vous|rdv|agenda commercial) /', 'route' => 'commercial.appointments.index', 'permission' => null],
        'prospects' => ['pattern' => '/ (prospect|prospects|pipeline|opportunite|opportunites) /', 'route' => 'commercial.prospects.index', 'permission' => null],
        'quotations' => ['pattern' => '/ (devis) /', 'route' => 'commercial.quotations.index', 'permission' => 'ai.sales'],
        'orders' => ['pattern' => '/ (commandes? (en cours|en attente|actives?|a livrer|a traiter|non livrees?)) /', 'route' => 'commercial.orders.index', 'permission' => 'ai.sales'],
        'stock_value' => ['pattern' => '/ (valeur du stock|valeur de stock|valeur stock|valorisation du stock|entrepot|entrepots|depots de stockage) /', 'route' => 'stock.index', 'permission' => 'ai.stock'],
        'suppliers' => ['pattern' => '/ (fournisseur|fournisseurs) /', 'route' => 'commercial.suppliers.index', 'permission' => null],
        'users' => ['pattern' => '/ (combien d utilisateurs|nombre d utilisateurs|utilisateurs actifs|comptes utilisateurs|liste des utilisateurs) /', 'route' => 'admin.users.index', 'permission' => null],
        'insurance' => ['pattern' => '/ (contrats? d assurance|polices? d assurance|assurances? (qui )?expirent|echeances? d assurance) /', 'route' => 'assurance.contracts.index', 'permission' => null],
        'print_jobs' => ['pattern' => '/ (travaux d impression|travail d impression|travaux print|commandes d impression|bat en attente) /', 'route' => 'print.jobs.index', 'permission' => null],
        'attendance' => ['pattern' => '/ (present|presents|presente|presentes|presence|presences|absent|absents|absente|pointe|pointes|pointage|retard|retards|en retard) /', 'route' => 'rh.attendance.index', 'permission' => null],
    ];

    public function __construct(
        protected AppNavigator $navigator,
        protected UserDailyActivityService $dailyActivity,
        protected CyberAgentPerformanceService $cyberPerformance,
    ) {}

    /**
     * Réponse si la question porte sur un sujet connu, sinon null.
     *
     * @return array{intent: string, answer: string, metrics: list<array<string, mixed>>, table: array{columns: list<string>, rows: list<list<string>>}|null, links: list<array{label: string, module: string, url: string}>}|null
     */
    public function answer(string $query, User $user): ?array
    {
        $text = ' '.$this->normalize($query).' ';
        $topic = collect(self::TOPICS)->search(fn (array $definition) => (bool) preg_match($definition['pattern'], $text));

        if ($topic === false) {
            return null;
        }

        $definition = self::TOPICS[$topic];
        $allowed = ($definition['route'] === null || $this->navigator->canAccess($user, $definition['route']))
            && ($definition['permission'] === null || $user->hasPermission($definition['permission']))
            && ($topic !== 'cyber_performance' || $user->hasPermission('cyber.performance'));

        if (! $allowed) {
            return $this->result('forbidden', "Votre rôle ne vous permet pas d'interroger l'assistant sur ce sujet. Rapprochez-vous d'un administrateur si vous en avez besoin.");
        }

        $result = match ($topic) {
            'my_role' => $this->myRole($user),
            'my_day' => $this->myDay($user),
            'company' => $this->company($user),
            'cyber_performance' => $this->cyberAgents(),
            'cyber_deposits' => $this->cyberDeposits(),
            'attendance' => $this->attendance(),
            'leaves' => $this->leaves(),
            'workforce' => $this->workforce(),
            'appointments' => $this->appointments(),
            'prospects' => $this->prospects(),
            'quotations' => $this->quotations(),
            'orders' => $this->orders(),
            'stock_value' => $this->stockValue(),
            'suppliers' => $this->suppliers(),
            'users' => $this->users(),
            'insurance' => $this->insurance(),
            'print_jobs' => $this->printJobs(),
        };

        if ($definition['route'] !== null && $result['links'] === []) {
            $result['links'] = [$this->link($definition['route'])];
        }

        return $result;
    }

    /**
     * @return array{intent: string, answer: string, metrics: list<array<string, mixed>>, table: array{columns: list<string>, rows: list<list<string>>}|null, links: list<array{label: string, module: string, url: string}>}
     */
    private function myRole(User $user): array
    {
        $modules = $this->navigator->modulesFor($user);
        $roles = $user->roles->pluck('name')->implode(', ') ?: 'aucun rôle';

        return $this->result('my_role',
            $user->isAdmin()
                ? "Vous êtes **{$user->name}**, **administrateur** : vous avez accès à tous les modules et à toutes les actions."
                : "Vous êtes **{$user->name}**, rôle : **{$roles}**. Vous avez accès à **".count($modules).' module(s)** : **'.(implode('**, **', $modules) ?: 'aucun').'**.',
            metrics: [['label' => 'Modules accessibles', 'value' => (string) count($modules)]],
            links: [['label' => 'Mon compte', 'module' => 'Général', 'url' => route('password.edit')]],
        );
    }

    private function myDay(User $user): array
    {
        $day = $this->dailyActivity->forDay($user, today());
        $attendance = $day['attendance'];

        return $this->result('my_day',
            "Aujourd'hui : **{$day['sales']['invoices']} facture(s)** pour **{$this->money($day['sales']['invoiced'])}**, **{$day['payments']['count']} encaissement(s)** pour **{$this->money($day['payments']['total'])}**"
                .($day['tasks']['total'] > 0 ? ", tâches : **{$day['tasks']['done']}/{$day['tasks']['total']}**" : '')
                .($attendance ? ', arrivée pointée à **'.($attendance->check_in ?? $attendance->check_in_at?->format('H:i')).'**' : ', aucun pointage aujourd\'hui').'.',
            metrics: [
                ['label' => 'Facturé', 'value' => $this->money($day['sales']['invoiced'])],
                ['label' => 'Encaissé', 'value' => $this->money($day['payments']['total'])],
                ['label' => 'Devis', 'value' => (string) $day['sales']['quotations']],
            ],
        );
    }

    private function company(User $user): array
    {
        $fields = [
            'company_name' => 'Raison sociale',
            'company_legal_form' => 'Forme juridique',
            'company_capital' => 'Capital',
            'company_address' => 'Adresse',
            'company_postal_box' => 'Boîte postale',
            'company_phone' => 'Téléphone',
            'company_email' => 'E-mail',
            'company_rccm' => 'RCCM',
            'company_cc' => 'Compte contribuable',
            'company_tax_regime' => 'Régime fiscal',
            'company_tax_center' => 'Centre des impôts',
            'company_cnps' => 'CNPS',
            'company_bank_name' => 'Banque',
            'default_tax_rate' => 'TVA par défaut (%)',
            'currency' => 'Devise',
        ];

        $rows = collect($fields)
            ->map(fn (string $label, string $key) => [$label, trim((string) Setting::get($key, ''))])
            ->filter(fn (array $row) => $row[1] !== '')
            ->values()
            ->all();

        return $this->result('company',
            $rows === [] ? "Les informations de l'entreprise ne sont pas encore renseignées." : "Voici l'identité de l'entreprise enregistrée dans les paramètres :",
            table: $rows === [] ? null : ['columns' => ['Information', 'Valeur'], 'rows' => $rows],
            links: $this->navigator->canAccess($user, 'admin.settings.index') ? [$this->link('admin.settings.index', 'Paramètres de l\'entreprise')] : [],
        );
    }

    private function cyberAgents(): array
    {
        $report = $this->cyberPerformance->forMonth(now());
        $ranked = collect($report['agents'])->filter(fn (array $agent) => $agent['rank'] !== null)->values();

        if ($ranked->isEmpty()) {
            return $this->result('cyber_performance', 'Aucune prestation cyber attribuée à un agent ce mois-ci.');
        }

        $best = $ranked->first();

        return $this->result('cyber_performance',
            "Ce mois-ci, l'agent le plus performant du cyber est **{$best['name']}** avec **{$this->money($best['revenue'])}** générés pour **{$best['clients']} client(s)**. Seuil de performance : **{$this->money($report['totals']['threshold'])}**.",
            table: [
                'columns' => ['Rang', 'Agent', 'Clients', 'Montant', 'Statut'],
                'rows' => $ranked->take(10)->map(fn (array $agent) => [(string) $agent['rank'], $agent['name'], (string) $agent['clients'], $this->money($agent['revenue']), $agent['status']])->all(),
            ],
        );
    }

    private function cyberDeposits(): array
    {
        $open = CyberPrintRequest::open()->with('files')->latest('id')->get();

        if ($open->isEmpty()) {
            return $this->result('cyber_deposits', "Aucun document en attente d'impression au cyber.");
        }

        return $this->result('cyber_deposits',
            "**{$open->count()} dépôt(s)** en attente au cyber, pour **".$open->sum(fn (CyberPrintRequest $deposit) => $deposit->totalPages()).' page(s)** à imprimer.',
            table: [
                'columns' => ['Code', 'Client', 'Pages', 'Statut'],
                'rows' => $open->take(10)->map(fn (CyberPrintRequest $deposit) => [
                    $deposit->code,
                    (string) $deposit->customer_name,
                    (string) $deposit->totalPages(),
                    CyberPrintRequest::STATUSES[$deposit->status][0] ?? $deposit->status,
                ])->all(),
            ],
        );
    }

    private function attendance(): array
    {
        $active = Employee::active()->get(['id', 'first_name', 'last_name']);
        $today = Attendance::whereDate('date', today())->whereIn('employee_id', $active->pluck('id'))->get()->keyBy('employee_id');
        $present = $active->filter(fn (Employee $employee) => $today->has($employee->id));
        $absent = $active->reject(fn (Employee $employee) => $today->has($employee->id));

        return $this->result('attendance',
            "Aujourd'hui : **{$present->count()} présent(s)** sur **{$active->count()} employé(s) actif(s)**, **{$absent->count()} sans pointage**.",
            metrics: [
                ['label' => 'Présents', 'value' => (string) $present->count()],
                ['label' => 'Sans pointage', 'value' => (string) $absent->count()],
            ],
            table: $active->isEmpty() ? null : [
                'columns' => ['Employé', 'Arrivée', 'Départ'],
                'rows' => $active->take(20)->map(function (Employee $employee) use ($today) {
                    $attendance = $today->get($employee->id);

                    return [
                        $employee->full_name,
                        $attendance ? (string) ($attendance->check_in ?? $attendance->check_in_at?->format('H:i') ?? '—') : 'Absent',
                        $attendance ? (string) ($attendance->check_out ?? $attendance->check_out_at?->format('H:i') ?? '—') : '—',
                    ];
                })->all(),
            ],
        );
    }

    private function leaves(): array
    {
        $pending = LeaveRequest::with('employee')->where('status', 'en_attente')->get();
        $current = LeaveRequest::with('employee')
            ->whereIn('status', ['approuve', 'approuvé', 'approved'])
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->get();

        return $this->result('leaves',
            "**{$current->count()} employé(s)** en congé aujourd'hui et **{$pending->count()} demande(s)** en attente de validation.",
            metrics: [
                ['label' => 'En congé', 'value' => (string) $current->count()],
                ['label' => 'À valider', 'value' => (string) $pending->count()],
            ],
            table: $current->concat($pending)->isEmpty() ? null : [
                'columns' => ['Employé', 'Du', 'Au', 'Statut'],
                'rows' => $current->concat($pending)->take(15)->map(fn (LeaveRequest $leave) => [
                    (string) $leave->employee?->full_name,
                    Carbon::parse($leave->start_date)->format('d/m'),
                    Carbon::parse($leave->end_date)->format('d/m'),
                    $leave->status === 'en_attente' ? 'À valider' : 'En congé',
                ])->all(),
            ],
        );
    }

    private function workforce(): array
    {
        $active = Employee::active()->get(['id', 'department', 'position']);
        $byDepartment = $active->groupBy(fn (Employee $employee) => $employee->department ?: 'Non renseigné')->map->count()->sortDesc();

        return $this->result('workforce',
            "L'entreprise compte **{$active->count()} employé(s) actif(s)**.",
            table: $byDepartment->isEmpty() ? null : [
                'columns' => ['Service', 'Employés'],
                'rows' => $byDepartment->map(fn (int $count, string $department) => [$department, (string) $count])->values()->all(),
            ],
        );
    }

    private function appointments(): array
    {
        $upcoming = CommercialAppointment::with(['customer', 'prospect'])
            ->whereDate('date', '>=', today())
            ->whereDate('date', '<=', today()->addDays(7))
            ->whereNotIn('status', ['annulé', 'annule', 'effectué', 'effectue'])
            ->orderBy('date')->orderBy('time')
            ->get();
        $todayCount = $upcoming->filter(fn (CommercialAppointment $appointment) => Carbon::parse($appointment->date)->isToday())->count();

        return $this->result('appointments',
            "**{$todayCount} rendez-vous** aujourd'hui et **{$upcoming->count()}** sur les 7 prochains jours.",
            table: $upcoming->isEmpty() ? null : [
                'columns' => ['Date', 'Heure', 'Objet', 'Avec'],
                'rows' => $upcoming->take(12)->map(fn (CommercialAppointment $appointment) => [
                    Carbon::parse($appointment->date)->format('d/m'),
                    (string) ($appointment->time ?? '—'),
                    (string) $appointment->title,
                    (string) ($appointment->customer?->name ?? $appointment->prospect?->name ?? '—'),
                ])->all(),
            ],
        );
    }

    private function prospects(): array
    {
        $open = Prospect::whereNotIn('stage', ['gagne', 'perdu'])->get(['id', 'name', 'stage', 'estimated_value', 'probability', 'next_follow_up']);
        $weighted = $open->sum(fn (Prospect $prospect) => (float) $prospect->estimated_value * (int) $prospect->probability / 100);
        $followUps = $open->filter(fn (Prospect $prospect) => $prospect->next_follow_up !== null && $prospect->next_follow_up->lte(today()))->count();

        return $this->result('prospects',
            "**{$open->count()} prospect(s)** en cours, pour un potentiel pondéré de **{$this->money($weighted)}**. **{$followUps}** sont à relancer.",
            metrics: [
                ['label' => 'Prospects ouverts', 'value' => (string) $open->count()],
                ['label' => 'Potentiel pondéré', 'value' => $this->money($weighted)],
                ['label' => 'À relancer', 'value' => (string) $followUps],
            ],
            table: $open->isEmpty() ? null : [
                'columns' => ['Étape', 'Prospects'],
                'rows' => $open->groupBy('stage')->map(fn ($group, $stage) => [Str::headline((string) $stage), (string) $group->count()])->values()->all(),
            ],
        );
    }

    private function quotations(): array
    {
        $pending = Quotation::with('customer')->whereIn('status', ['envoye', 'envoyé', 'brouillon'])->orderBy('valid_until')->get();
        $expiring = $pending->filter(fn (Quotation $quotation) => $quotation->valid_until !== null && $quotation->valid_until->between(today(), today()->addDays(7)))->count();

        return $this->result('quotations',
            "**{$pending->count()} devis** en attente pour **{$this->money((float) $pending->sum('total'))}**, dont **{$expiring}** qui expirent dans les 7 jours.",
            table: $pending->isEmpty() ? null : [
                'columns' => ['Devis', 'Client', 'Montant', 'Valable jusqu\'au'],
                'rows' => $pending->take(12)->map(fn (Quotation $quotation) => [
                    (string) $quotation->reference,
                    (string) ($quotation->customer?->name ?? '—'),
                    $this->money((float) $quotation->total),
                    $quotation->valid_until?->format('d/m/Y') ?? '—',
                ])->all(),
            ],
        );
    }

    private function orders(): array
    {
        $active = Order::with('customer')->whereIn('status', ['en_attente', 'confirmee', 'confirmée', 'en_cours'])->latest('date')->get();

        return $this->result('orders',
            "**{$active->count()} commande(s)** en cours de traitement pour **{$this->money((float) $active->sum('total'))}**.",
            table: $active->isEmpty() ? null : [
                'columns' => ['Commande', 'Client', 'Date', 'Montant'],
                'rows' => $active->take(12)->map(fn (Order $order) => [
                    (string) $order->reference,
                    (string) ($order->customer?->name ?? '—'),
                    $order->date?->format('d/m') ?? '—',
                    $this->money((float) $order->total),
                ])->all(),
            ],
        );
    }

    private function stockValue(): array
    {
        $products = Product::active()->with('warehouseStocks')->get();
        $value = $products->sum(fn (Product $product) => max(0, $product->current_stock) * (float) $product->purchase_price);
        $saleValue = $products->sum(fn (Product $product) => max(0, $product->current_stock) * (float) $product->selling_price);
        $warehouses = Warehouse::where('is_active', true)->withCount('warehouseStocks')->get();

        return $this->result('stock_value',
            "Le stock vaut **{$this->money($value)}** au prix d'achat (**{$this->money($saleValue)}** au prix de vente), sur **{$products->count()} article(s)** et **{$warehouses->count()} entrepôt(s)** actif(s).",
            metrics: [
                ['label' => 'Valeur d\'achat', 'value' => $this->money($value)],
                ['label' => 'Valeur de vente', 'value' => $this->money($saleValue)],
                ['label' => 'Marge potentielle', 'value' => $this->money($saleValue - $value)],
            ],
            table: $warehouses->isEmpty() ? null : [
                'columns' => ['Entrepôt', 'Code', 'Références stockées'],
                'rows' => $warehouses->map(fn (Warehouse $warehouse) => [(string) $warehouse->name, (string) $warehouse->code, (string) $warehouse->warehouse_stocks_count])->all(),
            ],
        );
    }

    private function suppliers(): array
    {
        $suppliers = Supplier::orderBy('name')->get(['id', 'name', 'phone', 'city', 'status']);
        $active = $suppliers->where('status', '!=', 'inactif');

        return $this->result('suppliers',
            "Vous avez **{$active->count()} fournisseur(s)** actif(s).",
            table: $active->isEmpty() ? null : [
                'columns' => ['Fournisseur', 'Téléphone', 'Ville'],
                'rows' => $active->take(15)->map(fn (Supplier $supplier) => [(string) $supplier->name, (string) ($supplier->phone ?? '—'), (string) ($supplier->city ?? '—')])->values()->all(),
            ],
        );
    }

    private function users(): array
    {
        $users = User::with('roles')->get(['id', 'name', 'is_active']);
        $byRole = $users->flatMap(fn (User $user) => $user->roles->pluck('name'))->countBy()->sortDesc();

        return $this->result('users',
            "**{$users->where('is_active', true)->count()} utilisateur(s)** actif(s) sur **{$users->count()}** compte(s).",
            table: $byRole->isEmpty() ? null : [
                'columns' => ['Rôle', 'Utilisateurs'],
                'rows' => $byRole->map(fn (int $count, string $role) => [$role, (string) $count])->values()->all(),
            ],
        );
    }

    private function insurance(): array
    {
        $active = InsuranceContract::with('customer')->where('status', '!=', 'resilie')->whereDate('end_date', '>=', today())->orderBy('end_date')->get();
        $expiring = $active->filter(fn (InsuranceContract $contract) => Carbon::parse($contract->end_date)->lte(today()->addDays(30)));

        return $this->result('insurance',
            "**{$active->count()} contrat(s)** d'assurance en cours, dont **{$expiring->count()}** qui arrivent à échéance dans les 30 jours.",
            table: $expiring->isEmpty() ? null : [
                'columns' => ['Contrat', 'Client', 'Échéance'],
                'rows' => $expiring->take(12)->map(fn (InsuranceContract $contract) => [
                    (string) $contract->reference,
                    (string) ($contract->customer?->name ?? '—'),
                    Carbon::parse($contract->end_date)->format('d/m/Y'),
                ])->values()->all(),
            ],
        );
    }

    private function printJobs(): array
    {
        $open = PrintJob::with('customer')->whereNotIn('status', ['terminé', 'termine', 'livré', 'livre', 'annulé', 'annule'])->orderBy('deadline')->get();
        $late = $open->filter(fn (PrintJob $job) => $job->deadline !== null && Carbon::parse($job->deadline)->lt(today()))->count();

        return $this->result('print_jobs',
            "**{$open->count()} travail(aux) d'impression** en cours, dont **{$late} en retard**.",
            table: $open->isEmpty() ? null : [
                'columns' => ['Référence', 'Client', 'Échéance', 'Statut'],
                'rows' => $open->take(12)->map(fn (PrintJob $job) => [
                    (string) $job->reference,
                    (string) ($job->customer?->name ?? '—'),
                    $job->deadline ? Carbon::parse($job->deadline)->format('d/m') : '—',
                    Str::headline((string) $job->status),
                ])->all(),
            ],
        );
    }

    /**
     * @param  list<array<string, mixed>>  $metrics
     * @param  array{columns: list<string>, rows: list<list<string>>}|null  $table
     * @param  list<array{label: string, module: string, url: string}>  $links
     * @return array{intent: string, answer: string, metrics: list<array<string, mixed>>, table: array{columns: list<string>, rows: list<list<string>>}|null, links: list<array{label: string, module: string, url: string}>}
     */
    private function result(string $intent, string $answer, array $metrics = [], ?array $table = null, array $links = []): array
    {
        return ['intent' => $intent, 'answer' => $answer, 'metrics' => $metrics, 'table' => $table, 'links' => $links];
    }

    /**
     * @return array{label: string, module: string, url: string}
     */
    private function link(string $route, ?string $label = null): array
    {
        $page = collect(config('navigation.assistant.pages', []))->firstWhere('route', $route);
        $module = collect(config('navigation.modules', []))->first(fn (array $module) => collect($module['items'])->contains('route', $route));

        return [
            'label' => $label ?? $page['label'] ?? collect($module['items'] ?? [])->firstWhere('route', $route)['label'] ?? 'Ouvrir la page',
            'module' => $page['module'] ?? $module['label'] ?? 'Général',
            'url' => route($route),
        ];
    }

    private function money(float $amount): string
    {
        return number_format($amount, 0, ',', ' ').' FCFA';
    }

    private function normalize(string $text): string
    {
        return trim((string) preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9]+/', ' ', Str::lower(Str::ascii($text)))));
    }
}
