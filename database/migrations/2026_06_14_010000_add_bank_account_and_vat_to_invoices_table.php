<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('bank_account_id')->nullable()->after('customer_id')->constrained('bank_accounts')->nullOnDelete();
            $table->boolean('include_vat')->default(false)->after('discount');
            $table->decimal('vat_rate', 5, 2)->default(18)->after('include_vat');
            $table->decimal('vat_amount', 10, 2)->default(0)->after('vat_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bank_account_id');
            $table->dropColumn(['include_vat', 'vat_rate', 'vat_amount']);
        });
    }
};
