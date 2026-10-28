<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_notifications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('type', 60);                    // e.g. 'task_due', 'exam_soon'
            $t->string('title', 200);
            $t->text('body')->nullable();
            $t->json('data')->nullable();
            $t->dateTime('read_at')->nullable();
            $t->timestamps();

            $t->index(['user_id', 'read_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('user_notifications'); }
};