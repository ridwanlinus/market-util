<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ga_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained('ga_properties')->nullOnDelete();
            $table->date('date');
            $table->unsignedBigInteger('users')->default(0);
            $table->unsignedBigInteger('new_users')->default(0);
            $table->unsignedBigInteger('sessions')->default(0);
            $table->unsignedBigInteger('pageviews')->default(0);
            $table->decimal('avg_session_duration', 10, 2)->nullable();
            $table->decimal('bounce_rate', 8, 4)->nullable();
            $table->json('top_pages')->nullable();
            $table->json('channels')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'property_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ga_insights');
    }
};