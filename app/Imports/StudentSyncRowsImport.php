<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class StudentSyncRowsImport implements ToArray
{
    public function array(array $array): array
    {
        return $array;
    }
}

