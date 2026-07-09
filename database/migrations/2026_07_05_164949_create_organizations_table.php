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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();

            // Core Identity
            $table->string('name');
            $table->string('registration_number')->nullable();
            $table->string('legal_status')->nullable();
            $table->string('type')->nullable();
            $table->year('founded_year')->nullable();

            // Registered (legal) address
            $table->string('registered_country')->nullable();
            $table->string('registered_state_province')->nullable();
            $table->string('registered_city')->nullable();
            $table->string('registered_address_line1')->nullable();
            $table->string('registered_address_line2')->nullable();
            $table->string('registered_postal_code')->nullable();

            // Operating / correspondence address (may differ from the registered one)
            $table->boolean('current_same_as_registered')->default(true);
            $table->string('current_country')->nullable();
            $table->string('current_state_province')->nullable();
            $table->string('current_city')->nullable();
            $table->string('current_address_line1')->nullable();
            $table->string('current_address_line2')->nullable();
            $table->string('current_postal_code')->nullable();

            // Contact
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('website_url')->nullable();

            // Social Links
            $table->string('social_facebook')->nullable();
            $table->string('social_linkedin')->nullable();
            $table->string('social_twitter')->nullable();
            $table->string('social_instagram')->nullable();
            $table->string('social_youtube')->nullable();
            $table->string('social_whatsapp')->nullable();

            // Financials
            $table->string('currency', 3)->default('GBP');
            $table->decimal('annual_income', 15, 2)->nullable();
            $table->decimal('annual_expenditure', 15, 2)->nullable();
            $table->text('reserves_policy')->nullable();

            // Metadata
            $table->string('status')->default('pending'); // pending/verified/off
            $table->string('note')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
