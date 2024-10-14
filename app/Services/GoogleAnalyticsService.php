<?php
namespace App\Services;

use Google\Client;
use Google\Service\AnalyticsData;
use Google\Service\AnalyticsData\Dimension;
use Google\Service\AnalyticsData\Metric;
use Google\Service\AnalyticsData\DateRange;
use Google\Service\AnalyticsData\FilterExpression;
use Google\Service\AnalyticsData\Filter;
use Google\Service\AnalyticsData\StringFilter;
use Google\Service\AnalyticsData\RunReportRequest;

class GoogleAnalyticsService
{
    protected $analytics;

    public function __construct()
    {
        $client = new Client();
        $client->setAuthConfig(storage_path('app/credentials/google-service-account.json'));
        $client->addScope('https://www.googleapis.com/auth/analytics.readonly');

        $this->analytics = new AnalyticsData($client);
    }

    public function getReport($propertyId, $startDate, $endDate)
    {
        // $dateRange = new \Google\Service\AnalyticsData\DateRange();
        // $dateRange->setStartDate($startDate);
        // $dateRange->setEndDate($endDate);

        // Define dimensions (like 'country')
        $dimension = new Dimension();
        $dimension->setName('country');  // Replace with your desired dimension

        
        $dimensionEventName = new Dimension();
        $dimensionEventName->setName('eventName');  // Dimension for event name
        
        $dimensionEventLabel = new Dimension();
        $dimensionEventLabel->setName('customEvent:event_label');  // Dimension for event label
        
        // Define metrics (like 'sessions')
        $metric = new Metric();
        $metric->setName('sessions');  // Replace with your desired metric

        // You can also define a metric like 'eventCount' to retrieve the number of events
        $metricEventCount = new Metric();
        $metricEventCount->setName('eventCount');  // Metric for the event count

        // Define the date range
        $dateRange = new DateRange();
        $dateRange->setStartDate('30daysAgo');  // Example range
        $dateRange->setEndDate('today');

        // Create the event name filter
        $eventFilter = new Filter();
        $eventFilter->setFieldName('eventName');

       // Create a StringFilter for matching the exact event name
        $stringFilter = new StringFilter();
        $stringFilter->setMatchType('EXACT');
        $stringFilter->setValue('button_click'); // The specific event name to filter

        // Set the StringFilter in the event filter
        $eventFilter->setStringFilter($stringFilter);

        // Create the filter expression
        $filterExpression = new FilterExpression();
        $filterExpression->setFilter($eventFilter);

        // Build the request with dimensions and metrics
        $request = new RunReportRequest();
        $request->setDateRanges([$dateRange]);
        $request->setMetrics([$metric]);
        $request->setMetrics([$metricEventCount]);
        $request->setDimensions([$dimension, $dimensionEventName, $dimensionEventLabel]);
        $request->setDimensionFilter($filterExpression);  // Apply the filter

        // Run the report
        $response = $this->analytics->properties->runReport('properties/460222082', $request);

        return $this->processReport($response);
    }

    private function processReport($report)
    {
        // Set up the CSV header
        $csvData = "Country,Selected Levels,Counts\n";

        // Process each row from the report
        foreach ($report->getRows() as $row) {
            $eventName = $row->getDimensionValues()[0]->getValue();  // Accessing eventName dimension
            $eventLabel = $row->getDimensionValues()[2]->getValue();  // Accessing eventLabel dimension
            $country = $row->getDimensionValues()[0]->getValue();  // Accessing 'country' dimension
            $sessions = $row->getMetricValues()[0]->getValue();    // Accessing 'sessions' metric
            $eventCount = $row->getMetricValues()[0]->getValue();

            // Add data to CSV
            $csvData .= "$eventName,$eventLabel,$eventCount\n";
        }

        return $csvData;
    }
}



?>
