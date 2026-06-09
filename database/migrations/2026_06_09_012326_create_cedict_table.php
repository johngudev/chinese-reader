<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cedict', function (Blueprint $table) {
            $table->id();
            $table->string('simplified', 64)->index();      
            $table->string('traditional', 64)->nullable();
            $table->string('pinyin', 255)->nullable();          
            $table->string('pinyin_numeric', 255)->nullable();  
            $table->text('english')->nullable();                
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cedict');
    }
};
