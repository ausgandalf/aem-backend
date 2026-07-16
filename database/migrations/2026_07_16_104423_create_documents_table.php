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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('stage_key')->nullable()->index();  // stage the doc was submitted on
            $table->string('sector_key')->nullable()->index(); // inspection sector (if any)
            $table->text('description')->nullable();
            $table->string('flag')->default('ok');             // ok / warning / invalid / ignore
            $table->text('flag_note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users'); // original attacher, never changes
            $table->foreignId('updated_by')->nullable()->constrained('users'); // last editor
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->foreign('stage_key')->references('key')->on('stages')->onUpdate('cascade');
            $table->foreign('sector_key')->references('key')->on('sectors')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
