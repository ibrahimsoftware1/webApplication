<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('friendships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Sender
            $table->foreignId('friend_id')->constrained('users')->onDelete('cascade'); // Receiver
            $table->enum('status', ['pending', 'accepted', 'rejected', 'blocked'])->default('pending');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            // Prevent duplicate friendships
            $table->unique(['user_id', 'friend_id']);

            // Indexes for performance
            $table->index('user_id');
            $table->index('friend_id');
            $table->index('status');
            $table->index('accepted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('friendships');
    }
};
