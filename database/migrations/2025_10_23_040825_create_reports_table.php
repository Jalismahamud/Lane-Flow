<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('type')->index();
            $table->string('lane')->nullable();
            $table->text('description')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->string('status')->default('blocked');
            $table->boolean('official_cleared')->default(false);
            $table->text('admin_note')->nullable();
            $table->unsignedInteger('clear_report_count')->default(0);
            $table->unsignedInteger('blocked_report_count')->default(1);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['latitude', 'longitude']);
            $table->index('status');
            $table->index('expires_at');
            $table->index('user_id');
            $table->index(['status', 'expires_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
