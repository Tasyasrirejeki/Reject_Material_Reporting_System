<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reject_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_number', 50)->unique();
            $table->foreignId('operator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('line_leader_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('department', ['lenscase', 'pouching']);
            // Shift 1: 07:00 - 19:00 | Shift 2: 19:00 - 07:00
            $table->enum('shift', ['1', '2']);
            $table->date('production_date');
            // product_name = Jenis Material, product_code = Batch Material
            $table->string('product_name');
            $table->string('product_code', 100)->nullable();
            $table->integer('total_production_qty')->default(0);
            $table->integer('total_reject_qty')->default(0);
            // submitted = terkirim (tidak perlu verifikasi)
            // pending   = sudah diedit operator, menunggu verifikasi Line Leader
            // verified / rejected = hasil verifikasi Line Leader atas laporan yang diedit
            $table->enum('status', ['submitted', 'pending', 'verified', 'rejected'])->default('submitted');
            $table->text('operator_notes')->nullable();
            $table->text('leader_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->unsignedInteger('edit_count')->default(0);
            $table->json('edit_history')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['department', 'production_date']);
            $table->index(['status', 'production_date']);
            $table->index('operator_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reject_reports');
    }
};
