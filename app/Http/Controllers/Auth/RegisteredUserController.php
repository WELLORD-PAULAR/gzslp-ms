<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Models\VerificationCode;
use App\Services\Verification\VerificationCodeService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('welcome', ['authModal' => 'register']);
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = User::create($request->validated());
        event(new Registered($user));

        if (filled($user->phone)) {
            app(VerificationCodeService::class)->send($user, VerificationCode::CHANNEL_SMS);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('account.dashboard'));
    }
}