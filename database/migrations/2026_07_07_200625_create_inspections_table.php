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
            $table->string('stage_key');
            $table->string('sector_key');
            
            $table->string('status')->default('pending')->index(); // pending/passed/rejected, or something(TBD)            
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();

            $table->foreign('stage_key')
                ->references('key')
                ->on('stages')
                ->onUpdate('cascade');

            $table->foreign('sector_key')
                ->references('key')
                ->on('sectors')
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
