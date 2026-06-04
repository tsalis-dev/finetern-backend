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
        Schema::create('vacancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // industry user
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('work_mode')->default('WFO');
            $table->integer('quota')->default(1);
            $table->date('deadline')->nullable();
            $table->json('required_skills')->nullable();
            $table->string('category')->nullable();
            $table->string('status')->default('open'); // open, closed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacancies');
    }
};
