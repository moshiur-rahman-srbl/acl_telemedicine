<?php

namespace App\Imports;

use App\Models\Cashout;
use App\User;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ExcelFileImport implements ToModel, WithHeadingRow
{
    private $rows = 0;
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        ++$this->rows;
        return null;
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }
}
