<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    /**
     * Valid municipality options for Benguet province.
     *
     * @var array<int, string>
     */
    public const MUNICIPALITIES = [
        'Atok',
        'Bakun',
        'Bokod',
        'Buguias',
        'Itogon',
        'Kabayan',
        'Kapangan',
        'Kibungan',
        'La Trinidad',
        'Mankayan',
        'Sablan',
        'Tuba',
        'Tublay',
    ];

    /**
     * Valid cooperative options.
     *
     * @var array<int, string>
     */
    public const COOPERATIVES = [
        'Benguet Highlands Farmers Cooperative',
        'La Trinidad Vegetable Growers Association',
        'Northern Benguet Agri Cooperative',
        'Kabayan Organic Farmers Cooperative',
        'Tuba Agro-Enterprise Cooperative',
    ];

    /**
     * Display the onboarding profile form.
     */
    public function show(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasCompletedOnboarding()) {
            return redirect()->route('dashboard');
        }

        return view('auth.onboarding', [
            'municipalities' => self::MUNICIPALITIES,
            'cooperatives' => self::COOPERATIVES,
        ]);
    }

    /**
     * Validate and save the onboarding profile fields.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'municipality' => ['required', 'string', Rule::in(self::MUNICIPALITIES)],
            'cooperative' => ['required', 'string', Rule::in(self::COOPERATIVES)],
        ]);

        $request->user()->forceFill([
            'preferred_municipality' => strtoupper($validated['municipality']),
            'cooperative' => $validated['cooperative'],
        ])->save();

        return redirect()->route('dashboard')->with('status', 'profile-completed');
    }
}
