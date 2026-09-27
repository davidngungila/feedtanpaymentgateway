<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\Security\Audit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(Request $request): View
    {
        $pane = $request->input('pane', 'general');

        $panes = ['general', 'commissions', 'security', 'notifications', 'cashpoint'];
        if (! in_array($pane, $panes, true)) {
            $pane = 'general';
        }

        $settings = Setting::all()->pluck('value', 'key');
        $agent = cash_point();

        return view('settings.index', compact('pane', 'settings', 'agent'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'general' => ['nullable', 'array'],
            'general.business_name' => ['nullable', 'string', 'max:120'],
            'general.address' => ['nullable', 'string', 'max:255'],
            'general.contact_email' => ['nullable', 'email', 'max:120'],
            'general.contact_phone' => ['nullable', 'string', 'max:30'],
            'general.currency' => ['nullable', 'string', 'max:10'],
            'commissions' => ['nullable', 'array'],
            'security' => ['nullable', 'array'],
            'security.max_transaction_limit' => ['nullable', 'numeric', 'min:0'],
            'security.min_withdrawal_limit' => ['nullable', 'numeric', 'min:0'],
            'security.require_approval_above' => ['nullable', 'numeric', 'min:0'],
            'security.session_timeout_minutes' => ['nullable', 'integer', 'min:1'],
            'notifications' => ['nullable', 'array'],
        ]);

        foreach ($validated as $group => $values) {
            if (is_array($values) && $values !== []) {
                Setting::updateOrCreate(['key' => $group], ['value' => $values]);
            }
        }

        Audit::log('settings.updated', 'Setting', ['groups' => array_keys(array_filter($validated, 'is_array'))]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Settings saved successfully.']);
        }

        return back()->with('status', 'Settings saved successfully.');
    }
}
