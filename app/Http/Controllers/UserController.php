<?php

namespace App\Http\Controllers;

use App\Models\AdminActivityLog;
use App\Models\CropProduction;
use App\Models\User;
use App\Support\BenguetLocations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search filter
        if ($request->filled('search')) {
            $search = strtolower(trim((string) $request->search));
            $searchTerm = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';

            $supportsLguColumns = $this->supportsLguValidatorColumns();

            $query->where(function($q) use ($searchTerm, $supportsLguColumns) {
                $q->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(email) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(role) LIKE ?', [$searchTerm]);

                if ($supportsLguColumns) {
                    $q->orWhereRaw('LOWER(COALESCE(lgu_municipality, \'\')) LIKE ?', [$searchTerm])
                        ->orWhereRaw('LOWER(COALESCE(lgu_barangay, \'\')) LIKE ?', [$searchTerm]);
                }
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if (in_array($request->get('status'), ['active', 'inactive'], true) && Schema::hasColumn('users', 'is_active')) {
            $query->where('is_active', $request->get('status') === 'active');
        }

        // Sort
        $allowedSorts = ['created_at', 'name', 'email', 'role'];
        $sortBy = in_array($request->get('sort_by'), $allowedSorts, true)
            ? $request->get('sort_by')
            : 'created_at';
        $sortOrder = $request->get('sort_order') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate(15)->withQueryString();

        // Statistics
        $totalUsers = User::count();
        $adminCount = User::where('role', 'admin')->count();
        $farmerCount = User::where('role', 'farmer')->count();
        $lguValidatorCount = User::where('role', User::ROLE_LGU_VALIDATOR)->count();
        $recentUsers = User::where('created_at', '>=', now()->subDays(30))->count();
        $activeUsers = Schema::hasColumn('users', 'is_active')
            ? User::where('is_active', true)->count()
            : $totalUsers;
        $inactiveUsers = Schema::hasColumn('users', 'is_active')
            ? User::where('is_active', false)->count()
            : 0;
        $stats = [
            'total' => $totalUsers,
            'admins' => $adminCount,
            'farmers' => $farmerCount,
            'lgu_validators' => $lguValidatorCount,
            'active' => $activeUsers,
            'inactive' => $inactiveUsers,
            'recent' => $recentUsers,
        ];
        $filters = $request->only(['search', 'role', 'status', 'sort_by', 'sort_order']);
        $municipalities = Schema::hasTable((new CropProduction)->getTable())
            ? CropProduction::query()
                ->distinct()
                ->pluck('municipality')
                ->map(fn ($municipality) => BenguetLocations::normalize((string) $municipality))
                ->filter()
            : collect();
        $municipalities = $municipalities
            ->merge(BenguetLocations::MUNICIPALITIES)
            ->unique()
            ->sort()
            ->values();

        return view('admin.users.index', compact('users', 'totalUsers', 'adminCount', 'farmerCount', 'lguValidatorCount', 'recentUsers', 'municipalities', 'stats', 'filters'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,farmer,lgu_validator'],
            'lgu_municipality' => ['nullable', 'required_if:role,lgu_validator', 'string', 'max:255', $this->validLguMunicipalityRule($request)],
            'lgu_barangay' => ['nullable', 'string', 'max:255', $this->validLguBarangayRule($request)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($validated['role'] === User::ROLE_LGU_VALIDATOR && ! $this->supportsLguValidatorColumns()) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'LGU validator setup is still being prepared. Please run the latest database migrations, then try again.');
        }

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'email_verified_at' => now(),
        ];

        if ($this->supportsLguValidatorColumns()) {
            $userData['lgu_municipality'] = $validated['role'] === User::ROLE_LGU_VALIDATOR ? BenguetLocations::normalize($validated['lgu_municipality']) : null;
            $userData['lgu_barangay'] = $validated['role'] === User::ROLE_LGU_VALIDATOR && ! empty($validated['lgu_barangay']) ? BenguetLocations::normalize($validated['lgu_barangay']) : null;
            $userData['is_active'] = $validated['role'] === User::ROLE_LGU_VALIDATOR ? $request->boolean('is_active', true) : true;
        }

        $user = User::create($userData);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully!');
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:admin,farmer,lgu_validator'],
            'lgu_municipality' => ['nullable', 'required_if:role,lgu_validator', 'string', 'max:255', $this->validLguMunicipalityRule($request)],
            'lgu_barangay' => ['nullable', 'string', 'max:255', $this->validLguBarangayRule($request)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($validated['role'] === User::ROLE_LGU_VALIDATOR && ! $this->supportsLguValidatorColumns()) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'LGU validator setup is still being prepared. Please run the latest database migrations, then try again.');
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        if ($this->supportsLguValidatorColumns()) {
            $user->lgu_municipality = $validated['role'] === User::ROLE_LGU_VALIDATOR ? BenguetLocations::normalize($validated['lgu_municipality']) : null;
            $user->lgu_barangay = $validated['role'] === User::ROLE_LGU_VALIDATOR && ! empty($validated['lgu_barangay']) ? BenguetLocations::normalize($validated['lgu_barangay']) : null;
            $user->is_active = $validated['role'] === User::ROLE_LGU_VALIDATOR ? $request->boolean('is_active') : true;
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

    /**
     * Reset the password for the specified user.
     */
    public function resetPassword(Request $request, User $user)
    {
        if ($user->is(auth()->user())) {
            return redirect()->route('admin.users.index')->with('error', 'Use the profile page to change your own password.');
        }

        $validated = $request->validateWithBag('resetPassword', [
            'new_password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        DB::transaction(function () use ($validated, $user) {
            $user->forceFill([
                'password' => $validated['new_password'],
                'must_change_password' => true,
                'remember_token' => Str::random(60),
            ])->save();

            if (config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))
                    ->where('user_id', $user->id)
                    ->delete();
            }

            AdminActivityLog::create([
                'actor_id' => auth()->id(),
                'subject_user_id' => $user->id,
                'action' => 'password_reset',
                'metadata' => [
                    'target_name' => $user->name,
                    'target_email' => $user->email,
                    'target_role' => $user->role,
                    'must_change_password' => true,
                ],
            ]);
        });

        return redirect()->route('admin.users.index')->with('success', 'Password reset successfully. Share the temporary password with the user and ask them to change it after signing in.');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete your own account!');
        }

        // Prevent deleting last admin
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('admin.users.index')->with('error', 'Cannot delete the last admin account!');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully!');
    }

    /**
     * Toggle user status (for future implementation)
     */
    public function toggleStatus(User $user)
    {
        // This can be implemented later if you add a status field
        return redirect()->route('admin.users.index')->with('info', 'Status toggle feature coming soon!');
    }

    private function supportsLguValidatorColumns(): bool
    {
        return Schema::hasColumn('users', 'lgu_municipality')
            && Schema::hasColumn('users', 'lgu_barangay')
            && Schema::hasColumn('users', 'is_active');
    }

    private function validLguMunicipalityRule(Request $request): \Closure
    {
        return function ($attribute, $value, $fail) use ($request) {
            if ($request->input('role') !== User::ROLE_LGU_VALIDATOR || blank($value)) {
                return;
            }

            if (! in_array(BenguetLocations::normalize($value), BenguetLocations::MUNICIPALITIES, true)) {
                $fail('The selected municipality is not supported.');
            }
        };
    }

    private function validLguBarangayRule(Request $request): \Closure
    {
        return function ($attribute, $value, $fail) use ($request) {
            if ($request->input('role') !== User::ROLE_LGU_VALIDATOR || blank($value)) {
                return;
            }

            if (! BenguetLocations::isBarangayInMunicipality($value, $request->input('lgu_municipality'))) {
                $fail('The selected barangay is not part of the assigned municipality.');
            }
        };
    }
}
