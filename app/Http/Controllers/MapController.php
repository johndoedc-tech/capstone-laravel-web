<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class MapController extends Controller
{
    /**
     * Display the interactive map page.
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Get user preferences for map personalization
        $preferredMunicipality = $user->preferred_municipality ?? null;
        $favoriteCrops = $user->favorite_crops ?? [];
        
        // Determine view based on user role
        $view = $user->isAdmin() 
            ? 'admin.map.index' 
            : 'farmers.map.index';
        
        return view($view, compact('preferredMunicipality', 'favoriteCrops'));
    }
}
