<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class AddDueDateToMaintenanceTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:add-due-date';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adiciona a coluna due_date à tabela maintenance_tasks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando tabela maintenance_tasks...');

        // Verifica se a tabela existe
        if (!Schema::hasTable('maintenance_tasks')) {
            $this->error('A tabela maintenance_tasks não existe!');

            // Criando a tabela
            $this->info('Criando a tabela maintenance_tasks...');
            Schema::create('maintenance_tasks', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->dateTime('due_date')->nullable();
                $table->dateTime('completion_date')->nullable();
                $table->enum('status', ['pending', 'in_progress', 'completed', 'delayed', 'cancelled'])->default('pending');
                $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
                $table->foreignId('equipment_id')->nullable();
                $table->foreignId('category_id')->nullable();
                $table->foreignId('assigned_to')->nullable();
                $table->foreignId('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
            $this->info('Tabela maintenance_tasks criada com sucesso!');
            return;
        }

        // Verifica se a coluna já existe
        if (Schema::hasColumn('maintenance_tasks', 'due_date')) {
            $this->info('A coluna due_date já existe na tabela maintenance_tasks!');
            return;
        }

        // Adicionando a coluna due_date
        $this->info('Adicionando a coluna due_date à tabela maintenance_tasks...');
        Schema::table('maintenance_tasks', function (Blueprint $table) {
            $table->dateTime('due_date')->nullable()->after('description');
        });

        $this->info('Coluna due_date adicionada com sucesso!');
    }
}
