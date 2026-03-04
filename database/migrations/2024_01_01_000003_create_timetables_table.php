<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->enum('day', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('venue')->nullable();
            $table->decimal('venue_latitude', 10, 8);
            $table->decimal('venue_longitude', 11, 8);
            $table->integer('allowed_radius')->default(50)->comment('Radius in meters');
            $table->timestamps();
            
            $table->index(['course_id', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetables');
    }
};
