<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Monster;

class DashboardController extends Controller
{
    public function index()
    {
        $totalMonsters = Monster::count();
        $availableMonsters = Monster::where('status', 'available')->count();
        $borrowedMonsters = Monster::where('status', 'borrowed')->count();
        $maintenanceMonsters = Monster::where('status', 'maintenance')->count();
        $totalCategories = Category::count();

        $recentMonsters = Monster::with('categories')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalMonsters',
            'availableMonsters',
            'borrowedMonsters',
            'maintenanceMonsters',
            'totalCategories',
            'recentMonsters'
        ));
    }
}
