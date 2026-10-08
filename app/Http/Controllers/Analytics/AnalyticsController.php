<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Services\ForecastingService;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(
        protected ForecastingService $forecastingService
    ) {}

    public function index(): View
    {
        $data = $this->forecastingService->getAnalyticsData();

        return view('analytics.index', $data);
    }
}
