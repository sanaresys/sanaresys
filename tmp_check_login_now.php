<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenant = App\Models\Tenant::where('centro_id', 5)->first();
tenancy()->initialize($tenant);
$stored = Illuminate\Support\Facades\DB::table('users')->where('email','mario@ejemplo.com')->value('password');
echo 'stored=' . $stored . PHP_EOL;
echo 'hash_check=' . (Illuminate\Support\Facades\Hash::check('12345678', $stored) ? 'ok' : 'fail') . PHP_EOL;
echo 'auth_attempt=' . (Filament\Facades\Filament::auth()->attempt(['email'=>'mario@ejemplo.com','password'=>'12345678']) ? 'ok' : 'fail') . PHP_EOL;
tenancy()->end();
