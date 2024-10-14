<?php
namespace App\Http\Controllers;

use App\Services\GoogleAnalyticsService;
use Illuminate\Http\Request;

class GoogleAnalyticsController extends Controller
{
    protected $analytics;

    public function __construct(GoogleAnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    public function downloadCsvReport(Request $request)
    {
        $startDate = $request->query('start_date', 'default_start_date');
        $endDate = $request->query('end_date', 'default_end_date');
        $propertyId = '460222082';
        $startDate = $startDate;
        $endDate = $endDate;

        $report = $this->analytics->getReport($propertyId, $startDate, $endDate);
        return $report;
    }
}

?>
