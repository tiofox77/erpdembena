<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class FixMrpMigrations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Desativar verificação de chaves estrangeiras temporariamente
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Verificar e criar tabelas bases
        $this->createLocationsTable();
        $this->createResourceTypesTable();
        $this->createResourcesTable();
        $this->createCapacityPlansTable();
        
        // Tabelas MRP
        $this->createMrpTables();
        
        // Ajustes de colunas e chaves estrangeiras
        $this->updateMrpTables();
        
        // Reativar verificação de chaves estrangeiras
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Nada a fazer, pois esta é uma migração de correção
    }

    private function createLocationsTable()
    {
        if (!Schema::hasTable('locations')) {
            Schema::create('locations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->text('description')->nullable();
                $table->boolean('active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('users');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    private function createResourceTypesTable()
    {
        if (!Schema::hasTable('resource_types')) {
            Schema::create('resource_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->text('description')->nullable();
                $table->enum('category', ['machine', 'labor', 'tool', 'other']);
                $table->boolean('active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('users');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    private function createResourcesTable()
    {
        if (!Schema::hasTable('resources')) {
            Schema::create('resources', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->unsignedBigInteger('resource_type_id');
                $table->unsignedBigInteger('location_id')->nullable();
                $table->unsignedBigInteger('department_id')->nullable();
                $table->decimal('capacity_per_hour', 10, 2)->default(0);
                $table->decimal('cost_per_hour', 10, 2)->default(0);
                $table->text('description')->nullable();
                $table->boolean('active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                
                $table->foreign('resource_type_id')->references('id')->on('resource_types');
                $table->foreign('location_id')->references('id')->on('locations');
                $table->foreign('department_id')->references('id')->on('departments')->nullable();
                $table->foreign('created_by')->references('id')->on('users');
                
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    private function createCapacityPlansTable()
    {
        if (!Schema::hasTable('capacity_plans')) {
            Schema::create('capacity_plans', function (Blueprint $table) {
                $table->id();
                $table->string('plan_number')->unique();
                $table->string('title');
                $table->date('start_date');
                $table->date('end_date');
                $table->unsignedBigInteger('resource_id');
                $table->decimal('planned_capacity', 10, 2);
                $table->decimal('actual_capacity', 10, 2)->nullable();
                $table->text('notes')->nullable();
                $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
                $table->unsignedBigInteger('created_by')->nullable();
                
                $table->foreign('resource_id')->references('id')->on('resources');
                $table->foreign('created_by')->references('id')->on('users');
                
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    private function createMrpTables()
    {
        // Verificar e criar tabelas do MRP que ainda não existem
        if (!Schema::hasTable('mrp_demand_forecasts')) {
            Schema::create('mrp_demand_forecasts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->date('forecast_date');
                $table->integer('forecast_quantity');
                $table->integer('actual_quantity')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                $table->foreign('product_id')->references('id')->on('products');
                $table->foreign('created_by')->references('id')->on('users');
            });
        }

        if (!Schema::hasTable('mrp_bom_headers')) {
            Schema::create('mrp_bom_headers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->string('bom_number')->unique();
                $table->string('description')->nullable();
                $table->date('effective_date');
                $table->date('expiry_date')->nullable();
                $table->enum('status', ['draft', 'active', 'obsolete'])->default('draft');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                $table->foreign('product_id')->references('id')->on('products');
                $table->foreign('created_by')->references('id')->on('users');
            });
        }

        if (!Schema::hasTable('mrp_bom_details')) {
            Schema::create('mrp_bom_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bom_header_id');
                $table->unsignedBigInteger('component_id');
                $table->decimal('quantity', 10, 2);
                $table->unsignedBigInteger('unit_type_id')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                $table->foreign('bom_header_id')->references('id')->on('mrp_bom_headers');
                $table->foreign('component_id')->references('id')->on('products');
                $table->foreign('unit_type_id')->references('id')->on('unit_types');
                $table->foreign('created_by')->references('id')->on('users');
            });
        }

        if (!Schema::hasTable('mrp_inventory_levels')) {
            Schema::create('mrp_inventory_levels', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->integer('current_stock');
                $table->integer('minimum_stock')->default(0);
                $table->integer('maximum_stock')->nullable();
                $table->integer('reorder_point')->default(0);
                $table->integer('safety_stock')->default(0);
                $table->integer('allocated_stock')->default(0);
                $table->integer('available_stock')->default(0);
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                $table->foreign('product_id')->references('id')->on('products');
                $table->foreign('created_by')->references('id')->on('users');
                $table->foreign('updated_by')->references('id')->on('users');
            });
        }

        if (!Schema::hasTable('mrp_production_schedules')) {
            Schema::create('mrp_production_schedules', function (Blueprint $table) {
                $table->id();
                $table->string('schedule_number')->unique();
                $table->unsignedBigInteger('product_id');
                $table->date('start_date');
                $table->date('end_date');
                $table->integer('planned_quantity');
                $table->integer('completed_quantity')->default(0);
                $table->decimal('planned_hours', 10, 2)->nullable();
                $table->decimal('actual_hours', 10, 2)->nullable();
                $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
                $table->text('notes')->nullable();
                $table->boolean('stock_movements_processed')->default(false);
                $table->unsignedBigInteger('responsible')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                $table->foreign('product_id')->references('id')->on('products');
                $table->foreign('responsible')->references('id')->on('users');
                $table->foreign('created_by')->references('id')->on('users');
            });
        }

        if (!Schema::hasTable('mrp_production_orders')) {
            Schema::create('mrp_production_orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_number')->unique();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('schedule_id')->nullable();
                $table->date('planned_date');
                $table->date('due_date');
                $table->integer('quantity');
                $table->integer('completed_quantity')->default(0);
                $table->enum('status', ['draft', 'released', 'in_progress', 'completed', 'cancelled'])->default('draft');
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                $table->foreign('product_id')->references('id')->on('products');
                $table->foreign('schedule_id')->references('id')->on('mrp_production_schedules');
                $table->foreign('created_by')->references('id')->on('users');
            });
        }

        if (!Schema::hasTable('mrp_purchase_plan_headers')) {
            Schema::create('mrp_purchase_plan_headers', function (Blueprint $table) {
                $table->id();
                $table->string('plan_number')->unique();
                $table->string('title');
                $table->date('plan_date');
                $table->date('required_date');
                $table->enum('status', ['draft', 'approved', 'in_progress', 'completed', 'cancelled'])->default('draft');
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                $table->foreign('created_by')->references('id')->on('users');
            });
        }

        if (!Schema::hasTable('mrp_purchase_plan_items')) {
            Schema::create('mrp_purchase_plan_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('plan_header_id');
                $table->unsignedBigInteger('product_id');
                $table->integer('required_quantity');
                $table->integer('ordered_quantity')->default(0);
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->decimal('estimated_cost', 10, 2)->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                $table->foreign('plan_header_id')->references('id')->on('mrp_purchase_plan_headers');
                $table->foreign('product_id')->references('id')->on('products');
                $table->foreign('supplier_id')->references('id')->on('suppliers');
                $table->foreign('created_by')->references('id')->on('users');
            });
        }

        if (!Schema::hasTable('production_daily_plans')) {
            Schema::create('production_daily_plans', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('schedule_id');
                $table->date('plan_date');
                $table->integer('planned_quantity');
                $table->integer('completed_quantity')->default(0);
                $table->text('notes')->nullable();
                $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                $table->foreign('schedule_id')->references('id')->on('mrp_production_schedules');
                $table->foreign('created_by')->references('id')->on('users');
            });
        }
    }

    private function updateMrpTables()
    {
        // Atualizar chave estrangeira no departamento dos recursos
        if (Schema::hasTable('resources') && Schema::hasColumn('resources', 'department_id')) {
            Schema::table('resources', function (Blueprint $table) {
                // Verificar se a chave estrangeira já existe
                $foreignKeys = $this->listTableForeignKeys('resources');
                
                if (in_array('resources_department_id_foreign', $foreignKeys)) {
                    $table->dropForeign('resources_department_id_foreign');
                }
                
                // Adicionar novamente com nullable
                $table->foreign('department_id')
                    ->references('id')->on('departments')
                    ->nullable();
            });
        }
    }

    /**
     * Obter a lista de chaves estrangeiras de uma tabela
     */
    protected function listTableForeignKeys($table)
    {
        // Verificar as chaves estrangeiras usando SQL direto para evitar problemas com o Doctrine
        $schema = Schema::getConnection()->getDatabaseName();
        
        $foreignKeys = [];
        try {
            $fks = DB::select(
                "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS 
                WHERE CONSTRAINT_TYPE = 'FOREIGN KEY' 
                AND TABLE_SCHEMA = ? 
                AND TABLE_NAME = ?", [$schema, $table]
            );
            
            foreach ($fks as $fk) {
                $foreignKeys[] = $fk->CONSTRAINT_NAME;
            }
        } catch (\Exception $e) {
            // Ignorar erro se a tabela não existir
        }
        
        return $foreignKeys;
    }
}
