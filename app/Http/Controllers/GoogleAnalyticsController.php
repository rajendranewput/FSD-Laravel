<?php
namespace App\Http\Controllers;

use App\Services\GoogleAnalyticsService;
use Illuminate\Http\Request;

/**
 * Google Analytics Controller
 * 
 * @package App\Http\Controllers
 * @version 1.0
 */
class GoogleAnalyticsController extends Controller
{
    /**
     * Google Analytics service instance
     * 
     * @var GoogleAnalyticsService
     */
    protected $analytics;

    /**
     * Constructor - Initialize Google Analytics service
     * 
     * Sets up the Google Analytics service dependency for data retrieval
     * 
     * @param GoogleAnalyticsService $analytics Google Analytics service instance
     */
    public function __construct(GoogleAnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    /**
     * Download CSV Report
     * 
     * @param Request $request The incoming HTTP request containing parameters
     * @return mixed CSV report data or download response
     * 
     * @api {get} /download-csv-report Download CSV Report
     * @apiName DownloadCsvReport
     * @apiGroup GoogleAnalytics
     * @apiParam {String} start_date Start date for analytics data (optional)
     * @apiParam {String} end_date End date for analytics data (optional)
     * @apiSuccess {File} report CSV file download
     */
    public function downloadCsvReport(Request $request)
    {
        // $startDate = $request->query('start_date', 'default_start_date');
        // $endDate = $request->query('end_date', 'default_end_date');
        $propertyId = '460222082';
        $startDate = '15daysAgo';
        $endDate = 'today';

        $report = $this->analytics->getReport($propertyId, $startDate, $endDate);
        return $report;
    }
}

?>
