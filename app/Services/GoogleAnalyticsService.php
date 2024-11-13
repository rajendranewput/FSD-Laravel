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
        $dateRange = new \Google\Service\AnalyticsData\DateRange();
        $dateRange->setStartDate($startDate);
        $dateRange->setEndDate($endDate);

        $dimension = new Dimension();
        $dimension->setName('country');

        $dimensionEventName = new Dimension();
        $dimensionEventName->setName('eventName');
        
        $dimensionEventLabel = new Dimension();
        $dimensionEventLabel->setName('customEvent:event_label');

        $selectedLevel = new Dimension();
        $selectedLevel->setName('customEvent:selected_level');

        $selectedValue = new Dimension();
        $selectedValue->setName('customEvent:selected_value');

        $selectedName = new Dimension();
        $selectedName->setName('customEvent:selected_name'); 
        
        $metric = new Metric();
        $metric->setName('sessions');

        $metricEventCount = new Metric();
        $metricEventCount->setName('eventCount');

        // $dateRange = new DateRange();
        // $dateRange->setStartDate('30daysAgo');  // Example range
        // $dateRange->setEndDate('today');

        // Create the event name filter
        $eventFilter = new Filter();
        $eventFilter->setFieldName('eventName');

       // Create a StringFilter for matching the exact event name
        $stringFilter = new StringFilter();
        $stringFilter->setMatchType('EXACT');
        $stringFilter->setValue('button_click');

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
        $request->setDimensions([$dimension, $dimensionEventName, $dimensionEventLabel, $selectedLevel, $selectedValue, $selectedName]);
        $request->setDimensionFilter($filterExpression);  // Apply the filter

        // Run the report
        $response = $this->analytics->properties->runReport('properties/460222082', $request);

        return $this->processReport($response);
    }

    private function processReport($report)
    {
        // Set up the CSV header
        $csvData = "Country,Selected Level, Id, Name, Click Counts\n";
        // Process each row from the report
        foreach ($report->getRows() as $row) {
           // $eventName = $row->getDimensionValues()[0]->getValue();  // Accessing eventName dimension
            //$eventLabel = $row->getDimensionValues()[2]->getValue();  // Accessing eventLabel dimension
            $level = $row->getDimensionValues()[3]->getValue();  // Accessing eventLabel dimension
            $value = $row->getDimensionValues()[4]->getValue();  // Accessing eventLabel dimension
            $name = $row->getDimensionValues()[5]->getValue();  // Accessing eventLabel dimension
            $sessions = $row->getMetricValues()[0]->getValue();    // Accessing 'sessions' metric
            $eventCount = $row->getMetricValues()[0]->getValue();

            // Add data to CSV
            $csvData .= "$level,$value,$name,$eventCount\n";
        }

        return $csvData;
    }
}



?>
