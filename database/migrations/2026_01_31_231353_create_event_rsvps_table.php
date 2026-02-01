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
        Schema::create('event_rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')
                ->constrained('tenants')
                ->cascadeOnDelete();
            $table->foreignId('event_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->enum('communication_preference', ['email', 'whatsapp', 'sms']);
            $table->enum('notifications_scope', ['event_only', 'workspace', 'platform']);
            $table->enum('status', ['going', 'not_going', 'maybe'])->default('going');
            $table->enum('source', ['public_page'])->default('public_page');
            $table->timestamps();

            $table->unique(['event_id', 'email']);
            $table->index(['workspace_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_rsvps');
    }
};
