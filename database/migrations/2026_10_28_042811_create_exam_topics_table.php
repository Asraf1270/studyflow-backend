<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('exam_topics', function (Blueprint $t) {
            $t->id();
            $t->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $t->string('title', 200);
            $t->boolean('is_completed')->default(false);
            $t->timestamps();

            $t->index(['exam_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('exam_topics'); }
};