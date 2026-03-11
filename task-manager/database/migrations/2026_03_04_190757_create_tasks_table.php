<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('tasks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
        $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
        $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
        $table->string('title');
        $table->text('description')->nullable();
        $table->enum('status', ['todo', 'in_progress', 'waiting', 'done', 'cancelled'])
              ->default('todo');
        $table->tinyInteger('complexity')->default(3); // 1 à 5
        $table->decimal('priority_score', 5, 2)->nullable();
        $table->datetime('deadline')->nullable();
        $table->timestamps();
    });
}
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
