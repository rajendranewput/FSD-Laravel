<?php

namespace App\Export\FlavorFirst;

use App\Traits\DateHandlerTrait;
use App\Traits\PurchasingTrait;
use Illuminate\Support\Facades\Redis;
use App\Models\Cor;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Corsheet implements FromCollection, WithHeadings, WithTitle, WithStyles
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

    public function collection()
    {
        $year = $this->year;
        $campusFlag = $this->campusFlag;
        $date = $this->date;
        $costCenter = $this->costCenter;
        $corData = $this->corData($date, $costCenter, $campusFlag, $year);
        return collect($corData);
    }

    public function headings(): array
    {
        return [
            'Costcenter', 'Category', 'Description', 'Manufacturer', 'Brand', 'Min', 'Spend'
        ];
    }
   
    public function styles(Worksheet $sheet)
    {
        return [
           
            1 => ['fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => '000000'], // Red background for cell A2
            ],
            'font' => [
                'color' => ['rgb' => 'ffffff'],
            ]]
           
        ];
    }
    public function title(): string
    {
        return 'Circle of Responsiblity';
    }
    public function corData($date, $costCenter, $campusFlag, $year){
        $data = Cor::getCorDetailsFlavorFirst($date, $costCenter, $campusFlag, $year);
        return $data;
    }
}