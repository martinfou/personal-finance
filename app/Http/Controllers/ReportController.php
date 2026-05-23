<?php

namespace App\Http\Controllers;

use App\Services\BudgetReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function __construct(
        private readonly BudgetReportService $reportService
    ) {}

    /**
     * List available monthly reports.
     */
    public function index(Request $request)
    {
        $months = $this->reportService->availableMonths($request->user()->id);

        return Inertia::render('Reports/Index', [
            'months' => $months,
        ]);
    }

    /**
     * Show budget review for a specific month.
     */
    public function monthly(Request $request)
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        // Ensure valid date
        $date = Carbon::create($year, $month, 1);
        if (!$date || !checkdate($month, 1, $year)) {
            $year = (int) now()->year;
            $month = (int) now()->month;
        }

        $review = $this->reportService->monthlyReview($request->user()->id, $year, $month);
        $months = $this->reportService->availableMonths($request->user()->id);

        return Inertia::render('Reports/Monthly', array_merge($review, [
            'months' => $months,
        ]));
    }

    /**
     * Export monthly budget report as CSV.
     */
    public function exportCsv(Request $request)
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $content = $this->reportService->exportCsv($request->user()->id, $year, $month);
        $filename = "budget-report-{$year}-{$month}.csv";

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
