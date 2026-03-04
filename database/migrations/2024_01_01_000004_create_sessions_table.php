<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('lecturer_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->string('qr_token')->unique();
            $table->enum('status', ['active', 'ended'])->default('active');
            $table->decimal('venue_latitude', 10, 8);
            $table->decimal('venue_longitude', 11, 8);
            $table->integer('allowed_radius')->default(50);
            $table->timestamps();
            
            $table->index('qr_token');
            $table->index(['status', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
