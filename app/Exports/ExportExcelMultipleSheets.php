<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExportExcelMultipleSheets implements WithMultipleSheets
{
    use Exportable;

    public function __construct($data, $header, $title = null)
    {
        $this->title = $title;
        $this->data = $data;
        $this->header = $header;
    }

    public function sheets(): array
    {
        $sheets = [];
        foreach ($this->data as $key => $sheet_data) {
            $sheets[] = new ExportExcel(collect([$sheet_data]), $this->header  , $this->title[$key]);
        }
        return $sheets;
    }


    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->header;
    }
}
