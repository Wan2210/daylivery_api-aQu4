<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('email', config('admin.email'))->first();

if (! $user) {
    fwrite(STDOUT, "user-missing\n");
    exit(1);
}

$user->password = Illuminate\Support\Facades\Hash::make(config('admin.password'));
$user->save();

$updated = $user->fresh();

fwrite(STDOUT, password_verify(config('admin.password'), $updated->password) ? "hash-ok\n" : "hash-mismatch\n");
