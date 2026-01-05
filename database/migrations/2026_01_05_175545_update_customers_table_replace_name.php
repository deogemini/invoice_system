<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('customers', 'name')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('name')->after('id')->nullable();
            });
        }

        // Migrate existing data
        if (DB::getDriverName() === 'sqlite') {
            DB::statement("UPDATE customers SET name = firstname || ' ' || lastname WHERE name IS NULL");
        } else {
            DB::statement("UPDATE customers SET name = CONCAT(firstname, ' ', lastname) WHERE name IS NULL");
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            if (Schema::hasColumn('customers', 'firstname')) {
                $table->dropColumn(['firstname']);
            }
             if (Schema::hasColumn('customers', 'lastname')) {
                $table->dropColumn(['lastname']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
        });

        // Attempt to split name back (imperfect)
        DB::statement("UPDATE customers SET firstname = name");

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
