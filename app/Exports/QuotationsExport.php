<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\FromView;

class QuotationsExport implements FromView,ShouldAutoSize,WithStyles
{
    private $view;
    private $data;
    private $rowEnd = 1;
    private $rowInitial = 1;
    function __construct($data,$view){
        $this->data = $data;
        $this->view = $view;
    }
    public function view(): View
    {
        return view($this->view, [
            'quotations' => $this->data,
        ]); 
    }
    public function styles(Worksheet $sheet)
    {
        $rango = "A" . $this->rowInitial . ":M" . $this->rowInitial;
        // $title = $sheet->getStyle('B2');
        // $title->getFont()->setBold(true);
        // $title->getFont()->setUnderline(true);
        // $title->getFont()->setSize(22);
        // $title->getAlignment()->setHorizontal('center');
        $headerTable = $sheet->getStyle($rango);
        $headerTable->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '41B612', // Color plomo (puedes cambiar esto)
                ],
            ],
        ]);
        $headerTable->getFont()->setBold(true);
        $headerTable->getAlignment()->setHorizontal('center');
        // $sheet->getStyle($rango)->getFont()->setSize(14);
        // $sheet->getStyle($rango)->getBorders()->getAllBorders()->setBorderStyle('thin');
        // $sheet->getStyle($rango)->getAlignment()->setHorizontal('center');
        // $sheet->getStyle($rango)->getAlignment()->setVertical('center');
    }
}
