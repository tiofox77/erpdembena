<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::where('email', 'criador@gmail.com')->first();

if ($user) {
    $user->password = bcrypt('Admin2017');
    $user->save();
    echo "✓ Senha atualizada com sucesso para: {$user->name} ({$user->email})\n";
} else {
    echo "✗ Usuário com email 'criador@gmail.com' não foi encontrado.\n";
}
