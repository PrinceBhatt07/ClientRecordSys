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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('updated_by')->nullable();
            $table->string('name');
            $table->string('contact')->nullable();
            $table->string('email')->unique();
            $table->longText('skype_id')->nullable();
            $table->longText('address')->nullable();
            $table->string('country')->nullable();
            $table->longText('website_url')->nullable();
            $table->longText('linkedin_url')->nullable();
            $table->longText('facebook_url')->nullable();
            $table->boolean('is_archived')->default(0);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
