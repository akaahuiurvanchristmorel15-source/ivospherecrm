<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\CommandCenterController;
use App\Http\Controllers\Admin\DomainController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Ai\AiAssistantController;
use App\Http\Controllers\Analytics\AnalyticsController;
use App\Http\Controllers\Assurance\AssuranceDashboardController;
use App\Http\Controllers\Assurance\InsuranceAppointmentController;
use App\Http\Controllers\Assurance\InsuranceCommissionController;
use App\Http\Controllers\Assurance\InsuranceContractController;
use App\Http\Controllers\Assurance\InsuranceProductController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Automation\AlertController;
use App\Http\Controllers\Automation\AutomationController;
use App\Http\Controllers\Calendar\CalendarController;
use App\Http\Controllers\ClientPortal\ClientPortalController;
use App\Http\Controllers\Commercial\CommercialAppointmentController;
use App\Http\Controllers\Commercial\CustomerController;
use App\Http\Controllers\Commercial\DeliveryController;
use App\Http\Controllers\Commercial\InvoiceController;
use App\Http\Controllers\Commercial\OrderController;
use App\Http\Controllers\Commercial\PaymentController;
use App\Http\Controllers\Commercial\PosController;
use App\Http\Controllers\Commercial\ProductController;
use App\Http\Controllers\Commercial\PromotionController;
use App\Http\Controllers\Commercial\ProspectController;
use App\Http\Controllers\Commercial\QuotationController;
use App\Http\Controllers\Commercial\SupplierController;
use App\Http\Controllers\Contract\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Finance\AssetMaintenanceController;
use App\Http\Controllers\Finance\AssetRentalController;
use App\Http\Controllers\Finance\AssetUsageController;
use App\Http\Controllers\Finance\BudgetController;
use App\Http\Controllers\Finance\CashRegisterController;
use App\Http\Controllers\Finance\ExpenseController;
use App\Http\Controllers\Finance\FinanceDashboardController;
use App\Http\Controllers\Finance\FixedAssetController;
use App\Http\Controllers\Finance\RevenueController;
use App\Http\Controllers\Finance\TransactionController;
use App\Http\Controllers\Ged\GedController;
use App\Http\Controllers\Media\EquipmentController;
use App\Http\Controllers\Media\EquipmentRentalController;
use App\Http\Controllers\Media\EventController;
use App\Http\Controllers\Media\MediaDashboardController;
use App\Http\Controllers\Media\PhotoSessionController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\Messaging\InternalMessageController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Print\PrintDashboardController;
use App\Http\Controllers\Print\PrintJobController;
use App\Http\Controllers\QuoteRequestController;
use App\Http\Controllers\Rh\AttendanceController;
use App\Http\Controllers\Rh\DailyTaskSheetController;
use App\Http\Controllers\Rh\EmployeeController;
use App\Http\Controllers\Rh\EmployeeGoalController;
use App\Http\Controllers\Rh\EmployeePortalController;
use App\Http\Controllers\Rh\EvaluationController;
use App\Http\Controllers\Rh\EvaluationCriterionController;
use App\Http\Controllers\Rh\HrSettingController;
use App\Http\Controllers\Rh\LeaveRequestController;
use App\Http\Controllers\Rh\PerformanceImprovementPlanController;
use App\Http\Controllers\Rh\RhDashboardController;
use App\Http\Controllers\Rh\SalesTargetController;
use App\Http\Controllers\Rh\ScheduleController;
use App\Http\Controllers\Sport\SportArticleController;
use App\Http\Controllers\Sport\SportDashboardController;
use App\Http\Controllers\Stock\StockAlertController;
use App\Http\Controllers\Stock\StockDashboardController;
use App\Http\Controllers\Stock\StockMovementController;
use App\Http\Controllers\Stock\StockProductController;
use App\Http\Controllers\Stock\WarehouseController;
use App\Http\Controllers\Support\SupportTicketController;
use App\Http\Controllers\Tech\AiCampaignController;
use App\Http\Controllers\Tech\TechDashboardController;
use App\Http\Controllers\Tech\TechProjectController;
use App\Http\Controllers\Tools\GlobalSearchController;
use App\Http\Controllers\Tools\SimulatorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — ERP IVOSPHERE
|--------------------------------------------------------------------------
*/

// Page d'accueil officielle IVOSPHERE
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Demandes de devis depuis la page d'accueil
Route::post('/devis', [QuoteRequestController::class, 'store'])->name('quote.store');
Route::post('/', [QuoteRequestController::class, 'store']);

// Authentification
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Espace Authentifié
Route::middleware('auth')->group(function () {
    // Tableau de bord principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Menu Grid — Hub des 8 Pôles Amplifiés
    Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');

    // Administration Générale (Administrateur uniquement)
    Route::prefix('admin')->name('admin.')->middleware('role:administrateur')->group(function () {
        // Utilisateurs
        Route::resource('users', UserController::class)->except(['show']);
        Route::patch('users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');

        // Rôles & Permissions
        Route::get('roles', [RolePermissionController::class, 'index'])->name('roles.index');
        Route::put('roles/{role}/permissions', [RolePermissionController::class, 'updateRolePermissions'])->name('roles.update');

        // Domaines IVOSPHERE
        Route::post('domains/batch', [DomainController::class, 'storeBatch'])->name('domains.batch');
        Route::patch('domains/{domain}/toggle', [DomainController::class, 'toggleStatus'])->name('domains.toggle');
        Route::resource('domains', DomainController::class);

        // Journal d'activité (Audit Trail)
        Route::get('logs', [ActivityLogController::class, 'index'])->name('logs.index');

        // Paramètres système
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('settings/pole-images', [SettingController::class, 'updatePoleImages'])->name('settings.pole-images');
    });

    // Pôles Transversaux — Navigation d'accès selon les Rôles et Domaines
    Route::prefix('rh')->name('rh.')->middleware('role:administrateur,responsable,responsable_rh')->group(function () {
        Route::get('/', [RhDashboardController::class, 'index'])->name('index');
        Route::resource('employees', EmployeeController::class);
        Route::resource('leaves', LeaveRequestController::class)->except(['show', 'edit', 'update']);
        Route::patch('leaves/{leave}/approve', [LeaveRequestController::class, 'approve'])->name('leaves.approve');
        Route::patch('leaves/{leave}/reject', [LeaveRequestController::class, 'reject'])->name('leaves.reject');
        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('attendance/terminal', [AttendanceController::class, 'terminal'])->name('attendance.terminal');
        Route::get('attendance/dynamic-qr-token', [AttendanceController::class, 'dynamicQrToken'])->name('attendance.dynamic-qr-token');
        Route::get('attendance/poster', [AttendanceController::class, 'poster'])->name('attendance.poster');

        // Plannings des Employés (Matrice Hebdomadaire & Règle des 6 jours max)
        Route::get('schedules', [ScheduleController::class, 'index'])->name('schedules.index');
        Route::get('schedules/create', [ScheduleController::class, 'create'])->name('schedules.create');
        Route::post('schedules', [ScheduleController::class, 'store'])->name('schedules.store');
        Route::post('schedules/apply-standard', [ScheduleController::class, 'applyStandardToAll'])->name('schedules.apply-standard');
        Route::delete('schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');

        Route::resource('sales-targets', SalesTargetController::class);
        Route::post('sales-targets/{salesTarget}/recalculate', [SalesTargetController::class, 'recalculate'])->name('sales-targets.recalculate');

        // Paramètres RH (Horaires, Géofencing, Évaluations)
        Route::get('settings', [HrSettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [HrSettingController::class, 'update'])->name('settings.update');
        Route::post('settings/regenerate-qr', [HrSettingController::class, 'regenerateQrSecret'])->name('settings.regenerate-qr');

        // Fiches de Tâches du Jour (Attribution, Checklist, WhatsApp & PDF)
        Route::get('daily-tasks/{dailyTask}/print', [DailyTaskSheetController::class, 'print'])->name('daily-tasks.print');
        Route::patch('daily-tasks/{dailyTask}/toggle-task', [DailyTaskSheetController::class, 'toggleTask'])->name('daily-tasks.toggle-task');
        Route::post('daily-tasks/{dailyTask}/submit-task', [DailyTaskSheetController::class, 'submitTask'])->name('daily-tasks.submit-task');
        Route::post('daily-tasks/{dailyTask}/validate-task', [DailyTaskSheetController::class, 'validateTask'])->name('daily-tasks.validate-task');
        Route::post('daily-tasks/{dailyTask}/resend/{channel}', [DailyTaskSheetController::class, 'resend'])->name('daily-tasks.resend');
        Route::resource('daily-tasks', DailyTaskSheetController::class)->parameters(['daily-tasks' => 'dailyTask']);

        // Évaluations Mensuelles (/30), Critères, Objectifs & Plans d'Amélioration
        Route::get('evaluations', [EvaluationController::class, 'index'])->name('evaluations.index');
        Route::post('evaluations/generate', [EvaluationController::class, 'generateCampaign'])->name('evaluations.generate');
        Route::get('evaluations/ranking', [EvaluationController::class, 'ranking'])->name('evaluations.ranking');
        Route::get('evaluations/history/{employee}', [EvaluationController::class, 'history'])->name('evaluations.history');
        Route::post('evaluations/teamwork/{dailyScore}', [EvaluationController::class, 'updateTeamwork'])->name('evaluations.update-teamwork');
        Route::get('evaluations/{evaluation}/print', [EvaluationController::class, 'print'])->name('evaluations.print');
        Route::post('evaluations/{evaluation}/lock', [EvaluationController::class, 'lock'])->name('evaluations.lock');
        Route::post('evaluations/{evaluation}/unlock', [EvaluationController::class, 'unlock'])->name('evaluations.unlock');
        Route::post('evaluations/{evaluation}/sign', [EvaluationController::class, 'sign'])->name('evaluations.sign');
        Route::get('evaluations/{evaluation}', [EvaluationController::class, 'show'])->name('evaluations.show');
        Route::put('evaluations/{evaluation}', [EvaluationController::class, 'update'])->name('evaluations.update');

        // Configuration des Critères d'Évaluation (Pondérations, Auto/Manuel & Par Service)
        Route::get('evaluations-criteria', [EvaluationCriterionController::class, 'index'])->name('evaluations.criteria.index');
        Route::post('evaluations-criteria', [EvaluationCriterionController::class, 'store'])->name('evaluations.criteria.store');
        Route::put('evaluations-criteria/{criterion}', [EvaluationCriterionController::class, 'update'])->name('evaluations.criteria.update');
        Route::patch('evaluations-criteria/{criterion}/toggle', [EvaluationCriterionController::class, 'toggle'])->name('evaluations.criteria.toggle');
        Route::delete('evaluations-criteria/{criterion}', [EvaluationCriterionController::class, 'destroy'])->name('evaluations.criteria.destroy');

        // Objectifs Individuels Collaborateurs (Point 9)
        Route::get('goals', [EmployeeGoalController::class, 'index'])->name('goals.index');
        Route::post('goals', [EmployeeGoalController::class, 'store'])->name('goals.store');
        Route::match(['put', 'patch'], 'goals/{goal}', [EmployeeGoalController::class, 'update'])->name('goals.update');
        Route::delete('goals/{goal}', [EmployeeGoalController::class, 'destroy'])->name('goals.destroy');

        // Plans d'Amélioration de la Performance (Point 10 - PIP)
        Route::get('pips', [PerformanceImprovementPlanController::class, 'index'])->name('pips.index');
        Route::post('pips', [PerformanceImprovementPlanController::class, 'store'])->name('pips.store');
        Route::match(['put', 'patch'], 'pips/{pip}', [PerformanceImprovementPlanController::class, 'update'])->name('pips.update');
        Route::delete('pips/{pip}', [PerformanceImprovementPlanController::class, 'destroy'])->name('pips.destroy');
    });

    // Pointage Collaborateurs & Soumission Tâches (Tous rôles — Mobile Friendly)
    Route::get('pointage', [AttendanceController::class, 'scanner'])->name('rh.attendance.scanner');
    Route::post('pointage/check-in', [AttendanceController::class, 'checkIn'])->name('rh.attendance.check-in');
    Route::post('pointage/check-out', [AttendanceController::class, 'checkOut'])->name('rh.attendance.check-out');
    Route::post('pointage/tasks/{dailyTask}/submit', [DailyTaskSheetController::class, 'submitTask'])->name('rh.daily-tasks.submit');

    // Mon Espace Collaborateur (Self-service RH : Tâches, Planning 6j, Note /30 & Badge QR)
    Route::get('mon-espace', [EmployeePortalController::class, 'index'])->name('rh.portal.index');
    Route::post('mon-espace/tasks/{dailyTask}/submit', [EmployeePortalController::class, 'submitTask'])->name('rh.portal.submit-task');
    Route::get('mon-espace/badge/{employee}', [EmployeePortalController::class, 'badge'])->name('rh.portal.badge');
    Route::post('mon-espace/leaves', [EmployeePortalController::class, 'requestLeave'])->name('rh.portal.request-leave');

    Route::prefix('commercial')->name('commercial.')->middleware('role:administrateur,responsable,responsable_commercial,agent_commercial_terrain,agent_caissier_vendeur,agent_cyber')->group(function () {
        Route::get('/', [App\Http\Controllers\Commercial\DashboardController::class, 'index'])->name('index');

        // Vente Comptoir / POS
        Route::get('pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');

        // Clients
        Route::resource('customers', CustomerController::class);

        // Fournisseurs
        Route::resource('suppliers', SupplierController::class);

        // Prospects & Pipeline Kanban
        Route::resource('prospects', ProspectController::class);
        Route::patch('prospects/{prospect}/stage', [ProspectController::class, 'updateStage'])->name('prospects.stage');
        Route::post('prospects/{prospect}/convert', [ProspectController::class, 'convertToCustomer'])->name('prospects.convert');

        // Rendez-vous commerciaux
        Route::resource('appointments', CommercialAppointmentController::class);

        // Produits & Catalogue (avec QR Code & EAN)
        Route::get('products/print-catalog', [ProductController::class, 'printCatalog'])->name('products.print-catalog');
        Route::get('products/{product}/qr-download', [ProductController::class, 'downloadQr'])->name('products.qr-download');
        Route::resource('products', ProductController::class);

        // Devis
        Route::get('quotations/{quotation}/print', [QuotationController::class, 'print'])->name('quotations.print');
        Route::patch('quotations/{quotation}/send', [QuotationController::class, 'send'])->name('quotations.send');
        Route::post('quotations/{quotation}/convert', [QuotationController::class, 'convertToOrder'])->name('quotations.convert');
        Route::resource('quotations', QuotationController::class);

        // Commandes
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('orders/{order}/invoice', [OrderController::class, 'generateInvoice'])->name('orders.invoice');
        Route::resource('orders', OrderController::class);

        // Factures & Règlements
        Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
        Route::post('invoices/{invoice}/payment', [InvoiceController::class, 'quickPayment'])->name('invoices.payment');
        Route::resource('invoices', InvoiceController::class)->except(['edit', 'update', 'destroy']);
        Route::resource('payments', PaymentController::class)->only(['index', 'store']);

        // Codes Promo & Remises
        Route::patch('promotions/{promotion}/toggle', [PromotionController::class, 'toggle'])->name('promotions.toggle');
        Route::resource('promotions', PromotionController::class)->except(['show']);
    });

    Route::prefix('finance')->name('finance.')->middleware('role:administrateur,responsable,responsable_financiere,agent_caissier_vendeur,agent_monetique,agent_cyber')->group(function () {
        Route::get('/', [FinanceDashboardController::class, 'index'])->name('index');
        Route::get('export', [FinanceDashboardController::class, 'exportReport'])->name('export');
        Route::post('transfers', [FinanceDashboardController::class, 'storeTransfer'])->name('transfers.store');
        Route::post('sessions/open', [FinanceDashboardController::class, 'openSession'])->name('sessions.open');
        Route::post('sessions/{session}/close', [FinanceDashboardController::class, 'closeSession'])->name('sessions.close');
        Route::post('payments', [FinanceDashboardController::class, 'storePayment'])->name('payments.store');
        Route::post('supplier-invoices', [FinanceDashboardController::class, 'storeSupplierInvoice'])->name('supplier-invoices.store');
        Route::post('reconciliations', [FinanceDashboardController::class, 'storeReconciliation'])->name('reconciliations.store');
        Route::post('closings', [FinanceDashboardController::class, 'storeClosing'])->name('closings.store');
        Route::post('ai-query', [FinanceDashboardController::class, 'aiQuery'])->name('ai-query');

        Route::post('cash-registers/batch', [CashRegisterController::class, 'storeBatch'])->name('cash-registers.batch');
        Route::patch('cash-registers/{cashRegister}/toggle', [CashRegisterController::class, 'toggleStatus'])->name('cash-registers.toggle');
        Route::resource('cash-registers', CashRegisterController::class)->except(['edit']);
        Route::resource('expenses', ExpenseController::class);
        Route::patch('expenses/{expense}/approve', [ExpenseController::class, 'approve'])->name('expenses.approve');
        Route::resource('revenues', RevenueController::class);
        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::post('transactions', [TransactionController::class, 'store'])->name('transactions.store');
        Route::resource('budgets', BudgetController::class);

        // Immobilisations, Actifs & Équipements
        Route::get('assets/export', [FixedAssetController::class, 'export'])->name('assets.export');
        Route::post('assets/{asset}/dispose', [FixedAssetController::class, 'dispose'])->name('assets.dispose');
        Route::post('assets/{asset}/usages', [AssetUsageController::class, 'store'])->name('assets.usages.store');
        Route::post('assets/{asset}/maintenances', [AssetMaintenanceController::class, 'store'])->name('assets.maintenances.store');
        Route::patch('assets/maintenances/{maintenance}/complete', [AssetMaintenanceController::class, 'complete'])->name('assets.maintenances.complete');
        Route::resource('assets', FixedAssetController::class);

        // Suivi des Locations de Matériel
        Route::patch('assets-rentals/{rental}/status', [AssetRentalController::class, 'updateStatus'])->name('assets.rentals.status');
        Route::resource('assets-rentals', AssetRentalController::class)->names('assets.rentals')->only(['index', 'store']);
    });

    Route::prefix('communication')->name('communication.')->middleware('role:administrateur,responsable,responsable_communication')->group(function () {
        Route::get('/', function () {
            return view('communication.index');
        })->name('index');
    });

    // PRINT
    Route::prefix('print')->name('print.')->group(function () {
        Route::get('/', [PrintDashboardController::class, 'index'])->name('index');
        Route::resource('jobs', PrintJobController::class);
    });

    // SPORT
    Route::prefix('sport')->name('sport.')->group(function () {
        Route::get('/', [SportDashboardController::class, 'index'])->name('index');
        Route::resource('articles', SportArticleController::class);
    });

    // TECH
    Route::prefix('tech')->name('tech.')->group(function () {
        Route::get('/', [TechDashboardController::class, 'index'])->name('index');
        Route::resource('projects', TechProjectController::class);
        Route::resource('campaigns', AiCampaignController::class);
    });

    // MEDIA & EVENTS
    Route::prefix('media')->name('media.')->group(function () {
        Route::get('/', [MediaDashboardController::class, 'index'])->name('index');
        Route::resource('sessions', PhotoSessionController::class);
        Route::resource('equipment', EquipmentController::class);
        Route::resource('rentals', EquipmentRentalController::class);
        Route::patch('rentals/{rental}/return', [EquipmentRentalController::class, 'returnEquipment'])->name('rentals.return');
        Route::resource('events', EventController::class);
    });

    // ASSURANCE
    Route::prefix('assurance')->name('assurance.')->group(function () {
        Route::get('/', [AssuranceDashboardController::class, 'index'])->name('index');
        Route::resource('products', InsuranceProductController::class);
        Route::resource('contracts', InsuranceContractController::class);
        Route::get('commissions', [InsuranceCommissionController::class, 'index'])->name('commissions.index');
        Route::patch('commissions/{commission}/pay', [InsuranceCommissionController::class, 'markPaid'])->name('commissions.pay');
        Route::resource('appointments', InsuranceAppointmentController::class);
    });

    // Phase 4 - Gestion des Stocks (Enterprise WMS Multi-Domaines & Multi-Entrepôts)
    Route::prefix('stock')->name('stock.')->middleware('role:administrateur,responsable,responsable_commercial,responsable_financiere,agent_technique,agent_commercial_terrain,agent_caissier_vendeur')->group(function () {
        Route::get('/', [StockDashboardController::class, 'index'])->name('index');
        Route::get('export', [StockDashboardController::class, 'exportCsv'])->name('export');
        Route::get('scanner', [StockDashboardController::class, 'scanner'])->name('scanner');
        Route::post('scanner/action', [StockDashboardController::class, 'scannerAction'])->name('scanner.action');
        Route::post('adjust', [StockDashboardController::class, 'adjustStock'])->name('adjust');
        Route::post('transfers', [StockDashboardController::class, 'storeTransfer'])->name('transfers.store');
        Route::patch('transfers/{transfer}/status', [StockDashboardController::class, 'updateTransferStatus'])->name('transfers.status');
        Route::post('inventories', [StockDashboardController::class, 'storeInventory'])->name('inventories.store');
        Route::post('purchase-requests', [StockDashboardController::class, 'storePurchaseRequest'])->name('purchase-requests.store');
        Route::patch('purchase-requests/{purchaseRequest}/status', [StockDashboardController::class, 'updatePurchaseRequestStatus'])->name('purchase-requests.status');
        Route::post('receptions', [StockDashboardController::class, 'storeReception'])->name('receptions.store');
        Route::post('batches', [StockDashboardController::class, 'storeBatch'])->name('batches.store');
        Route::post('maintenances', [StockDashboardController::class, 'storeMaintenance'])->name('maintenances.store');
        Route::patch('maintenances/{maintenance}/complete', [StockDashboardController::class, 'completeMaintenance'])->name('maintenances.complete');

        Route::post('revenue-booster/optimize-price', [StockDashboardController::class, 'optimizePrice'])->name('revenue-booster.optimize-price');
        Route::post('revenue-booster/create-bundle', [StockDashboardController::class, 'createBundle'])->name('revenue-booster.create-bundle');
        Route::post('revenue-booster/secure-stock', [StockDashboardController::class, 'secureStock'])->name('revenue-booster.secure-stock');

        Route::resource('warehouses', WarehouseController::class);
        Route::get('products/bulk-create', [StockProductController::class, 'bulkCreate'])->name('products.bulk-create');
        Route::post('products/bulk-store', [StockProductController::class, 'bulkStore'])->name('products.bulk-store');
        Route::post('products/bulk-destroy', [StockProductController::class, 'bulkDestroy'])->name('products.bulk-destroy');
        Route::get('products/print-catalog', [StockProductController::class, 'printCatalog'])->name('products.print-catalog');
        Route::post('products/{product}/quick-image', [StockProductController::class, 'updateImage'])->name('products.quick-image');
        Route::get('products/{product}/qr-download', [StockProductController::class, 'downloadQr'])->name('products.qr-download');
        Route::resource('products', StockProductController::class)->except(['show']);
        Route::get('movements', [StockMovementController::class, 'index'])->name('movements.index');
        Route::get('movements/create', [StockMovementController::class, 'create'])->name('movements.create');
        Route::post('movements', [StockMovementController::class, 'store'])->name('movements.store');
        Route::get('alerts', [StockAlertController::class, 'index'])->name('alerts.index');
        Route::patch('alerts/{alert}/resolve', [StockAlertController::class, 'resolve'])->name('alerts.resolve');
    });

    // Direction / Command Center (Exclusif Administrateur)
    Route::get('/command-center', [CommandCenterController::class, 'index'])
        ->name('command-center.index')
        ->middleware('role:administrateur');

    // IVOSPHERE AI — Assistant de Gestion & Studio Créatif
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('/', [AiAssistantController::class, 'index'])->name('index');
        Route::post('/query', [AiAssistantController::class, 'query'])->name('query');
        Route::post('/generate', [AiAssistantController::class, 'generate'])->name('generate');
    });

    // Business Intelligence & Prévisions (Analytics)
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // Centre de Notifications Unifié
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::patch('/{notification}/resolve', [NotificationController::class, 'resolve'])->name('resolve');
        Route::patch('/{notification}/dismiss', [NotificationController::class, 'dismiss'])->name('dismiss');
        Route::post('/run-checks', [NotificationController::class, 'runChecks'])->name('run-checks');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
    });

    // Alertes Intelligentes & Automatisations
    Route::prefix('alerts')->name('alerts.')->group(function () {
        Route::get('/', [AlertController::class, 'index'])->name('index');
        Route::patch('/{alert}/resolve', [AlertController::class, 'resolve'])->name('resolve');
        Route::patch('/{alert}/dismiss', [AlertController::class, 'dismiss'])->name('dismiss');
        Route::post('/run-checks', [AlertController::class, 'runChecks'])->name('run-checks');
    });

    Route::prefix('automations')->name('automations.')->group(function () {
        Route::get('/', [AutomationController::class, 'index'])->name('index');
        Route::post('/rules', [AutomationController::class, 'storeRule'])->name('rules.store');
        Route::patch('/rules/{rule}/toggle', [AutomationController::class, 'toggleRule'])->name('rules.toggle');
        Route::post('/workflows', [AutomationController::class, 'storeWorkflow'])->name('workflows.store');
        Route::patch('/workflows/{workflow}/toggle', [AutomationController::class, 'toggleWorkflow'])->name('workflows.toggle');
    });

    // Gestion Centrale des Contrats
    Route::resource('contracts', ContractController::class);

    // GED & Coffre-Fort Numérique
    Route::prefix('ged')->name('ged.')->group(function () {
        Route::get('/', [GedController::class, 'index'])->name('index');
        Route::get('/vault', [GedController::class, 'vault'])->name('vault');
        Route::post('/folders', [GedController::class, 'storeFolder'])->name('folders.store');
        Route::post('/documents', [GedController::class, 'storeDocument'])->name('documents.store');
        Route::delete('/documents/{document}', [GedController::class, 'destroy'])->name('documents.destroy');
    });

    // Espace Client
    Route::get('/client-portal', [ClientPortalController::class, 'index'])->name('client-portal.index');

    // Support & Ticketing
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/', [SupportTicketController::class, 'index'])->name('index');
        Route::get('/create', [SupportTicketController::class, 'create'])->name('create');
        Route::post('/', [SupportTicketController::class, 'store'])->name('store');
        Route::get('/{ticket}', [SupportTicketController::class, 'show'])->name('show');
        Route::post('/{ticket}/reply', [SupportTicketController::class, 'reply'])->name('reply');
        Route::patch('/{ticket}/status', [SupportTicketController::class, 'updateStatus'])->name('status');
    });

    // Calendrier Collaboratif Unifié
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

    // Messagerie Interne d'Équipe
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [InternalMessageController::class, 'index'])->name('index');
        Route::post('/', [InternalMessageController::class, 'storeMessage'])->name('store');
        Route::post('/channels', [InternalMessageController::class, 'storeChannel'])->name('channels.store');
    });

    // Simulateurs de Devis Métiers (PRINT, Location, Événementiel)
    Route::get('/simulators', [SimulatorController::class, 'index'])->name('simulators.index');

    // Suivi Logistique & Livraisons
    Route::prefix('deliveries')->name('deliveries.')->group(function () {
        Route::get('/', [DeliveryController::class, 'index'])->name('index');
        Route::post('/', [DeliveryController::class, 'store'])->name('store');
        Route::patch('/{delivery}/status', [DeliveryController::class, 'updateStatus'])->name('status');
    });

    // API Recherche Universelle Spotlight (CTRL+K)
    Route::get('/api/search', [GlobalSearchController::class, 'search'])->name('api.search');
});
