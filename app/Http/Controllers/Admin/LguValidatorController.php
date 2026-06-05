<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CropProduction;
use App\Models\User;
use App\Support\BenguetLocations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class LguValidatorController extends Controller
{
    public function index(Request $request)
    {
        $validatorsQuery = User::query()
            ->where('role', User::ROLE_LGU_VALIDATOR)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = strtolower(trim((string) $request->search));
                $searchTerm = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';

                $query->where(function ($searchQuery) use ($searchTerm) {
                    $searchQuery->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$searchTerm])
                        ->orWhereRaw('LOWER(lgu_municipality) LIKE ?', [$searchTerm])
                        ->orWhereRaw('LOWER(lgu_barangay) LIKE ?', [$searchTerm]);
                });
            })
            ->when($request->filled('municipality'), function ($query) use ($request) {
                $query->where('lgu_municipality', BenguetLocations::normalize((string) $request->municipality));
            })
            ->when($request->filled('barangay'), function ($query) use ($request) {
                $query->where('lgu_barangay', BenguetLocations::normalize((string) $request->barangay));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->status === 'active');
            });

        $validators = $validatorsQuery
            ->orderBy('lgu_municipality')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => User::where('role', User::ROLE_LGU_VALIDATOR)->count(),
            'active' => User::where('role', User::ROLE_LGU_VALIDATOR)->where('is_active', true)->count(),
            'inactive' => User::where('role', User::ROLE_LGU_VALIDATOR)->where('is_active', false)->count(),
            'municipalities' => User::where('role', User::ROLE_LGU_VALIDATOR)->whereNotNull('lgu_municipality')->distinct()->count('lgu_municipality'),
            'barangay_scoped' => User::where('role', User::ROLE_LGU_VALIDATOR)->whereNotNull('lgu_barangay')->count(),
        ];

        return view('admin.lgu-validators.index', [
            'validators' => $validators,
            'municipalities' => $this->municipalityOptions(),
            'barangaysByMunicipality' => BenguetLocations::BARANGAYS_BY_MUNICIPALITY,
            'stats' => $stats,
            'filters' => $request->only(['search', 'municipality', 'barangay', 'status']),
        ]);
    }

    public function create()
    {
        return view('admin.lgu-validators.create', [
            'municipalities' => $this->municipalityOptions(),
            'barangaysByMunicipality' => BenguetLocations::BARANGAYS_BY_MUNICIPALITY,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules($request));

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'email_verified_at' => now(),
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_LGU_VALIDATOR,
            'lgu_municipality' => BenguetLocations::normalize($validated['lgu_municipality']),
            'lgu_barangay' => BenguetLocations::normalize($validated['lgu_barangay'] ?? null),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.lgu-validators.index')
            ->with('success', 'LGU validator account created successfully.');
    }

    public function edit(User $validator)
    {
        $this->ensureValidator($validator);

        return view('admin.lgu-validators.edit', [
            'validator' => $validator,
            'municipalities' => $this->municipalityOptions(),
            'barangaysByMunicipality' => BenguetLocations::BARANGAYS_BY_MUNICIPALITY,
        ]);
    }

    public function update(Request $request, User $validator)
    {
        $this->ensureValidator($validator);

        $validated = $request->validate($this->rules($request, $validator));

        $validator->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'lgu_municipality' => BenguetLocations::normalize($validated['lgu_municipality']),
            'lgu_barangay' => BenguetLocations::normalize($validated['lgu_barangay'] ?? null),
            'is_active' => $request->boolean('is_active'),
        ]);

        if (! empty($validated['password'])) {
            $validator->password = Hash::make($validated['password']);
        }

        $validator->save();

        return redirect()
            ->route('admin.lgu-validators.index')
            ->with('success', 'LGU validator account updated successfully.');
    }

    public function toggleActive(User $validator)
    {
        $this->ensureValidator($validator);

        $validator->update([
            'is_active' => ! (bool) $validator->is_active,
        ]);

        return back()->with('success', $validator->is_active
            ? 'LGU validator account activated.'
            : 'LGU validator account deactivated.');
    }

    private function rules(Request $request, ?User $validator = null): array
    {
        $municipalities = $this->municipalityOptions()->all();
        $emailRule = Rule::unique('users', 'email');

        if ($validator) {
            $emailRule->ignore($validator->id);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', $emailRule],
            'lgu_municipality' => ['required', 'string', Rule::in($municipalities)],
            'lgu_barangay' => ['nullable', 'string', 'max:255', function ($attribute, $value, $fail) use ($request) {
                if (filled($value) && ! BenguetLocations::isBarangayInMunicipality($value, $request->input('lgu_municipality'))) {
                    $fail('The selected barangay is not part of the assigned municipality.');
                }
            }],
            'password' => [$validator ? 'nullable' : 'required', 'confirmed', Password::min(8)],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    private function ensureValidator(User $validator): void
    {
        abort_unless($validator->role === User::ROLE_LGU_VALIDATOR, 404);
    }

    private function municipalityOptions()
    {
        $fromCropData = Schema::hasTable((new CropProduction)->getTable())
            ? CropProduction::query()
                ->distinct()
                ->pluck('municipality')
                ->map(fn ($municipality) => BenguetLocations::normalize((string) $municipality))
                ->filter()
            : collect();

        return $fromCropData
            ->merge(BenguetLocations::MUNICIPALITIES)
            ->unique()
            ->sort()
            ->values();
    }
}
