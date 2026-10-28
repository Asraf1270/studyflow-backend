<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('study_sessions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $t->uuid('client_session_id');            // idempotency key from app
            $t->dateTime('started_at');
            $t->dateTime('ended_at')->nullable();
            $t->unsignedInteger('duration_seconds')->nullable();
            $t->timestamps();

            $t->unique(['user_id', 'client_session_id']); // prevents duplicates on retry
            $t->index(['user_id', 'started_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('study_sessions'); }
};