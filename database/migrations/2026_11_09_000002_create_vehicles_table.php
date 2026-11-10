<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_number', 40)->unique();
            $table->enum('type', ['TI', 'JEMBER']);
            $table->unsignedInteger('trips')->default(0);
            $table->decimal('gross_revenue', 18, 2)->default(0);
            $table->decimal('travel_allowance', 18, 2)->default(0);
            $table->decimal('profit', 18, 2)->default(0);
            $table->decimal('operating_cost', 18, 2)->default(0);
            $table->enum('availability', ['Ready', 'Disewakan'])->default('Ready');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('vehicles'); }
};
