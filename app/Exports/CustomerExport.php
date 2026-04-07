<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\FromView;

class CustomerExport implements FromView,ShouldAutoSize,WithStyles
{
    private $data;
    private $rowEnd = 3;
    private $rowInitial = 5;
    public function __construct($data,$totalData){
        $this->data = $data;
        $this->rowEnd += $totalData;
    }
    public function view(): View
    {
        return view('reports.customers-excel', [
            'customers' => $this->data,
        ]); 
    }
     public function styles(Worksheet $sheet)
    {
        $this->rowEnd += count($this->data);
        $rango = "A" . $this->rowInitial . ":L" . $this->rowEnd;
        $title = $sheet->getStyle('B2');
        $title->getFont()->setBold(true);
        $title->getFont()->setUnderline(true);
        $title->getFont()->setSize(22);
        $title->getAlignment()->setHorizontal('center');
        $sheet->getStyle($rango)->getFont()->setSize(14);
        $sheet->getStyle($rango)->getBorders()->getAllBorders()->setBorderStyle('thin');
        $sheet->getStyle($rango)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($rango)->getAlignment()->setVertical('center');
    }
}
