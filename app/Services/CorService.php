<?php

namespace App\Services;

use App\Repositories\CorRepository;
use App\Traits\PurchasingTrait;

/**
 * COR Service
 * 
 * @package App\Services
 * @version 1.0
 */
class CorService
{
    use PurchasingTrait;

    /**
     * COR Repository instance
     * 
     * @var CorRepository
     */
    protected $repository;

    /**
     * Constructor
     * 
     * @param CorRepository $repository COR repository instance
     */
    public function __construct(CorRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 
     * @param array $date Array of processing dates
     * @param array $costCenter Array of cost center codes
     * @param string $campusFlag Campus flag identifier
     * @param int $year Fiscal year
     * @return array Processed COR data with percentages and color thresholds
     */
    public function getCorData(array $date, array $costCenter, string $campusFlag, int $year): array
    {
        $data = $this->repository->getCorData($date, $costCenter, $campusFlag, $year);
        
        // Calculate total COR values
        $totalFirstItem = 0;
        $totalSecondItem = 0;
        
        foreach ($data as $value) {
            if ($value->cor == 1) {
                $totalFirstItem += $value->spend;
            }
            if (in_array($value->cor, [1, -1, 2])) {
                $totalSecondItem += $value->spend;
            }
        }

        // Calculate total percentage
        $total = $this->calculatePercentage($totalFirstItem, $totalSecondItem);

        // Calculate individual category values
        $beef = $this->getCorValue($data, config('constants.BEEF_CODE'));
        $chicken = $this->getCorValue($data, config('constants.CHICKEN_CODE'));
        $turkey = $this->getCorValue($data, config('constants.TURKEY_CODE'));
        $pork = $this->getCorValue($data, config('constants.PORK_CODE'));
        $eggs = $this->getCorValue($data, config('constants.EGGS_CODE'));
        $dairy = $this->getCorValue($data, config('constants.DAIRY_PRODUCT_CODE'));
        $fish = $this->getCorValue($data, config('constants.FISH_AND_SEEFOOD_CODE'));

        // Calculate color thresholds
        $totalColor = $this->getColorThreshold($total, config('constants.COR_SECTION'));
        $beefColor = $this->getColorThreshold($beef, config('constants.COR_SECTION'));
        $chickenColor = $this->getColorThreshold($chicken, config('constants.COR_SECTION'));
        $turkeyColor = $this->getColorThreshold($turkey, config('constants.COR_SECTION'));
        $porkColor = $this->getColorThreshold($pork, config('constants.COR_SECTION'));
        $eggsColor = $this->getColorThreshold($eggs, config('constants.COR_SECTION'));
        $dairyColor = $this->getColorThreshold($dairy, config('constants.COR_SECTION'));
        $fishColor = $this->getColorThreshold($fish, config('constants.COR_SECTION'));

        return [
            'total_cor' => [
                'percentage' => $total,
                'color_threshold' => $totalColor
            ],
            'beef' => [
                'percentage' => $beef,
                'color_threshold' => $beefColor
            ],
            'chicken' => [
                'percentage' => $chicken,
                'color_threshold' => $chickenColor
            ],
            'turkey' => [
                'percentage' => $turkey,
                'color_threshold' => $turkeyColor
            ],
            'pork' => [
                'percentage' => $pork,
                'color_threshold' => $porkColor
            ],
            'eggs' => [
                'percentage' => $eggs,
                'color_threshold' => $eggsColor
            ],
            'dairy' => [
                'percentage' => $dairy,
                'color_threshold' => $dairyColor
            ],
            'fish' => [
                'percentage' => $fish,
                'color_threshold' => $fishColor
            ]
        ];
    }

    /**
     * 
     * @param float $firstItem First item value
     * @param float $secondItem Second item value
     * @return float|null Calculated percentage or null
     */
    private function calculatePercentage(float $firstItem, float $secondItem): ?float
    {
        if (empty($firstItem) || empty($secondItem)) {
            if (empty($firstItem) && empty($secondItem)) {
                return null;
            }
            return 0;
        }
        
        return round(($firstItem / $secondItem) * 100);
    }
} 