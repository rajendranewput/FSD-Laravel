<?php

return [
    'CAFE_FLAG' => 0,
    'CAMPUS_FLAG' => 1,
    'CAMPUS_SUMMARY_FLAG' => 2,
    'CAFE_SUMMARY_FLAG' => 3,
    'ACCOUNT_FLAG' => 4,
    'ACCOUNT_SUMMARY_FLAG' => 5,
    'DM_FLAG' => 6,
    'DM_SUMMARY_FLAG' => 7,
    'RVP_FLAG' => 8,
    'RVP_SUMMARY_FLAG' => 9,
    'COMPANY_FLAG' => 10,
    'COMPANY_SUMMARY_FLAG' => 11,

    'ITEMS_PER_PAGE' => 10,

    // HTTP Status Codes
    'HTTP_OK' => 200,
    'HTTP_PRECONDITION_FAILED' => 412,
    'HTTP_INTERNAL_SERVER_ERROR' => 500,


    // Category Codes
    'BEEF_CODE' => 'MCC-10069',
    'CHICKEN_CODE' => 'MCC-10048',
    'TURKEY_CODE' => 'MCC-10053',
    'PORK_CODE' => 'MCC-10066',
    'EGGS_CODE' => 'MCC-10025',
    'DAIRY_PRODUCT_CODE' => 'MCC-10067',
    'FISH_AND_SEEFOOD_CODE' => 'MCC-10021',

    // Colors and Indicators
    'COR_COLOR_DIVIDE_VALUE' => 90,
    'FF_COLOR_DIVIDE_VALUE' => 15,
    'FF_FULL_CIRCLE_VALUE' => 20,
    'INDICATOR_POSITIVE' => '#63BF87',
    'INDICATOR_NEGATIVE' => '#E35E56',

    // GL Codes
    'PRODUCE_GL_CODE' => '411028',
    'MEAT_GL_CODE' => '411029',
    'CHEESE_GL_CODE' => '411031',
    'FLUID_DAIRY_GL_CODE' => '411032',
    'SEAFOOD_GL_CODE' => '411136',
    'SUSHI_GL_CODE' => '411137',
    'BAKERY_GL_CODE' => '411138',
    'ARTISAN_GL_CODE' => '411139',
    'COFFEE_GL_CODE' => '411140',
    'LOCALLY_CRAFTED_GL_CODE' => '411141',

    'EXCLUDED_COSTCENTERS' => ['AGH000', 'AEV000', 'AEW000'],

    'F2F_EXP_ARRAY_ONE' => [
        '411028', '411029', '411031', '411032', '411136',
        '411137', '411138', '411139', '411140', '411141'
    ],
    'F2F_EXP_ARRAY_TWO' => [
        '411028', '411029', '411031', '411032', '411036', '411037',
        '411038', '411039', '411041', '411045', '411048', '411060',
        '411061', '411071', '411072', '411073', '411074', '411076',
        '411085', '411086', '411100', '411136', '411137', '411138',
        '411139', '411140', '411141'
    ],

    'COR_SECTION' => 'cor',
    'COOKED_LEAKAGE_SECTION' => 'cookedLeakage',
    'FARM_FORK_SECTION' => 'farmToFork',
    'IMPORTED_MEAT' => 'importedMeat',
    'PAPER_PURCHASES' => 'paperPurchases',
    'COFFEE_SPEND' => 'coffeeSpend',

    // PPS Indicators
    'PRODUCE_DATA' => 'produceData',
    'WHOLE_GRAIN' => 'wholeGrain',
    'DAIRY' => 'dairy',
    'ANIMAL_PROTEIN' => 'animalProtein',
    'PLANT_PROTEIN' => 'plantProtein',
    'SUGAR' => 'sugar',
    'PLANT_OIL' => 'plantOil',

    // PPS Values
    'PPS_COLOR_DIVIDE_VALUE' => 0,
    'IMPORTED_MEAT_VALUE' => 0,
    'PAPER_PURCHASES_VALUE' => 90,
    'COFFEE_SPEND_VALUE' => 90,

    'PRODUCE_DATA_VALUE' => 6.5,
    'WHOLE_GRAIN_VALUE' => 2.7,
    'DAIRY_VALUE' => 2.9,
    'ANIMAL_PROTEIN_VALUE' => 2.5,
    'PLANT_PROTEIN_VALUE' => 1.5,
    'SUGAR_VALUE' => 0.4,
    'PLANT_OIL_VALUE' => 0.6,

    // MFR Category Codes
    'MEAT_MFR_CAT_CODE' => [
        'MCC-10034', 'MCC-10041', 'MCC-10048', 'MCC-10053',
        'MCC-10066', 'MCC-10069'
    ],
    'PAPER_MFR_CAT_CODE' => ['MCC-10032', 'MCC-10058', 'MCC-10070'],
    'COFFEE_MFR_CAT_CODE' => ['MCC-10097'],

];
