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
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('role')->index(); // mirrors the Spatie role for fast filtering
            $table->string('status')->default('pending'); // pending/active/blocked
            $table->foreignId('organization_id')->nullable()->constrained();
            $table->string('referred_from')->nullable();
            $table->jsonb('preferred_contact')->default(json_encode(['email'])); // ["email", "sms", "scheduled_call"]
            $table->jsonb('metadata')->nullable();
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('name')->nullable(); // restore the original column

            $table->dropConstrainedForeignId('organization_id'); // drops FK constraint + column

            $table->dropColumn([
                'first_name',
                'middle_name',
                'last_name',
                'phone',
                'role',
                'status',
                'referred_from',
                'preferred_contact',
                'metadata',
            ]);
        });
    }
};
