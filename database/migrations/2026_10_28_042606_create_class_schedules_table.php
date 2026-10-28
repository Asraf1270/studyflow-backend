<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('class_schedules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('course_id')->constrained()->cascadeOnDelete();
            $t->unsignedTinyInteger('day_of_week'); // 1=Mon .. 7=Sun (ISO-8601)
            $t->time('start_time');
            $t->time('end_time');
            $t->string('location', 120)->nullable();
            $t->string('instructor', 120)->nullable();
            $t->unsignedSmallInteger('reminder_offset_minutes')->nullable();
            $t->timestamps();

            $t->index(['user_id', 'day_of_week']);
            $t->index(['course_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('class_schedules'); }
};