<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_ads_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('google_ads_campaigns')->nullOnDelete();
            $table->date('date');
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->decimal('ctr', 8, 4)->nullable();
            $table->decimal('cpc', 12, 2)->nullable();
            $table->decimal('cost', 14, 2)->default(0);
            $table->unsignedBigInteger('conversions')->default(0);
            $table->decimal('conversion_value', 14, 2)->nullable();
            $table->decimal('roas', 10, 4)->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'campaign_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_ads_insights');
    }
};