<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class AnalyticsController extends Controller
{
    /**
     * Display the Internal & Analytical Management dashboard.
     */
    public function index(): View
    {
        Gate::authorize('view analytics');

        return view('analytics.index');
    }

    /**
     * Display the Expenses & Charges explorer.
     */
    public function expenses(): View
    {
        Gate::authorize('view expenses');

        return view('analytics.expenses');
    }

    /**
     * Display the Annual Forecasts explorer.
     */
    public function forecasts(): View
    {
        Gate::authorize('view annual forecasts');

        return view('analytics.forecasts');
    }

    /**
     * Display the Company Statistics explorer.
     */
    public function statistics(): View
    {
        Gate::authorize('view company statistics');

        return view('analytics.statistics');
    }

    /**
     * Display the Reports Management explorer.
     */
    public function reports(): View
    {
        Gate::authorize('view reports');

        return view('analytics.reports');
    }
}
