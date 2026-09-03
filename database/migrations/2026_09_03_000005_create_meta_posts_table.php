<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meta_page_id')->nullable()->constrained()->nullOnDelete();
            $table->string('post_id')->nullable();
            $table->enum('kind', ['post', 'ad'])->default('post');
            $table->text('message')->nullable();
            $table->date('posted_at')->nullable();
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('reach')->default(0);
            $table->unsignedBigInteger('likes')->default(0);
            $table->unsignedBigInteger('comments')->default(0);
            $table->unsignedBigInteger('shares')->default(0);
            $table->unsignedBigInteger('saves')->default(0);
            $table->unsignedBigInteger('video_views')->default(0);
            $table->unsignedBigInteger('link_clicks')->default(0);
            $table->unsignedBigInteger('followers_count')->nullable();
            $table->decimal('spend', 14, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_posts');
    }
};