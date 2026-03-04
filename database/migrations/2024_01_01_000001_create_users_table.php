<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->unique()->comment('admission_number or staff_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password');
            $table->enum('role', ['student', 'lecturer', 'admin'])->default('student');
            $table->string('course')->nullable()->comment('For students');
            $table->string('department')->nullable()->comment('For lecturers');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            
            $table->index('identifier');
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
