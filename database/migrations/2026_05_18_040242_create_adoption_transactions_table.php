<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
Schema::create('adoption_transactions', function (Blueprint $table) {

    $table->id();

    $table->foreignId('user_id')
        ->constrained('users')
        ->cascadeOnDelete();

    $table->foreignId('adoption_project_id')
        ->constrained('adoption_projects')
        ->cascadeOnDelete();

    /*
    |--------------------------------------------------------------------------
    | INVESTMENT
    |--------------------------------------------------------------------------
    */
    $table->integer('slot');

    $table->bigInteger('modal');

    $table->decimal('roi_percent', 5, 2);

    $table->bigInteger('estimasi_profit');

    $table->bigInteger('total_akhir');

    /*
    |--------------------------------------------------------------------------
    | STATUS
    |--------------------------------------------------------------------------
    */
    $table->enum('status', [

        'pending',
        'active',
        'completed',
        'cancelled'

    ])->default('pending');

    /*
    |--------------------------------------------------------------------------
    | DATE
    |--------------------------------------------------------------------------
    */
    $table->timestamp('mulai_at')->nullable();

    $table->timestamp('jatuh_tempo_at')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adoption_transactions');
    }
};
