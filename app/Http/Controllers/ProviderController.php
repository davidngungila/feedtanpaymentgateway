<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\ProviderCredential;
use App\Models\ProviderTransaction;
use App\Models\WebhookEvent;
use App\Payments\ProviderRegistry;
use App\Support\Security\Audit;
use App\Support\Security\Crypto;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProviderController extends Controller
{
    public function index()
    {
        $providers = Provider::orderBy('id')->get();
        if ($providers->isEmpty()) {
            foreach (ProviderRegistry::codes() as $code) {
                $adapter = ProviderRegistry::get($code);
                $providers->push(Provider::create([
                    'code' => $code,
                    'name' => $adapter->name(),
                    'color' => $adapter->color(),
                    'driver' => config("mobile_money.providers.{$code}.driver", 'clickpesa'),
                    'is_active' => true,
                    'config' => ['prefixes' => config("mobile_money.providers.{$code}.prefixes", [])],
                ]));
            }
        }

        $today = today();
        $stats = [];
        foreach ($providers as $p) {
            $base = ProviderTransaction::where('provider', $p->code);
            $stats[$p->code] = [
                'today' => (float) (clone $base)->whereDate('created_at', $today)->where('status', 'SUCCESS')->sum('amount'),
                'total' => (float) (clone $base)->where('status', 'SUCCESS')->sum('amount'),
                'pending' => (clone $base)->whereIn('status', ['PENDING', 'PROCESSING'])->count(),
                'success' => (clone $base)->where('status', 'SUCCESS')->count(),
                'failed' => (clone $base)->where('status', 'FAILED')->count(),
            ];
        }

        return view('providers.index', compact('providers', 'stats'));
    }

    public function show(string $provider)
    {
        return redirect()->route('providers.collections', $provider);
    }

    protected function resolve(string $provider): array
    {
        $model = Provider::where('code', $provider)->firstOrFail();
        $adapter = ProviderRegistry::get($model->code);

        return [$model, $adapter, route('webhooks.receive', $model->code)];
    }

    public function collections(string $provider)
    {
        [$model, $adapter, $endpoint] = $this->resolve($provider);
        $collections = ProviderTransaction::with('customer')->where('provider', $model->code)->latest()->paginate(15);

        return view('providers.collections', [
            'provider' => $model,
            'endpoint' => $endpoint,
            'collections' => $collections,
            'tableProviders' => [$model->code => ['name' => $model->name, 'color' => $model->color]],
        ]);
    }

    public function statusPage(string $provider)
    {
        [$model, $adapter, $endpoint] = $this->resolve($provider);

        return view('providers.status', ['provider' => $model, 'endpoint' => $endpoint]);
    }

    public function webhooksPage(string $provider)
    {
        [$model, $adapter, $endpoint] = $this->resolve($provider);
        $webhooks = WebhookEvent::where('provider', $model->code)->latest()->paginate(15);

        return view('providers.webhooks', ['provider' => $model, 'endpoint' => $endpoint, 'webhooks' => $webhooks]);
    }

    public function logsPage(string $provider)
    {
        [$model, $adapter, $endpoint] = $this->resolve($provider);
        $webhooks = WebhookEvent::where('provider', $model->code)->latest()->paginate(20);

        return view('providers.logs', ['provider' => $model, 'endpoint' => $endpoint, 'webhooks' => $webhooks]);
    }

    public function configPage(string $provider)
    {
        [$model, $adapter, $endpoint] = $this->resolve($provider);

        return view('providers.config', ['provider' => $model, 'endpoint' => $endpoint]);
    }

    public function credentialsPage(string $provider)
    {
        [$model, $adapter, $endpoint] = $this->resolve($provider);

        return view('providers.credentials', ['provider' => $model, 'endpoint' => $endpoint, 'credential' => $model->liveCredential()]);
    }

    public function updateConfig(Request $request, string $provider)
    {
        $model = Provider::where('code', $provider)->firstOrFail();
        $data = $request->validate([
            'is_active' => 'nullable|boolean',
            'prefixes' => 'nullable|string',
            'fee_percent' => 'nullable|numeric|min:0|max:100',
            'fee_flat' => 'nullable|numeric|min:0',
            'base_url' => 'nullable|url|max:255',
            'initiate_path' => 'nullable|string|max:255',
            'status_path' => 'nullable|string|max:255',
            'test_path' => 'nullable|string|max:255',
            'auth_type' => 'nullable|in:api-key,bearer,none',
        ]);

        $prefixes = array_values(array_filter(array_map('trim', explode(',', (string) ($data['prefixes'] ?? '')))));
        $direct = array_merge($model->config['direct'] ?? [], array_filter([
            'base_url' => $data['base_url'] ?? null,
            'initiate_path' => $data['initiate_path'] ?? null,
            'status_path' => $data['status_path'] ?? null,
            'test_path' => $data['test_path'] ?? null,
            'auth_type' => $data['auth_type'] ?? null,
        ], fn ($v) => $v !== null && $v !== ''));
        $config = array_merge($model->config ?? [], [
            'prefixes' => $prefixes !== [] ? $prefixes : $model->prefixes(),
            'fee' => ['percent' => (float) ($data['fee_percent'] ?? 0), 'flat' => (float) ($data['fee_flat'] ?? 0)],
            'direct' => $direct,
        ]);

        // Keep runtime config in sync for this request lifecycle too.
        config(["mobile_money.providers.{$model->code}.prefixes" => $config['prefixes']]);
        config(["mobile_money.providers.{$model->code}.fee" => $config['fee']]);
        foreach ($direct as $k => $v) {
            config(["mobile_money.providers.{$model->code}.direct.{$k}" => $v]);
        }

        $model->update(['config' => $config, 'is_active' => $request->boolean('is_active', true)]);
        Audit::log('provider.config.changed', $model, ['code' => $model->code, 'active' => $model->is_active]);

        return back()->with('success', $model->name.' configuration saved.');
    }

    public function storeCredential(Request $request, string $provider)
    {
        $model = Provider::where('code', $provider)->firstOrFail();
        $data = $request->validate([
            'environment' => 'required|in:live,sandbox',
            'label' => 'nullable|string|max:100',
            'client_id' => 'nullable|string|max:255',
            'client_secret' => 'nullable|string|max:500',
            'api_key' => 'nullable|string|max:500',
            'webhook_secret' => 'nullable|string|max:500',
        ]);

        $credential = ProviderCredential::updateOrCreate(
            ['provider_id' => $model->id, 'environment' => $data['environment']],
            [
                'label' => $data['label'] ?? null,
                'client_id' => $data['client_id'] ?? null,
                'client_secret' => $data['client_secret'] ?: null,
                'api_key' => $data['api_key'] ?: null,
                'webhook_secret' => $data['webhook_secret'] ?: null,
            ]
        );

        Audit::log('provider.credential.changed', $credential, ['provider' => $model->code, 'environment' => $data['environment']]);

        return back()->with('success', 'Credentials stored encrypted for '.$model->name.' ('.$data['environment'].').');
    }

    public function testCredential(string $provider)
    {
        $model = Provider::where('code', $provider)->firstOrFail();
        $adapter = ProviderRegistry::get($model->code);

        $result = $adapter->testConnection();
        Audit::log('provider.credential.tested', $model, ['provider' => $model->code, 'success' => (bool) ($result['success'] ?? false)]);

        if (! ($result['success'] ?? false)) {
            return back()->withErrors(['credentials' => $result['message'] ?? 'Connection test failed.']);
        }

        return back()->with('success', $result['message'] ?? ('Connection OK for '.$model->name.'.'));
    }

    public function rotateWebhookSecret(string $provider)
    {
        $model = Provider::where('code', $provider)->firstOrFail();
        $plain = 'whsec_'.Str::random(32);

        $credential = ProviderCredential::updateOrCreate(
            ['provider_id' => $model->id, 'environment' => 'live'],
            ['webhook_secret' => $plain]
        );

        Audit::log('provider.credential.changed', $credential, ['provider' => $model->code, 'rotated' => 'webhook_secret']);

        return back()->with('success', 'Webhook secret rotated. Copy it now — it will never be shown again.')
            ->with('plain_secret', $plain);
    }

    public function statusQuery(Request $request, string $provider)
    {
        $model = Provider::where('code', $provider)->firstOrFail();
        $data = $request->validate(['reference' => 'required|string|max:40']);

        $adapter = ProviderRegistry::get($model->code);
        $result = $adapter->query($data['reference']);

        Audit::log('provider.status.queried', $model, ['provider' => $model->code, 'reference' => $data['reference']]);

        return back()->with('status_result', [
            'reference' => $data['reference'],
            'status' => $result['status'] ?? 'PENDING',
            'amount' => $result['amount'] ?? null,
            'provider_reference' => $result['provider_reference'] ?? null,
            'error' => $result['error'] ?? null,
        ]);
    }
}
