<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\VerificationCode;
use App\Services\Verification\VerificationCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VerificationController extends Controller
{
    public function show(): View
    {
        return view('auth.verify-email');
    }

    public function send(Request $request, string $channel): RedirectResponse
    {
        try {
            app(VerificationCodeService::class)->send($request->user(), $channel);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('status', $channel === VerificationCode::CHANNEL_EMAIL
            ? 'A new email code has been sent.'
            : 'A new SMS code has been sent.');
    }

    public function verify(Request $request, string $channel): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'size:6']]);

        try {
            app(VerificationCodeService::class)->verify($request->user(), $channel, $data['code']);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()->route($this->dashboardRoute($request))->with(
            'status',
            $channel === VerificationCode::CHANNEL_EMAIL
                ? 'Email address verified.'
                : 'Phone number verified.',
        );
    }

    private function dashboardRoute(Request $request): string
    {
        $user = $request->user();

        return $user->isStaff() && ! $user->isPreviewingStorefront()
            ? 'admin.account.dashboard'
            : 'account.dashboard';
    }
}
