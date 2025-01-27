<?php

use Illuminate\Database\Migrations\Migration;
use \Kesify\MicroserviceSkeleton\Models\Microservice;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Microservice::create([
            'name' => 'example',
            'host' => 'host',
            'ssl' => false,
            'active' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Microservice::where('name', 'example')->delete();
    }
};
