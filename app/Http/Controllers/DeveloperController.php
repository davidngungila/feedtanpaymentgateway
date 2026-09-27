<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\ApiRequestLog;
use App\Models\WebhookEvent;
use App\Support\Security\Audit;
use Illuminate\Http\Request;

class DeveloperController extends Controller
{
    public function keys()
    {
        $keys = ApiKey::with('creator')->latest()->paginate(15);

        return view('developers.keys', compact('keys'));
    }

    public function storeKey(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:100']);

        ['model' => $key, 'plaintext' => $plain] = ApiKey::mint($data['name'], $request->user());
        Audit::log('api_key.created', $key, ['name' => $key->name, 'prefix' => $key->prefix]);

        return back()->with('success', 'API key created. Copy it now — the full key will never be shown again.')
            ->with('plain_key', $plain);
    }

    public function revokeKey(Request $request, ApiKey $key)
    {
        $key->update(['is_active' => false]);
        Audit::log('api_key.revoked', $key, ['prefix' => $key->prefix]);

        return back()->with('success', 'API key revoked.');
    }

    public function webhooks()
    {
        $events = WebhookEvent::latest()->paginate(20);

        return view('developers.webhooks', compact('events'));
    }

    public function apiLogs()
    {
        $logs = ApiRequestLog::with('key')->latest()->paginate(20);

        return view('developers.logs', compact('logs'));
    }

    public function docs()
    {
        return view('developers.docs');
    }
}
