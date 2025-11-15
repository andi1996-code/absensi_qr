<?php

namespace App\Http\Controllers;

use App\Models\Salaries;
use App\Models\SchoolProfile;

class SalaryController extends Controller
{
    /**
     * Show salary slip for printing
     */
    public function slip(Salaries $salary)
    {
        $schoolProfile = SchoolProfile::first();

        return view('salaries.slip', [
            'salary' => $salary,
            'schoolProfile' => $schoolProfile,
        ]);
    }
}
