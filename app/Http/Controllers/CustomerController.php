<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Payments\PaymentEngine;
use App\Support\Security\Audit;
use App\Support\Security\Crypto;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $query = Customer::withCount('transactions')->latest();

        if ($q !== '') {
            $digits = preg_replace('/[^0-9]/', '', $q);
            $query->where(function ($w) use ($q, $digits) {
                $w->where('name', 'like', "%{$q}%");
                if ($digits !== '') {
                    $w->orWhere('phone_hash', Crypto::blindIndex('customer', $digits));
                }
            });
        }

        return view('customers.index', ['customers' => $query->paginate(15)->withQueryString(), 'q' => $q]);
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:150',
            'national_id' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
        ]);

        $phone = PaymentEngine::normalizePhone($data['phone']);
        if (! $phone) {
            return back()->withErrors(['phone' => 'Invalid Tanzanian mobile number.'])->withInput();
        }
        if (Customer::findByPhone($phone)) {
            return back()->withErrors(['phone' => 'A customer with this phone number already exists.'])->withInput();
        }

        $customer = Customer::create([
            'name' => $data['name'],
            'phone_encrypted' => $phone,
            'phone_hash' => Crypto::blindIndex('customer', $phone),
            'email_encrypted' => $data['email'] ?? null,
            'email_hash' => Crypto::blindIndex('customer', $data['email'] ?? null),
            'national_id_encrypted' => $data['national_id'] ?? null,
            'national_id_hash' => Crypto::blindIndex('customer', $data['national_id'] ?? null),
            'address_encrypted' => $data['address'] ?? null,
            'status' => 'active',
        ]);

        Audit::log('customer.created', $customer, ['name' => $customer->name]);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer created. Phone stored encrypted.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['transactions' => fn ($q) => $q->with('payment')->latest()->limit(20), 'paymentRequests' => fn ($q) => $q->latest()->limit(10)]);

        return view('customers.show', ['customer' => $customer]);
    }

    /**
     * Authorized reveal of the full phone number. Audited every time.
     */
    public function reveal(Request $request, Customer $customer)
    {
        $phone = $customer->revealPhone();

        Audit::log('customer.revealed', $customer, ['name' => $customer->name]);

        return response()->json(['success' => true, 'phone' => $phone]);
    }
}
