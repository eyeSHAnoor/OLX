<?php

namespace App\Http\Controllers;

use App\Enums\StudentEnrollmentStatus;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard');
    }
}
