<?php

namespace App\Http\Controllers;

use App\Models\ProviderTransaction;
use App\Models\WebhookEvent;
use App\Support\Security\Audit;
use Illuminate\Http\Request;

class ReconController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->route()->defaults['tab'] ?? $request->query('tab', 'unmatched');
        $provider = $request->query('provider', 'all');

        $unmatched = ProviderTransaction::with('customer')
            ->whereNull('payment_id')
            ->when($provider !== 'all', fn ($q) => $q->where('provider', $provider))
            ->latest()->paginate(15, ['*'], 'unmatched');

        $providerTxns = ProviderTransaction::with(['customer','payment'])
            ->when($provider !== 'all', fn ($q) => $q->where('provider', $provider))
            ->latest()->paginate(15, ['*'], 'provider');

        $exceptions = WebhookEvent::whereIn('processing_status', ['failed', 'rejected'])
            ->orWhere('signature_status', 'invalid')
            ->when($provider !== 'all', fn ($q) => $q->where('provider', $provider))
            ->latest()->paginate(15, ['*'], 'exceptions');

        $counts = [
            'unmatched' => ProviderTransaction::whereNull('payment_id')->count(),
            'eligible' => ProviderTransaction::where('status', 'SUCCESS')->where('settlement_status', 'eligible')->count(),
            'exceptions' => WebhookEvent::whereIn('processing_status', ['failed', 'rejected'])->orWhere('signature_status', 'invalid')->count(),
        ];

        return view('reconciliation.index', [
            'tab' => $tab,
            'provider' => $provider,
            'unmatched' => $unmatched,
            'providerTxns' => $providerTxns,
            'exceptions' => $exceptions,
            'counts' => $counts,
        ]);
    }

    /**
     * Manually link an unmatched provider transaction to a local payment.
     */
    public function match(Request $request, ProviderTransaction $transaction)
    {
        $data = $request->validate(['payment_reference' => 'required|string|max:40']);

        $payment = \App\Models\Payment::where('reference', $data['payment_reference'])->first();
        if (! $payment) {
            return back()->withErrors(['payment_reference' => 'No local payment with that reference.']);
        }

        $transaction->update(['payment_id' => $payment->id, 'settlement_status' => $transaction->status === 'SUCCESS' ? 'eligible' : 'pending']);
        Audit::log('reconciliation.matched', $transaction, ['payment' => $payment->reference]);

        return back()->with('success', 'Transaction matched to '.$payment->reference.'.');
    }

    public function resolve(WebhookEvent $event)
    {
        $event->update(['processing_status' => 'resolved', 'processed_at' => now(), 'error' => null]);
        Audit::log('reconciliation.exception.resolved', $event, ['provider' => $event->provider]);

        return back()->with('success', 'Exception marked resolved.');
    }

    public function approve(Request $request)
    {
        $count = ProviderTransaction::where('status', 'SUCCESS')->where('settlement_status', 'eligible')->count();
        Audit::log('reconciliation.approved', 'reconciliation', ['eligible_count' => $count, 'by' => $request->user()->email]);

        return back()->with('success', $count.' eligible transaction(s) reviewed. Create a settlement batch to settle them.');
    }
}
