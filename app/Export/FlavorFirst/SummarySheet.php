<?php

namespace App\Export\FlavorFirst;

use Illuminate\Http\Request;
use App\Traits\DateHandlerTrait;
use App\Traits\PurchasingTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\Cor;
use App\Models\CookedLeakage;
use App\Models\Farmtofork;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class SummarySheet implements WithEvents
{
    use DateHandlerTrait, PurchasingTrait;

    protected $year;
    protected $campusFlag;
    protected $date;
    protected $type;

    public function __construct($year, $campusFlag, $date, $costCenter)
    {
        $this->year = $year;
        $this->campusFlag = $campusFlag;
        $this->date = $date;
        $this->costCenter = $costCenter;
    }
    
    public function registerEvents(): array
    {
        $year = $this->year;
        $campusFlag = $this->campusFlag;
        $date = $this->date;
        $costCenter = $this->costCenter;
        //$date = $this->handleDates($request->end_date, $request->campus_flag);
        
        $corData = $this->corData($date, $costCenter, $campusFlag, $year);
        $cfsData = $this->cfsData($date, $costCenter, $campusFlag, $year);
        $leakageData = $this->leakageData($date, $costCenter, $campusFlag, $year);
        $f2f = $this->farmtoForkData($date, $costCenter, $campusFlag, $year);
        
        return [
            AfterSheet::class => function(AfterSheet $event) use($corData, $cfsData, $f2f, $leakageData) {
                $sheet = $event->sheet;
                $sheet->setTitle('Summary');
                $sheet->setShowGridlines(false);
                $sheet->getSheetView()->setZoomScale(150);
                $sheet->getColumnDimension('A')->setWidth(40);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $style = array(
                    'alignment' => array(
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    )
                );
                $sheet->getStyle('B')->applyFromArray($style);
                $border_array = $this->get_border_style();
                $sheet->getDelegate()->getStyle('A3:B11')->applyFromArray($border_array);
                $sheet->getDelegate()->getStyle('A13:B14')->applyFromArray($border_array);
                $sheet->getDelegate()->getStyle('A16:B18')->applyFromArray($border_array);
                $sheet->getDelegate()->getStyle('A20:B21')->applyFromArray($border_array);
                // Set specific cell values
                $sheet->getDelegate()->getStyle('A1:B1')->applyFromArray($this->get_header_style());
                $sheet->getDelegate()->getStyle('A3:B3')->applyFromArray($this->get_header_style());
                $sheet->setCellValue('A1', '2024-01-01');
                $sheet->setCellValue('A3', 'Circle of Responsibility');
                $sheet->setCellValue('B3', '%');
                $sheet->setCellValue('A4', 'Total Cor');
                $sheet->setCellValue('B4', $corData['total']);
                $sheet->getDelegate()->getStyle('B4')->applyFromArray($this->corFontColor($corData['total']));
                $sheet->setCellValue('A5', 'Ground Beef');
                $sheet->setCellValue('B5', $corData['beef']);
                $sheet->getDelegate()->getStyle('B5')->applyFromArray($this->corFontColor($corData['beef']));
                $sheet->setCellValue('A6', 'Chicken');
                $sheet->setCellValue('B6', $corData['chiken']);
                $sheet->getDelegate()->getStyle('B6')->applyFromArray($this->corFontColor($corData['chiken']));
                $sheet->setCellValue('A7', 'Turkey');
                $sheet->setCellValue('B7', $corData['turkey']);
                $sheet->getDelegate()->getStyle('B7')->applyFromArray($this->corFontColor($corData['turkey']));
                $sheet->setCellValue('A8', 'Pork');
                $sheet->setCellValue('B8', $corData['pork']);
                $sheet->getDelegate()->getStyle('B8')->applyFromArray($this->corFontColor($corData['pork']));
                $sheet->setCellValue('A9', 'Eggs');
                $sheet->setCellValue('B9', $corData['eggs']);
                $sheet->getDelegate()->getStyle('B9')->applyFromArray($this->corFontColor($corData['eggs']));
                $sheet->setCellValue('A10', 'Milk & Yogurt');
                $sheet->setCellValue('B10', $corData['dairy']);
                $sheet->getDelegate()->getStyle('B10')->applyFromArray($this->corFontColor($corData['dairy']));
                $sheet->setCellValue('A11', 'Fish & Seafood');
                $sheet->setCellValue('B11', $corData['fish']);
                $sheet->getDelegate()->getStyle('B11')->applyFromArray($this->corFontColor($corData['fish']));
                $sheet->getDelegate()->getStyle('A13:B13')->applyFromArray($this->get_header_style());
                $sheet->setCellValue('A13', 'Cooked From Scratch');
                $sheet->setCellValue('B13', '$');
                $sheet->setCellValue('A14', 'Noncompliant Spend ($)');
                $sheet->setCellValue('B14', $cfsData);
                $sheet->getDelegate()->getStyle('B14')->getFont()->getColor()->setARGB('E35E56');
                $sheet->getDelegate()->getStyle('A16:B16')->applyFromArray($this->get_header_style());
                $sheet->setCellValue('A16', 'Farm to Fork');
                $sheet->setCellValue('B16', '%');
                $sheet->setCellValue('A17', 'Farm to Fork % (Current Period)');
                $sheet->setCellValue('B17', $f2f['period']);
                $sheet->getDelegate()->getStyle('B17')->applyFromArray($this->f2fFontColor($f2f['period']));
                $sheet->setCellValue('A18', 'Farm to Fork % (FYTD)');
                $sheet->setCellValue('B18', $f2f['yearly']);
                $sheet->getDelegate()->getStyle('B18')->applyFromArray($this->f2fFontColor($f2f['yearly']));
                $sheet->getDelegate()->getStyle('A20:B20')->applyFromArray($this->get_header_style());
                $sheet->setCellValue('A20', 'Leakage From Reporting Vendors');
                $sheet->setCellValue('B20', '$');
                $sheet->setCellValue('A21', 'Noncompliant Spend ($)');
                $sheet->setCellValue('B21', $leakageData);
                $sheet->getDelegate()->getStyle('B21')->getFont()->getColor()->setARGB('E35E56');
            },
        ];
    }
    public function get_header_style(){
        return [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => '000000'], // Red background for cell A2
            ],
            'font' => [
                'color' => ['rgb' => 'ffffff'],
            ]
        ];
    }
    
    public function get_border_style(){
        return array(
            'borders' => array(
                'outline' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => array('rgb' => '000000'),
                ),
            ),
        );
    }
    public function corFontColor($value){
        return [
            'font' => [
                'color' => ['argb' => $value >= 90 ? '63BF87' : 'E35E56'], // Blue font color for cell A2
            ],
        ];
    }
    public function f2fFontColor($value){
        return [
            'font' => [
                'color' => ['argb' => $value >= 15 ? '63BF87' : 'E35E56'], // Blue font color for cell A2
            ],
        ];
    }
    
    public function corData($date, $costCenter, $campusFlag, $year){
        $data = Cor::getCorData($date, $costCenter, $campusFlag, $year);
        $totalFirstItem = 0;
        $totalSecondItem = 0;
        foreach($data as $key => $value){
            if($value->cor == 1){
                $totalFirstItem += $value->spend;
            }
            if(in_array($value->cor, [1,-1,2])){
                $totalSecondItem += $value->spend;
            }
        }
        if(empty($totalFirstItem) || empty($totalSecondItem)){
            if(empty($totalFirstItem) && empty($totalSecondItem)){
                $total = null;
            } else {
                $total = 0;
            }
        } else {
            $total = round(($totalFirstItem/$totalSecondItem)*100);
        }

        $beef = $this->getCorValue($data, BEEF_CODE);
        $chiken = $this->getCorValue($data, CHICKEN_CODE);
        $turkey = $this->getCorValue($data, TURKEY_CODE);
        $pork = $this->getCorValue($data, PORK_CODE);
        $eggs = $this->getCorValue($data, EGGS_CODE);
        $dairy = $this->getCorValue($data, DAIRY_PRODUCT_CODE);
        $fish = $this->getCorValue($data, FISH_AND_SEEFOOD_CODE);
        $data = array(
            'total' => $total,
            'beef' =>$beef,
            'chiken' => $chiken,
            'turkey' => $turkey,
            'pork' => $pork,
            'eggs' => $eggs,
            'dairy' => $dairy,
            'fish' => $fish
        );
        return collect($data);
    }

    public function cfsData($date, $costCenter, $campusFlag, $year){
        $cookedFromScratch = CookedLeakage::cookedFromScratch($date, $costCenter, $campusFlag, $year);
        return $cookedFromScratch;
    }

    public function leakageData($date, $costCenter, $campusFlag, $year){
        $leakageFromVendors = CookedLeakage::leakageFromVendors($date, $costCenter, $campusFlag, $year);
        return $leakageFromVendors;
    }
    public function farmToForkData($date, $costCenter, $campusFlag, $year){
        $farmToForkPeriod = Farmtofork::farmToForkData($date, $costCenter, $campusFlag, $year, 'period');
        $farmToForkYear = Farmtofork::farmToForkData($date, $costCenter, $campusFlag, $year, 'year');

        return array(
            'period' => $farmToForkPeriod,
            'yearly' => $farmToForkYear
        );
    }
}
