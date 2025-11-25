<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use RonasIT\Support\Traits\MigrationTrait;

return new class extends Migration
{
    use MigrationTrait;

    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->integer('media_id');
            $table->integer('user_id');
            $table->string('title')->nullable();
            $table->string('body')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
