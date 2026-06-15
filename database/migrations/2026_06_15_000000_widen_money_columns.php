<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE invoices MODIFY sub_total DECIMAL(15, 2) NOT NULL');
        DB::statement('ALTER TABLE invoices MODIFY discount DECIMAL(15, 2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE invoices MODIFY vat_amount DECIMAL(15, 2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE invoices MODIFY total DECIMAL(15, 2) NOT NULL');
        DB::statement('ALTER TABLE invoices MODIFY paid_amount DECIMAL(15, 2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE invoice_items MODIFY unit_price DECIMAL(15, 2) NOT NULL');
        DB::statement('ALTER TABLE products MODIFY unit_price DECIMAL(15, 2) NOT NULL');
        DB::statement('ALTER TABLE delivery_note_items MODIFY unit_price DECIMAL(15, 2) NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE invoices MODIFY sub_total DECIMAL(10, 2) NOT NULL');
        DB::statement('ALTER TABLE invoices MODIFY discount DECIMAL(10, 2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE invoices MODIFY vat_amount DECIMAL(10, 2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE invoices MODIFY total DECIMAL(10, 2) NOT NULL');
        DB::statement('ALTER TABLE invoices MODIFY paid_amount DECIMAL(10, 2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE invoice_items MODIFY unit_price DECIMAL(10, 2) NOT NULL');
        DB::statement('ALTER TABLE products MODIFY unit_price DECIMAL(10, 2) NOT NULL');
        DB::statement('ALTER TABLE delivery_note_items MODIFY unit_price DECIMAL(10, 2) NOT NULL DEFAULT 0');
    }
};
