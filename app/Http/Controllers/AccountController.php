<?php

namespace App\Http\Controllers;

use App\Models\VerificationCode;
use App\Services\Verification\VerificationCodeService;
use App\Http\Requests\UpdateAccountPasswordRequest;
use App\Http\Requests\UpdateAccountRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        return view('account.dashboard');
    }

    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('account.edit', compact('user'));
    }

    public function update(UpdateAccountRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $emailChanged = $data['email'] !== $user->email;
        $phoneChanged = ($data['phone'] ?? null) !== $user->phone;

        $user->update($data);

        if ($emailChanged) {
            $user->forceFill(['email_verified_at' => null])->save();
            app(VerificationCodeService::class)->send($user, VerificationCode::CHANNEL_EMAIL, true);
        }

        if ($phoneChanged) {
            $user->forceFill(['phone_verified_at' => null])->save();

            if (filled($user->phone)) {
                app(VerificationCodeService::class)->send($user, VerificationCode::CHANNEL_SMS, true);
            }
        }

        return redirect()->route('account.dashboard')->with('status', 'Account details updated.');
    }

    public function updatePassword(UpdateAccountPasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        $request->session()->regenerate();

        return redirect()->route('account.dashboard')->with('status', 'Password updated.');
    }
}