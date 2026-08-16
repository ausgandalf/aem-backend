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
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('stage_key');

            // Sector SNAPSHOT — inspections copy the sector's data at inspection time
            // rather than linking to it. sector_key may match a sectors.key, but it is
            // intentionally NOT a foreign key: sectors can be deleted or renamed without
            // touching inspections, and sector_key may point to a sector that no longer
            // exists. Treat these as a frozen copy, not a live reference.
            $table->string('sector_key');
            $table->string('sector_label')->nullable();
            $table->string('sector_description')->nullable();
            $table->text('sector_section')->nullable();
            $table->smallInteger('sector_order')->nullable(); // snapshot of the sector's order

            $table->string('status')->default('pending')->index(); // pending/passed/rejected, or something(TBD)
            $table->text('note')->nullable();
            $table->jsonb('metadata')->nullable();
            // created_by is null for auto-snapshotted inspections; only set when an
            // officer manually adds one via the Process "Complete" action.
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');

            $table->timestamps();

            $table->foreign('stage_key')
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
        Schema::dropIfExists('inspections');
    }
};
