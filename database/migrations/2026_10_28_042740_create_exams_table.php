<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('exams', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('course_id')->constrained()->cascadeOnDelete();
            $t->string('title', 200);
            $t->dateTime('exam_at');                  // stored UTC
            $t->string('location', 120)->nullable();
            $t->text('notes')->nullable();
            $t->dateTime('reminder_at')->nullable();
            $t->timestamps();

            $t->index(['user_id', 'exam_at']);
            $t->index(['course_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('exams'); }
};