<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tasks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $t->string('title', 200);
            $t->text('description')->nullable();
            $t->dateTime('due_at')->nullable();       // stored UTC
            $t->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $t->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $t->dateTime('reminder_at')->nullable();
            $t->dateTime('completed_at')->nullable();
            $t->timestamps();
            $t->softDeletes();

            $t->index(['user_id', 'status', 'due_at']);
            $t->index(['user_id', 'course_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('tasks'); }
};