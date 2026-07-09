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
        Schema::create('application_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('stage_key')->nullable()->index(); // stages.key at the time of the action
            $table->unsignedBigInteger('inspection_id')->default(0); // 0 = not applicable
            $table->unsignedBigInteger('document_id')->default(0);   // 0 = not applicable
            $table->string('status')->nullable(); // ApplicationStatus value
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_logs');
    }
};
