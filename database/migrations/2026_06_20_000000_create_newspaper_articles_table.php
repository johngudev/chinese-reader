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
        Schema::create('newspaper_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');                          // English title
            $table->string('slug')->unique();                 // SEO-friendly URL: /articles/{slug}
            $table->text('summary')->nullable();              // excerpt -> list + meta description
            $table->text('body');                             // the Chinese article
            $table->text('english')->nullable();              // translation / notes
            $table->json('definitions')->nullable();          // glossary: [{word, pinyin, english}, ...]
            $table->unsignedTinyInteger('hsk_level')->nullable();
            $table->date('publication_date')->nullable();
            $table->boolean('is_published')->default(false);  // draft vs live
            $table->timestamps();

            // Fast lookups for the public list (published, newest first)
            $table->index(['is_published', 'publication_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('newspaper_articles');
    }
};
