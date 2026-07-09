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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();

            // Ownership
            $table->foreignId('applicant_id')->constrained('users');
            $table->foreignId('organization_id')->nullable()->constrained('organizations');

            // Snapshot of the organization, copied in at a specific stage (TBD)
            $table->jsonb('organization_details')->nullable();

            // Project
            $table->string('project_title');
            $table->string('project_location')->nullable();
            $table->decimal('requested_amount', 15, 2)->nullable();
            $table->string('currency', 3)->default('GBP');
            $table->jsonb('project_details')->nullable();

            // Workflow position (stage keys reference stages.key, like inspections.stage_key)
            $table->string('current_stage')->nullable()->index();
            $table->string('current_status')->default('in_progress')->index(); // passed/in_progress/on_hold/rejected/deleted
            $table->string('prev_stage')->nullable();
            $table->string('prev_status')->nullable();

            $table->foreignId('updated_by')->nullable()->constrained('users');

            $table->jsonb('metadata')->nullable();

            $table->timestamps();

            $table->foreign('current_stage')
                ->references('key')
                ->on('stages')
                ->onUpdate('cascade');

            $table->foreign('prev_stage')
                ->references('key')
                ->on('stages')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
