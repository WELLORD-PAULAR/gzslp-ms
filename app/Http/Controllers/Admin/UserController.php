<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreManagedUserRequest;
use App\Http\Requests\UpdateManagedUserRequest;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();
            $query->where(fn ($query) => $query
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%'));
        }

        if ($request->filled('role') && in_array($request->input('role'), [User::ROLE_CUSTOMER, User::ROLE_EMPLOYEE, User::ROLE_ADMIN], true)) {
            $query->where('role', $request->input('role'));
        }

        $users = $query->latest()->paginate(25)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create', ['managedUser' => new User(['is_active' => true])]);
    }

    public function store(StoreManagedUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
        ]);
        $user->forceFill([
            'role' => $data['role'],
            'is_active' => $data['is_active'],
        ])->save();

        return redirect()->route('admin.users.index')->with('status', 'Account created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', ['managedUser' => $user]);
    }

    public function update(UpdateManagedUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $isRemovingLastAdmin = $user->isAdmin()
            && $user->isActive()
            && ($data['role'] !== User::ROLE_ADMIN || ! $data['is_active'])
            && User::where('role', User::ROLE_ADMIN)->where('is_active', true)->count() <= 1;

        if ($isRemovingLastAdmin) {
            return back()->withInput()->withErrors([
                'role' => 'The last active administrator cannot be demoted or disabled.',
            ]);
        }

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
            $this->expireCodes($user, VerificationCode::CHANNEL_EMAIL);
        }

        if ($user->isDirty('phone')) {
            $user->phone_verified_at = null;
            $this->expireCodes($user, VerificationCode::CHANNEL_SMS);
        }

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->forceFill([
            'role' => $data['role'],
            'is_active' => $data['is_active'],
        ])->save();

        return redirect()->route('admin.users.index')->with('status', 'Account updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors(['account' => 'You cannot delete the account you are currently using.']);
        }

        if ($user->isAdmin() && $user->isActive()
            && User::where('role', User::ROLE_ADMIN)->where('is_active', true)->count() <= 1) {
            return back()->withErrors(['account' => 'The last active administrator cannot be deleted.']);
        }

        if ($user->orders()->exists() || $user->inventoryMovements()->exists()) {
            return back()->withErrors(['account' => 'This account has order or inventory history. Deactivate it to preserve those records.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'Account deleted.');
    }

    private function expireCodes(User $user, string $channel): void
    {
        VerificationCode::query()
            ->where('user_id', $user->id)
            ->where('channel', $channel)
            ->whereNull('verified_at')
            ->update(['expires_at' => now()]);
    }
}