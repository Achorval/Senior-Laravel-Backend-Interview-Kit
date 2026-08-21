<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_fundings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets');
            $table->unsignedBigInteger('amount');
            $table->string('reference');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_fundings');
    }
};
