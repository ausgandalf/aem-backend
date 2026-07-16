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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploaded_by')->nullable()->constrained('users');
            $table->string('disk')->default('s3');            // 's3' or 'fs'
            $table->string('bucket')->default('wrblo-arm-storage');
            $table->string('object_key');
            $table->string('original_name');
            $table->string('extension')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('thumbnail')->nullable();          // thumbnail object key
            $table->unsignedBigInteger('size')->default(0);   // bytes
            $table->text('tags')->nullable();
            $table->text('about')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
