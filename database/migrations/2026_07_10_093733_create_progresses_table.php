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
        Schema::create('progresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('stage_key');
            $table->string('status')->nullable(); // ApplicationStatus value
            $table->text('note')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->timestamps();

            // Latest snapshot per (application, stage) — one row each
            $table->unique(['application_id', 'stage_key']);

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
        Schema::dropIfExists('progresses');
    }
};
