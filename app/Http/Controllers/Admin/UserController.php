<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search
        if ($request->has('q') && $request->q) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('username', 'like', "%{$q}%");
            });
        }

        // Filter by Role
        if ($request->has('role') && $request->role && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        // Filter by Status
        if ($request->has('status') && $request->status !== 'all') {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $users = $query
            ->select(['id', 'name', 'email', 'role', 'is_active', 'avatar', 'created_at'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.users.partials.table', compact('users'))->render();
        }

        $stats = [
            'total' => User::count(),
            'runners' => User::where('role', 'runner')->count(),
            'coaches' => User::where('role', 'coach')->count(),
            'active' => User::where('is_active', true)->count(),
        ];

        $programs = Program::select('id', 'title')->where('is_active', true)->get();

        return view('admin.users.index', compact('users', 'stats', 'programs'));
    }

    /**
     * Get user transactions.
     */
    public function transactions(User $user)
    {
        return response()->json([
            'transactions' => $user->wallet
                ? $user->wallet->transactions()->latest()->limit(5)->get()
                : [],
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin,coach,runner,eo',
            'program_id' => 'nullable|exists:programs,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        // Create Wallet
        Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
            'locked_balance' => 0,
        ]);

        // Enroll in Program if selected
        if (! empty($validated['program_id']) && $validated['role'] === 'runner') {
            ProgramEnrollment::create([
                'program_id' => $validated['program_id'],
                'runner_id' => $user->id,
                'start_date' => now(),
                'end_date' => now()->addWeeks(12), // Default duration, should fetch from program
                'status' => 'active',
                'payment_status' => 'paid', // Admin added, assumed paid or free
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'User created successfully.',
                'user' => $user,
            ], 201);
        }

        return back()->with('success', 'User created successfully.');
    }

    /**
     * Adjust user wallet balance.
     */
    public function adjustWallet(Request $request, User $user)
    {
        $validated = $request->validate([
            'type' => 'required|in:deposit,withdraw',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
        ]);

        $wallet = $user->wallet ?? Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
            'locked_balance' => 0,
        ]);

        $balanceBefore = $wallet->balance;

        if ($validated['type'] === 'deposit') {
            $wallet->increment('balance', $validated['amount']);
        } else {
            if ($wallet->balance < $validated['amount']) {
                return back()->with('error', 'Insufficient wallet balance.');
            }
            $wallet->decrement('balance', $validated['amount']);
        }

        $balanceAfter = $wallet->balance;

        // Create Transaction Record
        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => $validated['type'], // deposit or withdraw
            'amount' => $validated['amount'],
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'status' => 'completed',
            'description' => $validated['description'].' (Admin Adjustment)',
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Wallet balance updated successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        if (request()->wantsJson()) {
            return response()->json($user->load('wallet'));
        }

        return view('admin.users.show', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => 'required|string|in:admin,coach,runner,eo',
            'is_active' => 'boolean',
            'gender' => 'nullable|string|in:male,female',
            'date_of_birth' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',

            // PBs
            'pb_5k' => 'nullable|string', // Assuming time string or simple text
            'pb_10k' => 'nullable|string',
            'pb_hm' => 'nullable|string',
            'pb_fm' => 'nullable|string',

            // Socials
            'strava_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'facebook_url' => 'nullable|url',
            'tiktok_url' => 'nullable|url',

            // Bank
            'bank_name' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',

            // Files
            'avatar' => 'nullable|image|max:2048', // 2MB
            'banner' => 'nullable|image|max:4096', // 4MB
        ]);

        // Handle File Uploads
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists and not default
            if ($user->avatar && Storage::exists('public/'.$user->avatar)) {
                // Storage::delete('public/' . $user->avatar); // Optional: keep history or delete
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path; // Store relative path or URL depending on setup. Usually path.
        }

        if ($request->hasFile('banner')) {
            if ($user->banner && Storage::exists('public/'.$user->banner)) {
                // Storage::delete('public/' . $user->banner);
            }
            $path = $request->file('banner')->store('banners', 'public');
            $validated['banner'] = $path;
        }

        // Handle Password Update
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Handle boolean checkbox for is_active if not sent (html checkbox behavior)
        if (! $request->has('is_active')) {
            // If checkbox is unchecked, it sends nothing.
            // But usually we want to explicit set it.
            // If we use a hidden input before checkbox, we get 0.
            // Let's assume the form handles this or we default to existing if not present?
            // Actually, 'boolean' validation handles "on", "1", true.
            // If missing, we might not want to update it OR assume false.
            // For safety, let's only update if present in request or use default behavior.
            // If the form sends '0' for unchecked, it's fine.
            // If strictly API, missing means no change?
            // Let's rely on $request->all() containing it or not.
            // A common pattern is $request->merge(['is_active' => $request->has('is_active')]);
            // But wait, validation runs before.
        }

        // Fix is_active from checkbox (often "on" or missing)
        // If it's a checkbox in the form:
        $validated['is_active'] = $request->boolean('is_active');

        // Handle Bank Account JSON
        if ($request->hasAny(['bank_name', 'bank_account_name', 'bank_account_number'])) {
            $currentBank = $user->bank_account ?? [];
            $validated['bank_account'] = [
                'bank_name' => $validated['bank_name'] ?? $currentBank['bank_name'] ?? null,
                'account_name' => $validated['bank_account_name'] ?? $currentBank['account_name'] ?? null,
                'account_number' => $validated['bank_account_number'] ?? $currentBank['account_number'] ?? null,
            ];
            // Remove individual fields so they don't clutter or cause issues if model doesn't expect them
            unset($validated['bank_name'], $validated['bank_account_name'], $validated['bank_account_number']);
        }

        $user->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'User profile updated successfully.',
                'user' => $user->fresh()->load('wallet'),
            ]);
        }

        return back()->with('success', 'User profile updated successfully.');
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user)
    {
        // Prevent disabling self
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot disable your own account.');
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "User {$user->name} has been {$status}.");
    }

    /**
     * Login as the selected user.
     */
    public function impersonate(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You are already logged in as yourself.');
        }

        // Optional: Guard against impersonating other admins if needed, but usually allowed for super admin
        // if ($user->isAdmin()) { ... }

        Auth::login($user);

        return redirect()->route('home')->with('success', "You are now logged in as {$user->name}");
    }
}
