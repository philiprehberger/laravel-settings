<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('settings.table', 'settings'), function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string');
            $table->string('group', 100)->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamps();

            $table->unique(['key', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('settings.table', 'settings'));
    }
};
