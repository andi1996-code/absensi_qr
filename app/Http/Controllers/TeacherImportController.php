<?php

namespace App\Http\Controllers;

use App\Exports\TeachersTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class TeacherImportController extends Controller
{
    /**
     * Download template for teacher import
     */
    public function downloadTemplate()
    {
        return Excel::download(new TeachersTemplateExport(), 'template-guru.xlsx');
    }
}
