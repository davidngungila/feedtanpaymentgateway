<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\PaymentLink;
use App\Models\PaymentRequest;
use App\Models\RecurringCollection;
use App\Payments\PaymentEngine;
use App\Payments\ProviderRegistry;
use App\Support\Security\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CollectionController extends Controller
{
    // ---------- Payment requests ----------
    public function requests(Request $request)
    {
        $requests = PaymentRequest::with('customer')->latest()->paginate(15);

        return view('collections.requests.index', ['requests' => $requests]);
    }

    public function createRequest()
    {
        return view('collections.requests.create', ['providers' => ProviderRegistry::meta()]);
    }

    public function storeRequest(Request $request)
    {
        $data = $request->validate([
            'provider' => 'required|string',
            'phone' => 'required|string|max:20',
            'customer_name' => 'required|string|min:2|max:100',
            'amount' => 'required|numeric|min:500|max:5000000',
            'purpose' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $phone = PaymentEngine::normalizePhone($data['phone']);
        if (! $phone) {
            return back()->withErrors(['phone' => 'Invalid Tanzanian mobile number.'])->withInput();
        }

        $customer = Customer::findOrCreateFromPayment($data['customer_name'], $phone);

        $req = PaymentRequest::create([
            'reference' => 'REQ'.now()->format('YmdHis').random_int(100, 999),
            'customer_id' => $customer->id,
            'provider' => $data['provider'],
            'amount' => $data['amount'],
            'purpose' => $data['purpose'],
            'description' => $data['description'] ?? null,
            'status' => 'pending',
            'expires_at' => $data['expires_at'] ?? now()->addDay(),
            'initiated_by' => $request->user()->id,
        ]);

        Audit::log('payment_request.created', $req, ['provider' => $req->provider, 'amount' => $req->amount]);

        return redirect()->route('collections.requests')->with('success', 'Payment request '.$req->reference.' created.');
    }

    public function cancelRequest(Request $request, PaymentRequest $req)
    {
        if ($req->status !== 'pending') {
            return back()->withErrors(['status' => 'Only pending requests can be cancelled.']);
        }
        $req->update(['status' => 'cancelled']);
        Audit::log('payment_request.cancelled', $req, []);

        return back()->with('success', 'Request cancelled.');
    }

    public function collectRequest(Request $request, PaymentRequest $req, PaymentEngine $engine)
    {
        if ($req->status !== 'pending') {
            return back()->withErrors(['status' => 'Only pending requests can be collected.']);
        }

        $result = $engine->initiate([
            'provider' => $req->provider,
            'phone' => $req->customer?->revealPhone() ?? '',
            'amount' => $req->amount,
            'customer_name' => $req->customer?->name ?? 'Mwanachama',
            'description' => $req->description,
        ], $request->user());

        if (! ($result['success'] ?? false)) {
            return back()->withErrors(['phone' => $result['error'] ?? 'Collection failed.']);
        }

        $req->update(['status' => 'sent']);

        return redirect()->route('collections.payments.show', $result['transaction'])->with('success', 'USSD push sent to the customer.');
    }

    // ---------- Payment links ----------
    public function links()
    {
        $links = PaymentLink::with('request.customer')->latest()->paginate(15);

        return view('collections.links.index', ['links' => $links]);
    }

    public function storeLink(Request $request)
    {
        $data = $request->validate(['payment_request_id' => 'required|string', 'expires_at' => 'nullable|date|after:now']);

        $requestId = decrypt_id($data['payment_request_id']);
        $req = $requestId ? PaymentRequest::find($requestId) : null;
        if (! $req) {
            return back()->withErrors(['payment_request_id' => 'Payment request not found.']);
        }
        if ($req->status !== 'pending') {
            return back()->withErrors(['payment_request_id' => 'Links can only be created for pending requests.']);
        }

        $link = PaymentLink::create([
            'payment_request_id' => $req->id,
            'status' => 'active',
            'expires_at' => $data['expires_at'] ?? $req->expires_at,
        ]);

        Audit::log('payment_link.created', $link, ['request' => $req->reference]);

        return back()->with('success', 'Payment link created: '.route('collections.links.short', $link->code));
    }

    public function revokeLink(Request $request, PaymentLink $link)
    {
        $link->update(['status' => 'revoked']);
        Audit::log('payment_link.revoked', $link, []);

        return back()->with('success', 'Link revoked.');
    }

    public function checkout(string $token)
    {
        $link = PaymentLink::with('request.customer')->where('token', $token)->firstOrFail();

        return $this->renderCheckout($link);
    }

    public function checkoutByCode(string $code)
    {
        $link = PaymentLink::with('request.customer')->where('code', strtoupper($code))->firstOrFail();

        return $this->renderCheckout($link);
    }

    protected function renderCheckout(PaymentLink $link)
    {
        if ($link->status !== 'active' || ($link->expires_at && $link->expires_at->isPast())) {
            abort(410, 'This payment link is no longer active.');
        }

        return view('collections.links.checkout', ['link' => $link, 'providers' => ProviderRegistry::meta()]);
    }

    public function checkoutPay(Request $request, string $token, PaymentEngine $engine)
    {
        $link = PaymentLink::with('request.customer')->where('token', $token)->firstOrFail();

        return $this->payForLink($request, $link, $engine);
    }

    public function payByCode(Request $request, string $code, PaymentEngine $engine)
    {
        $link = PaymentLink::with('request.customer')->where('code', strtoupper($code))->firstOrFail();

        return $this->payForLink($request, $link, $engine);
    }

    protected function payForLink(Request $request, PaymentLink $link, PaymentEngine $engine)
    {
        if ($link->status !== 'active' || ($link->expires_at && $link->expires_at->isPast())) {
            abort(410, 'This payment link is no longer active.');
        }

        $data = $request->validate([
            'phone' => 'required|string|max:20',
            'customer_name' => 'required|string|min:2|max:100',
        ]);

        $result = $engine->initiate([
            'provider' => 'auto',
            'phone' => $data['phone'],
            'amount' => $link->request->amount,
            'customer_name' => $data['customer_name'],
            'description' => $link->request->purpose
                ? $link->request->purpose.($link->request->description ? ' — '.$link->request->description : '')
                : $link->request->description,
        ]);

        if (! ($result['success'] ?? false)) {
            return back()->withErrors(['phone' => $result['error'] ?? 'Payment failed.'])->withInput();
        }

        $link->update(['status' => 'redeemed', 'redeemed_at' => now()]);
        $link->request->update(['status' => 'sent']);

        return redirect()->route('payments.status.page', ['reference' => $result['payment']->reference]);
    }

    // ---------- Invoices ----------
    public function invoices()
    {
        $invoices = Invoice::with('customer')->latest()->paginate(15);

        return view('collections.invoices.index', ['invoices' => $invoices]);
    }

    public function createInvoice()
    {
        return view('collections.invoices.create', ['providers' => ProviderRegistry::meta()]);
    }

    public function storeInvoice(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string|max:20',
            'customer_name' => 'required|string|min:2|max:100',
            'provider' => 'nullable|string',
            'amount' => 'required|numeric|min:500|max:5000000',
            'due_at' => 'nullable|date|after:now',
            'notes' => 'nullable|string|max:500',
        ]);

        $phone = PaymentEngine::normalizePhone($data['phone']);
        if (! $phone) {
            return back()->withErrors(['phone' => 'Invalid Tanzanian mobile number.'])->withInput();
        }

        $customer = Customer::findOrCreateFromPayment($data['customer_name'], $phone);

        $invoice = Invoice::create([
            'number' => 'INV'.now()->format('YmdHis').random_int(100, 999),
            'customer_id' => $customer->id,
            'provider' => $data['provider'] ?: null,
            'amount' => $data['amount'],
            'due_at' => $data['due_at'] ?? null,
            'status' => 'issued',
            'notes' => $data['notes'] ?? null,
        ]);

        Audit::log('invoice.created', $invoice, ['amount' => $invoice->amount]);

        return redirect()->route('collections.invoices')->with('success', 'Invoice '.$invoice->number.' issued.');
    }

    public function collectInvoice(Request $request, Invoice $invoice, PaymentEngine $engine)
    {
        if (! in_array($invoice->status, ['issued', 'overdue'], true)) {
            return back()->withErrors(['status' => 'Only issued invoices can be collected.']);
        }

        $result = $engine->initiate([
            'provider' => $invoice->provider ?: 'auto',
            'phone' => $invoice->customer?->revealPhone() ?? '',
            'amount' => $invoice->amount,
            'customer_name' => $invoice->customer?->name ?? 'Mwanachama',
            'description' => 'Invoice '.$invoice->number,
        ], $request->user());

        if (! ($result['success'] ?? false)) {
            return back()->withErrors(['phone' => $result['error'] ?? 'Collection failed.']);
        }

        $invoice->update(['status' => 'sent']);

        return redirect()->route('collections.payments.show', $result['transaction'])->with('success', 'USSD push sent for invoice '.$invoice->number.'.');
    }

    public function cancelInvoice(Invoice $invoice)
    {
        if (! in_array($invoice->status, ['issued', 'overdue', 'sent'], true)) {
            return back()->withErrors(['status' => 'This invoice can no longer be cancelled.']);
        }
        $invoice->update(['status' => 'cancelled']);
        Audit::log('invoice.cancelled', $invoice, []);

        return back()->with('success', 'Invoice cancelled.');
    }

    // ---------- Recurring ----------
    public function recurring()
    {
        $items = RecurringCollection::with('customer')->latest()->paginate(15);

        return view('collections.recurring.index', ['items' => $items]);
    }

    public function createRecurring()
    {
        return view('collections.recurring.create', ['providers' => ProviderRegistry::meta()]);
    }

    public function storeRecurring(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string|max:20',
            'customer_name' => 'required|string|min:2|max:100',
            'provider' => 'required|string',
            'amount' => 'required|numeric|min:500|max:5000000',
            'frequency' => 'required|in:daily,weekly,monthly',
            'next_run_at' => 'required|date|after:now',
        ]);

        $phone = PaymentEngine::normalizePhone($data['phone']);
        if (! $phone) {
            return back()->withErrors(['phone' => 'Invalid Tanzanian mobile number.'])->withInput();
        }

        $customer = Customer::findOrCreateFromPayment($data['customer_name'], $phone);

        $item = RecurringCollection::create([
            'reference' => 'REC'.now()->format('YmdHis').random_int(100, 999),
            'customer_id' => $customer->id,
            'provider' => $data['provider'],
            'amount' => $data['amount'],
            'frequency' => $data['frequency'],
            'next_run_at' => $data['next_run_at'],
            'status' => 'active',
        ]);

        Audit::log('recurring.created', $item, ['frequency' => $item->frequency, 'amount' => $item->amount]);

        return redirect()->route('collections.recurring')->with('success', 'Recurring collection '.$item->reference.' scheduled.');
    }

    public function pauseRecurring(RecurringCollection $recurring)
    {
        $recurring->update(['status' => 'paused']);

        return back()->with('success', 'Recurring collection paused.');
    }

    public function resumeRecurring(RecurringCollection $recurring)
    {
        $recurring->update(['status' => 'active']);

        return back()->with('success', 'Recurring collection resumed.');
    }
}
