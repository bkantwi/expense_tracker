<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;

class DefaultAccountsSeeder extends Seeder
{
    public function run(): void
    {
        // Seed for your first user only (adjust as needed)
        $user = User::first();
        if (!$user) return;

        $defaults = [
            ['name' => 'Cash',        'type' => 'cash'],
            ['name' => 'MoMo (MTN)',  'type' => 'momo'],
            ['name' => 'Main Bank',   'type' => 'bank'],
            ['name' => 'Visa Card',   'type' => 'card'],
        ];

        foreach ($defaults as $d) {
            Account::firstOrCreate(
                ['user_id' => $user->id, 'name' => $d['name']],
                ['type' => $d['type'], 'currency' => 'GHS', 'starting_balance' => 0]
            );
        }
    }
}
