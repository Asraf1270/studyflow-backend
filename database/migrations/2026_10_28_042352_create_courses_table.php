<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('courses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('name', 120);
            $t->string('code', 30)->nullable();
            $t->string('semester', 40)->nullable();
            $t->char('color', 7)->default('#4F46E5'); // hex #RRGGBB
            $t->timestamps();
            $t->softDeletes();

            $t->index(['user_id']);
            $t->index(['user_id', 'semester']);
        });
    }
    public function down(): void { Schema::dropIfExists('courses'); }
};