<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $administrator = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => 'Admin@12345',
                'role' => User::ROLE_ADMINISTRATOR,
                'is_active' => true,
            ]
        );

        foreach (['customers', 'products', 'invoices', 'invoice_items', 'bank_accounts', 'company_settings'] as $table) {
            DB::table($table)->whereNull('user_id')->update(['user_id' => $administrator->id]);
            DB::table($table)->whereNull('created_by')->update(['created_by' => $administrator->id]);
            DB::table($table)->whereNull('updated_by')->update(['updated_by' => $administrator->id]);
        }
    }
}
