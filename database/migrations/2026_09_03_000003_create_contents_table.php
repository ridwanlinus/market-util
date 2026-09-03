<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['single', 'carousel'])->default('single');
            $table->unsignedSmallInteger('slides_count')->default(1);
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('draft');
            $table->json('design')->nullable();
            $table->string('cover_path')->nullable();
            $table->json('files')->nullable();
            $table->text('caption')->nullable();
            $table->string('platform')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->text('approval_note')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};