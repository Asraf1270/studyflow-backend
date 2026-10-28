<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $t->string('title', 200);
            $t->longText('content')->nullable();
            $t->boolean('is_favorite')->default(false);
            $t->dateTime('last_edited_at')->nullable();
            $t->timestamps();
            $t->softDeletes();

            $t->index(['user_id', 'course_id']);
            $t->index(['user_id', 'is_favorite']);
        });
    }
    public function down(): void { Schema::dropIfExists('notes'); }
};