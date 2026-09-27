<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$enc = 'MjpkM2IwMWY3Mzk2ZTZkNGM4N2E2YzRlOWU0ZmFiMzQwMGRiZDE5MzMyYzk3NDY5YmIyNWU4NmZiNjgxOGU1ODJk';
$dec = decrypt_id($enc);
echo "enc $enc dec $dec\n";
$id = $dec;
$p = \App\Models\Payout::find($id);
echo "DB payout: ".($p ? json_encode($p->toArray(), JSON_PRETTY_PRINT) : 'not found')."\n";
echo "total: ".\App\Models\Payout::count()."\n";
$all = \App\Models\Payout::latest()->limit(3)->get(['id','reference','amount','status']);
echo "recent: ".json_encode($all->toArray(), JSON_PRETTY_PRINT)."\n";

$svc = app(\App\Services\ClickPesaService::class);
$res = $svc->queryAllPayouts(['limit'=>5]);
echo "API queryAll: ".json_encode($res, JSON_PRETTY_PRINT)."\n";
