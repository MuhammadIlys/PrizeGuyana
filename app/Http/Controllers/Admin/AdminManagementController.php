<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminManagementController extends Controller
{

    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $admins = DB::table('users')
            ->where('role', 'admin')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->select(
                'id',
                'first_name',
                'last_name',
                'email',
                'phone_number',
                'country',
                'date_of_birth',
                'created_at'
            )
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $admins->getCollection()->transform(function ($admin) {
            return [
                'id' => Crypt::encryptString((string) $admin->id),
                'first_name' => $admin->first_name,
                'last_name' => $admin->last_name,
                'name' => trim($admin->first_name . ' ' . $admin->last_name),
                'email' => $admin->email,
                'phone_number' => $admin->phone_number,
                'country' => $admin->country,
                'date_of_birth' => $admin->date_of_birth,
                'created_at' => $admin->created_at,
            ];
        });

        return Inertia::render('Admin/Admins/Index', [
            'admins' => $admins,
            'stats' => [
                'total_admins' => DB::table('users')
                    ->where('role', 'admin')
                    ->count(),
            ],
            'filters' => [
                'search' => $search,
            ],
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'country' => ['required', 'string', 'max:100'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::table('users')->insert([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => trim(
                $validated['first_name'] . ' ' . $validated['last_name']
            ),
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'country' => $validated['country'],
            'date_of_birth' => $validated['date_of_birth'],
            'role' => 'admin',
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Administrator created successfully.');
    }

    public function update(Request $request, string $id)
    {
        $adminId = $this->decryptAdminId($id);

        $admin = DB::table('users')
            ->where('id', $adminId)
            ->where('role', 'admin')
            ->first();

        abort_if(!$admin, 404);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($adminId),
            ],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'country' => ['required', 'string', 'max:100'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $updates = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => trim(
                $validated['first_name'] . ' ' . $validated['last_name']
            ),
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'country' => $validated['country'],
            'date_of_birth' => $validated['date_of_birth'],
            'updated_at' => now(),
        ];

        if (!empty($validated['password'])) {
            $updates['password'] = Hash::make($validated['password']);
        }

        DB::table('users')
            ->where('id', $adminId)
            ->where('role', 'admin')
            ->update($updates);

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Administrator updated successfully.');
    }

    public function destroy(string $id)
    {
        $adminId = $this->decryptAdminId($id);
        $currentAdminId = Auth::id();

        if ((int) $adminId === (int) $currentAdminId) {
            return back()->with('error', 'You cannot delete your own administrator account.');
        }

        $admin = DB::table('users')
            ->where('id', $adminId)
            ->where('role', 'admin')
            ->first();

        abort_if(!$admin, 404);

        $adminCount = DB::table('users')
            ->where('role', 'admin')
            ->count();

        if ($adminCount <= 1) {
            return back()->with('error', 'The last administrator account cannot be deleted.');
        }

        // Protect records that reference this account.
        $hasOrders = DB::table('orders')
            ->where('user_id', $adminId)
            ->exists();

        $hasWinningRecords = DB::table('prize_winning_tickets')
            ->where('winner_user_id', $adminId)
            ->exists();

        $hasTickets = DB::table('competition_tickets')
            ->where('user_id', $adminId)
            ->exists();

        if ($hasOrders || $hasWinningRecords || $hasTickets) {
            return back()->with(
                'error',
                'This administrator has associated platform records and cannot be deleted.'
            );
        }

        DB::table('users')
            ->where('id', $adminId)
            ->where('role', 'admin')
            ->delete();

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Administrator deleted successfully.');
    }

    private function decryptAdminId(string $id): int
    {
        try {
            $decrypted = Crypt::decryptString($id);
        } catch (DecryptException $exception) {
            abort(404);
        }

        abort_unless(
            ctype_digit($decrypted) && (int) $decrypted > 0,
            404
        );

        return (int) $decrypted;
    }
}
