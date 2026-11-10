<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('criteria', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->unique();
            $table->string('name', 100);
            $table->enum('criterion_type', ['benefit', 'cost']);
            $table->decimal('weight', 7, 4);
            $table->string('description', 240);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('criteria'); }
};
