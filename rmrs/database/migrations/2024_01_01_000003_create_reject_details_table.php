<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reject_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reject_reports')->onDelete('cascade');
            $table->string('defect_type');
            $table->enum('defect_category', ['critical', 'major', 'minor'])->default('minor');
            $table->integer('quantity')->default(0);
            $table->text('description')->nullable();
            $table->string('defect_location', 255)->nullable();
            $table->timestamps();

            $table->index('report_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reject_details');
    }
};
