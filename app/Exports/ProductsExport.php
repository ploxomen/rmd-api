<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\FromView;

class ProductsExport implements FromView,ShouldAutoSize,WithStyles
{
    private $view;
    private $data;
    private $rowEnd = 4;
    private $rowInitial = 4;
    function __construct($data,$view){
        $this->data = $data;
        $this->view = $view;
    }
    public function view(): View
    {
        return view($this->view, [
            'products' => $this->data,
        ]); 
    }
    public function styles(Worksheet $sheet)
    {
        $this->rowEnd += count($this->data);
        $rango = "B" . $this->rowInitial . ":H" . $this->rowEnd;
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
