<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CommandCenterService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommandCenterController extends Controller
{
    public function __construct(
        protected CommandCenterService $commandCenterService
    ) {}

    public function index(Request $request): View
    {
        $period = (string) $request->get('period', 'month');
        $domain = $request->get('domain');

        $metrics = $this->commandCenterService->getMetrics($period, $domain);

        return view('command_center.index', $metrics);
    }
}
