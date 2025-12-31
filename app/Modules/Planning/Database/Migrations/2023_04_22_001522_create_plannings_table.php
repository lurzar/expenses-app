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
        Schema::create('plannings', function (Blueprint $table) {
            $table->id();
            $table->ulid('planning_id')->unique();
            $table->ulid('user_id')->index()->foreign()->references('user_id')->on('users')->onDelete('cascade');
            $table->string('month');
            $table->string('year');
            $table->float('salary');
            $table->json('sections');
            $table->json('totals');
            $table->string('slug')->unique();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plannings');
    }
};
