<?php

namespace App\Export\FlavorFirst;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
//use App\Export\FlavorFirst\SummarySheet;

class MultiSheetExport implements WithMultipleSheets
{
    protected $year;
    protected $campusFlag;
    protected $date;
    protected $costCenter;

    public function __construct($year, $campusFlag, $date, $costCenter)
    {
        $this->year = $year;
        $this->campusFlag = $campusFlag;
        $this->date = $date;
        $this->costCenter = $costCenter;
    }
    /**
     * Create an array of sheets to be exported.
     * Each entry corresponds to a sheet.
     */
    public function sheets(): array
    { 
        $year = $this->year;
        $campusFlag = $this->campusFlag;
        $date = $this->date;
        $costCenter = $this->costCenter;
        return [
            new SummarySheet($year, $campusFlag, $date, $costCenter),
            new CorSheet($year, $campusFlag, $date, $costCenter),
            // new ProductsSheet(),
        ];
    }
}
