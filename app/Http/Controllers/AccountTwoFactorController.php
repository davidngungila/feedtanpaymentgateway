<?php

namespace App\Http\Controllers;

use App\Support\Security\Audit;
use App\Support\Security\TwoFactor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountTwoFactorController extends Controller
{
    /**
     * Dedicated setup page (create-style layout with QR + verification).
     * The secret lives in session only until the user proves a valid code.
     */
    public function showSetup(Request $request)
    {
        $user = $request->user();
        if ($user->two_factor_enabled) {
            return redirect()->route('account.index');
        }

        $secret = $request->session()->get('two_factor_pending_secret');
        if (! $secret) {
            $secret = TwoFactor::generateSecret();
            $request->session()->put('two_factor_pending_secret', $secret);
        }

        return view('account.two-factor-setup', [
            'user' => $user,
            'secret' => $secret,
            'uri' => TwoFactor::otpauthUri($secret, $user->email),
        ]);
    }

    /**
     * Start setup: fresh secret kept in session (never in DB) until
     * the user proves they scanned it by entering a valid code.
     */
    public function setup(Request $request)
    {
        $secret = TwoFactor::generateSecret();
        $request->session()->put('two_factor_pending_secret', $secret);

        return redirect()->route('account.two-factor.setup');
    }

    /**
     * Verify the code against the pending secret and enable 2FA.
     * Returns fresh recovery codes (shown once).
     */
    public function confirm(Request $request)
    {
        $request->validate(['code' => ['required', 'string', 'max:10']]);

        $secret = $request->session()->get('two_factor_pending_secret');
        if (! $secret) {
            return response()->json(['success' => false, 'message' => 'Setup session expired. Please start again.'], 422);
        }

        if (! TwoFactor::verifyPending($secret, $request->input('code'))) {
            Audit::log('two_factor.setup.failed', $request->user(), [], 'FAILED', 'warning');

            return response()->json(['success' => false, 'message' => 'That code did not match. Check your authenticator app time and try again.'], 422);
        }

        $codes = TwoFactor::generateRecoveryCodes();
        $request->user()->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_enabled' => true,
            'two_factor_recovery_codes' => array_map([TwoFactor::class, 'hashRecoveryCode'], $codes),
        ])->save();
        $request->session()->forget('two_factor_pending_secret');

        Audit::log('two_factor.enabled', $request->user(), []);

        return response()->json(['success' => true, 'message' => 'Two-factor authentication enabled.', 'recovery_codes' => $codes]);
    }

    /**
     * Disable 2FA after confirming the current password.
     */
    public function disable(Request $request)
    {
        $request->validate(['current_password' => ['required', 'string']]);

        $user = $request->user();
        if (! Hash::check($request->input('current_password'), $user->password)) {
            return response()->json(['success' => false, 'message' => 'Your current password is incorrect.'], 422);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_enabled' => false,
            'two_factor_recovery_codes' => null,
        ])->save();

        Audit::log('two_factor.disabled', $user, []);

        return response()->json(['success' => true, 'message' => 'Two-factor authentication disabled.']);
    }

    /**
     * Burn old codes and issue fresh ones (shown once).
     */
    public function regenerateCodes(Request $request)
    {
        $request->validate(['current_password' => ['required', 'string']]);

        $user = $request->user();
        if (! Hash::check($request->input('current_password'), $user->password)) {
            return response()->json(['success' => false, 'message' => 'Your current password is incorrect.'], 422);
        }
        if (! $user->two_factor_enabled) {
            return response()->json(['success' => false, 'message' => 'Two-factor is not enabled.'], 422);
        }

        $codes = TwoFactor::generateRecoveryCodes();
        $user->forceFill([
            'two_factor_recovery_codes' => array_map([TwoFactor::class, 'hashRecoveryCode'], $codes),
        ])->save();

        Audit::log('two_factor.recovery_codes.regenerated', $user, []);

        return response()->json(['success' => true, 'message' => 'New recovery codes generated.', 'recovery_codes' => $codes]);
    }
}
