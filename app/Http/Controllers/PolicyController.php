<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PolicyController extends Controller
{
    /**
     * Display privacy policy page
     */
    public function privacy()
    {
        return Inertia::render('policies/Index', [
            'activeTab' => 'privacy',
            'lastUpdated' => 'March 11, 2026'
        ]);
    }

    /**
     * Display terms and conditions page
     */
    public function terms()
    {
        return Inertia::render('policies/Index', [
            'activeTab' => 'terms',
            'lastUpdated' => 'March 11, 2026'
        ]);
    }

    /**
     * Display refund policy page
     */
    public function refund()
    {
        return Inertia::render('policies/Index', [
            'activeTab' => 'refund',
            'lastUpdated' => 'March 11, 2026'
        ]);
    }

    /**
     * Single method to handle all policy types
     */
    public function show($type)
    {
        $validTypes = ['privacy', 'terms', 'refund'];
        
        if (!in_array($type, $validTypes)) {
            abort(404);
        }

        return Inertia::render('policies/Index', [
            'activeTab' => $type,
            'lastUpdated' => 'March 11, 2026'
        ]);
    }
}