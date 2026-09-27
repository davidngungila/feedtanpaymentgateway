<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\ProviderTransaction;
use App\Models\Refund;
use App\Models\Reversal;
use App\Payments\PaymentEngine;
use App\Payments\ProviderRegistry;
use App\Support\Security\Audit;
use Illuminate\Http\Request;

class CollectionPaymentController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->route()->defaults['status'] ?? $request->query('status');
        $provider = $request->query('provider', 'all');
        $q = trim((string) $request->query('q', ''));

        $query = ProviderTransaction::with(['customer', 'payment'])->latest();
        if ($status && $status !== 'all') {
            $query->where('status', strtoupper($status));
        }
        if ($provider !== 'all') {
            $query->where('provider', $provider);
        }
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('txn_id', 'like', "%{$q}%")->orWhere('reference', 'like', "%{$q}%");
            });
        }

        $transactions = $query->paginate(15)->withQueryString();

        $titles = ['PENDING' => 'Pending', 'SUCCESS' => 'Successful', 'FAILED' => 'Failed', 'REVERSED' => 'Reversed'];

        return view('collections.payments.index', [
            'transactions' => $transactions,
            'status' => $status ? strtoupper($status) : null,
            'title' => $status ? ($titles[strtoupper($status)] ?? 'Transactions') : 'All Transactions',
            'provider' => $provider,
            'q' => $q,
            'providers' => ProviderRegistry::meta(),
        ]);
    }

    public function create()
    {
        return view('collections.payments.create', [
            'providers' => ProviderRegistry::meta(),
        ]);
    }

    public function store(Request $request, PaymentEngine $engine)
    {
        $data = $request->validate([
            'provider' => 'required|string',
            'phone' => 'required|string|max:20',
            'amount' => 'required|numeric|min:500|max:5000000',
            'customer_name' => 'required|string|min:2|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $result = $engine->initiate([
            'provider' => $data['provider'],
            'phone' => $data['phone'],
            'amount' => $data['amount'],
            'customer_name' => $data['customer_name'],
            'description' => $data['description'] ?? null,
        ], $request->user());

        if (! ($result['success'] ?? false)) {
            return back()->withErrors(['phone' => $result['error'] ?? 'Failed to initiate payment.'])->withInput();
        }

        return redirect()
            ->route('collections.payments.show', $result['transaction'])
            ->with('success', 'Collection request sent. The customer must authorize on their phone.');
    }

    /**
     * Public JSON endpoint used by /pay (member checkout, no login).
     */
    public function publicStore(Request $request, PaymentEngine $engine)
    {
        $data = $request->validate([
            'payer_name' => 'required|string|min:2|max:100',
            'phone_number' => 'required|string|max:20',
            'amount' => 'required|numeric|min:500|max:5000000',
            'description' => 'required|string|max:255',
            'provider' => 'nullable|string',
            'akiba_type' => 'nullable|string',
            'uwekezaji_type' => 'nullable|string',
            'hisa_type' => 'nullable|string',
        ]);

        $desc = trim($data['description']);
        if ($data['akiba_type'] ?? null) {
            $desc = $desc === 'Akiba' ? 'Akiba - '.$data['akiba_type'] : $desc;
        }
        if (($data['uwekezaji_type'] ?? null) && $desc === 'Uwekezaji') {
            $desc = 'Uwekezaji - '.$data['uwekezaji_type'];
        }
        if (($data['hisa_type'] ?? null) && str_starts_with($desc, 'Hisa') && $desc === 'Hisa') {
            $desc = 'Hisa - '.$data['hisa_type'];
        }

        $result = $engine->initiate([
            'provider' => $data['provider'] ?? 'auto',
            'phone' => $data['phone_number'],
            'amount' => $data['amount'],
            'customer_name' => trim($data['payer_name']),
            'description' => $desc,
        ]);

        if (! ($result['success'] ?? false)) {
            return response()->json(['success' => false, 'message' => $result['error'] ?? 'Imeshindikana kutuma malipo.'], $result['http_status'] ?? 422);
        }

        $payment = $result['payment'];

        return response()->json([
            'success' => true,
            'message' => 'USSD imetumwa. Thibitisha kwenye simu.',
            'order_reference' => $payment->reference,
            'orderReference' => $payment->reference,
            'reference' => $payment->reference,
            'phone_number' => $payment->customer_phone,
            'phone' => $payment->customer_phone,
            'amount' => (float) $payment->amount,
            'provider' => $payment->provider,
        ]);
    }

    public function show(ProviderTransaction $transaction)
    {
        if ($transaction->payment) {
            return redirect()->route('payments.show', $transaction->payment);
        }

        $transaction->load(['customer']);

        return view('collections.payments.show', ['txn' => $transaction]);
    }

    public function refresh(ProviderTransaction $transaction, PaymentEngine $engine)
    {
        if (! $transaction->payment) {
            return back()->withErrors(['status' => 'No linked payment to refresh.']);
        }

        $result = $engine->refresh($transaction->payment);

        if (! ($result['success'] ?? false)) {
            return back()->withErrors(['status' => $result['error'] ?? 'Refresh failed.']);
        }

        return back()->with('success', 'Status refreshed: '.($result['status'] ?? 'PENDING'));
    }

    public function refunds()
    {
        $refunds = Refund::with('payment')->latest()->paginate(15, ['*'], 'refunds');
        $reversals = Reversal::with('payment')->latest()->paginate(15, ['*'], 'reversals');

        return view('collections.payments.refunds', compact('refunds', 'reversals'));
    }

    public function requestRefund(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:1|max:'.$payment->amount,
            'reason' => 'required|string|max:500',
        ]);

        $refund = Refund::create([
            'reference' => 'RFD'.now()->format('YmdHis').random_int(100, 999),
            'payment_id' => $payment->id,
            'provider' => $payment->provider ?: 'mpesa',
            'amount' => $data['amount'],
            'reason' => $data['reason'],
            'status' => 'requested',
            'requested_by' => $request->user()->id,
        ]);

        Audit::log('payment.refund.requested', $refund, ['payment' => $payment->reference, 'amount' => $data['amount']]);

        return back()->with('success', 'Refund '.$refund->reference.' requested. A supervisor must approve it.');
    }

    public function approveRefund(Request $request, Refund $refund)
    {
        if ($refund->status !== 'requested') {
            return back()->withErrors(['status' => 'Only requested refunds can be approved.']);
        }

        $refund->update(['status' => 'approved', 'approved_by' => $request->user()->id]);
        Audit::log('payment.refund.approved', $refund, ['payment' => $refund->payment?->reference, 'amount' => $refund->amount]);

        return back()->with('success', 'Refund '.$refund->reference.' approved for manual processing via '.$refund->payment?->provider.'.');
    }

    public function requestReversal(Request $request, Payment $payment, PaymentEngine $engine)
    {
        $data = $request->validate(['reason' => 'required|string|max:500']);

        $reversal = Reversal::create([
            'reference' => 'REV'.now()->format('YmdHis').random_int(100, 999),
            'payment_id' => $payment->id,
            'provider' => $payment->provider ?: 'mpesa',
            'amount' => $payment->amount,
            'reason' => $data['reason'],
            'status' => 'completed',
            'requested_by' => $request->user()->id,
            'approved_by' => $request->user()->id,
        ]);

        $txn = ProviderTransaction::where('payment_id', $payment->id)->latest()->first();
        if ($txn) {
            $engine->applyStatus($txn, 'REVERSED', 'manual');
        } else {
            $payment->recordHistory($payment->status, 'reversed', 'manual');
            $payment->update(['status' => 'reversed']);
        }

        Audit::log('payment.reversed', $reversal, ['payment' => $payment->reference, 'amount' => $payment->amount]);

        return back()->with('success', 'Payment reversed. Original record kept; correction recorded as '.$reversal->reference.'.');
    }
}
