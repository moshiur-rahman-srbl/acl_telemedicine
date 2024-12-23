<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExportExcel implements FromCollection,WithHeadings,WithTitle
{

    public function __construct($data,$header, $title = null)
    {

        $this->title = $title;
        $this->data = $data;
        $this->header = $header;

    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
//        dd($this->data);
        return $this->data;
    }

    public function title(): string
    {
        return $this->title ?? 'Export-' . strtotime(date("Y-m-d H:i:s"));//exception handling for " App\Exports\ExportExcel::title() must be of the type string, null returned " error . this error is caused by the fact that the title is not set in the constructor while calling the export function.
    }

    /**
     * @return array
     */
    public function headings(): array
    {
//        dd($this->header);
        return $this->header;
    }
}
