<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('moora_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('total_vehicles');
            $table->longText('weight_snapshot');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('moora_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('run_id')->constrained('moora_runs')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->double('norm_c1')->default(0);
            $table->double('norm_c2')->default(0);
            $table->double('norm_c3')->default(0);
            $table->double('weighted_c1')->default(0);
            $table->double('weighted_c2')->default(0);
            $table->double('weighted_c3')->default(0);
            $table->double('optimization_value')->default(0);
            $table->unsignedInteger('ranking');
            $table->enum('availability_snapshot', ['Ready', 'Disewakan'])->default('Ready');
            $table->timestamps();
            $table->unique(['run_id', 'vehicle_id']);
            $table->index(['run_id', 'ranking']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('moora_results');
        Schema::dropIfExists('moora_runs');
    }
};
