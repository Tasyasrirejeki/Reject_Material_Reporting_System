<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE reject_reports MODIFY status ENUM('submitted','pending','verified','rejected') NOT NULL DEFAULT 'submitted'");
    }

    public function down(): void
    {
        DB::statement("UPDATE reject_reports SET status='pending' WHERE status='submitted'");
        DB::statement("ALTER TABLE reject_reports MODIFY status ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending'");
    }
};

