<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saved_words', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('generated_text_id')->nullable()->constrained()->nullOnDelete();
            $table->string('word', 32);
            $table->string('pinyin')->nullable();
            $table->text('english')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'generated_text_id', 'word']);   // re-clicks don't duplicate
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('saved_words');
    }
};
