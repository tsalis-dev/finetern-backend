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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Student specific
            $table->string('school')->nullable();
            $table->string('department')->nullable();
            $table->string('nisn')->nullable();
            $table->string('grade')->nullable();
            $table->string('gender')->nullable();
            $table->date('birth_date')->nullable();
            
            // Shared info
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->text('bio')->nullable();
            
            // Industry specific
            $table->string('company_name')->nullable();
            $table->text('company_address')->nullable();
            $table->text('company_description')->nullable();
            
            // School specific
            $table->string('npsn')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
