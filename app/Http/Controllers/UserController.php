<?php

namespace App\Http\Controllers;

use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate(15);

        // Statistics
        $totalUsers = User::count();
        $adminCount = User::where('role', 'admin')->count();
        $farmerCount = User::where('role', 'farmer')->count();
        $recentUsers = User::where('created_at', '>=', now()->subDays(30))->count();

        return view('admin.users.index', compact('users', 'totalUsers', 'adminCount', 'farmerCount', 'recentUsers'));
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
            'role' => ['required', 'in:admin,farmer'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'email_verified_at' => now(),
        ]);

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
            'role' => ['required', 'in:admin,farmer'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

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
}
