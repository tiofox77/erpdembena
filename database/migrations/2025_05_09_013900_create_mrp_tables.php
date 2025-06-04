<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create mrp_demand_forecasts table
        if (!Schema::hasTable('mrp_demand_forecasts')) {
            Schema::create('mrp_demand_forecasts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->date('forecast_date');
                $table->integer('forecast_quantity');
                $table->decimal('confidence_level', 5, 2)->nullable()->comment('Confidence level of the forecast (0-100%)');
                $table->enum('forecast_type', ['manual', 'automatic', 'adjusted'])->default('manual');
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                // Adiciona as chaves estrangeiras apenas se as tabelas referenciadas existirem
                if (Schema::hasTable('sc_products')) {
                    $table->foreign('product_id')->references('id')->on('sc_products')->onDelete('cascade');
                }
                
                if (Schema::hasTable('users')) {
                    $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                    $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
                }
            });
        }

        // Create mrp_bom_headers table
        if (!Schema::hasTable('mrp_bom_headers')) {
            Schema::create('mrp_bom_headers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->string('bom_number');
                $table->string('description');
                $table->enum('status', ['draft', 'active', 'obsolete'])->default('draft');
                $table->date('effective_date');
                $table->date('expiration_date')->nullable();
                $table->integer('version')->default(1);
                $table->enum('uom', ['unit', 'kg', 'l', 'g', 'ml', 'pcs'])->default('unit');
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                // Adiciona as chaves estrangeiras apenas se as tabelas referenciadas existirem
                if (Schema::hasTable('sc_products')) {
                    $table->foreign('product_id')->references('id')->on('sc_products')->onDelete('cascade');
                }
                
                if (Schema::hasTable('users')) {
                    $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                    $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
                }
            });
        }

        // Create mrp_bom_details table
        if (!Schema::hasTable('mrp_bom_details')) {
            Schema::create('mrp_bom_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bom_header_id');
                $table->unsignedBigInteger('component_id')->comment('Component or raw material');
                $table->decimal('quantity', 10, 2);
                $table->enum('uom', ['unit', 'kg', 'l', 'g', 'ml', 'pcs'])->default('unit');
                $table->string('reference_designator')->nullable()->comment('Position reference in the assembly');
                $table->boolean('is_critical')->default(false);
                $table->string('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                // Adiciona as chaves estrangeiras apenas se as tabelas referenciadas existirem
                if (Schema::hasTable('mrp_bom_headers')) {
                    $table->foreign('bom_header_id')->references('id')->on('mrp_bom_headers')->onDelete('cascade');
                }
                
                if (Schema::hasTable('sc_products')) {
                    $table->foreign('component_id')->references('id')->on('sc_products')->onDelete('cascade');
                }
            });
        }

        // Create mrp_inventory_levels table
        if (!Schema::hasTable('mrp_inventory_levels')) {
            Schema::create('mrp_inventory_levels', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('location_id');
                $table->integer('reorder_point')->default(0);
                $table->integer('safety_stock')->default(0);
                $table->integer('max_stock_level')->default(0);
                $table->integer('economic_order_quantity')->default(0)->comment('EOQ');
                $table->integer('lead_time_days')->default(0)->comment('Average lead time in days');
                $table->enum('abc_classification', ['A', 'B', 'C'])->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                // Adiciona as chaves estrangeiras apenas se as tabelas referenciadas existirem
                if (Schema::hasTable('sc_products')) {
                    $table->foreign('product_id')->references('id')->on('sc_products')->onDelete('cascade');
                }
                
                if (Schema::hasTable('sc_inventory_locations')) {
                    $table->foreign('location_id')->references('id')->on('sc_inventory_locations')->onDelete('cascade');
                }
            });
        }

        // Create mrp_production_schedules table
        if (!Schema::hasTable('mrp_production_schedules')) {
            Schema::create('mrp_production_schedules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->date('start_date');
                $table->date('end_date');
                $table->integer('planned_quantity');
                $table->enum('status', ['draft', 'confirmed', 'in_progress', 'completed', 'cancelled'])->default('draft');
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
                $table->unsignedBigInteger('location_id')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                // Adiciona as chaves estrangeiras apenas se as tabelas referenciadas existirem
                if (Schema::hasTable('sc_products')) {
                    $table->foreign('product_id')->references('id')->on('sc_products')->onDelete('cascade');
                }
                
                if (Schema::hasTable('sc_inventory_locations')) {
                    $table->foreign('location_id')->references('id')->on('sc_inventory_locations')->onDelete('set null');
                }
            });
        }

        // Create mrp_production_orders table
        if (!Schema::hasTable('mrp_production_orders')) {
            Schema::create('mrp_production_orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_number')->unique();
                $table->unsignedBigInteger('product_id');
                $table->integer('quantity');
                $table->date('planned_start_date');
                $table->date('planned_end_date');
                $table->date('actual_start_date')->nullable();
                $table->date('actual_end_date')->nullable();
                $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
                $table->enum('status', ['draft', 'released', 'in_progress', 'completed', 'cancelled'])->default('draft');
                $table->unsignedBigInteger('production_schedule_id')->nullable();
                $table->unsignedBigInteger('location_id')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                // Adiciona as chaves estrangeiras apenas se as tabelas referenciadas existirem
                if (Schema::hasTable('sc_products')) {
                    $table->foreign('product_id')->references('id')->on('sc_products')->onDelete('cascade');
                }
                
                if (Schema::hasTable('mrp_production_schedules')) {
                    $table->foreign('production_schedule_id')->references('id')->on('mrp_production_schedules')->onDelete('set null');
                }
                
                if (Schema::hasTable('sc_inventory_locations')) {
                    $table->foreign('location_id')->references('id')->on('sc_inventory_locations')->onDelete('set null');
                }
            });
        }

        // Create mrp_purchase_plans table
        if (!Schema::hasTable('mrp_purchase_plans')) {
            Schema::create('mrp_purchase_plans', function (Blueprint $table) {
                $table->id();
                $table->string('plan_number')->unique();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('supplier_id');
                $table->integer('order_quantity');
                $table->date('planned_order_date');
                $table->date('expected_delivery_date');
                $table->decimal('estimated_cost', 10, 2)->nullable();
                $table->enum('status', ['draft', 'approved', 'ordered', 'received', 'cancelled'])->default('draft');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                // Adiciona as chaves estrangeiras apenas se as tabelas referenciadas existirem
                if (Schema::hasTable('sc_products')) {
                    $table->foreign('product_id')->references('id')->on('sc_products')->onDelete('cascade');
                }
                
                if (Schema::hasTable('sc_suppliers')) {
                    $table->foreign('supplier_id')->references('id')->on('sc_suppliers')->onDelete('cascade');
                }
            });
        }

        // Create mrp_capacity_plans table
        if (!Schema::hasTable('mrp_capacity_plans')) {
            Schema::create('mrp_capacity_plans', function (Blueprint $table) {
                $table->id();
                $table->string('plan_number')->unique();
                $table->unsignedBigInteger('work_center_id')->nullable();
                $table->string('resource_name');
                $table->decimal('available_hours', 8, 2);
                $table->decimal('required_hours', 8, 2);
                $table->decimal('efficiency', 5, 2)->default(100.00)->comment('Efficiency percentage');
                $table->date('start_date');
                $table->date('end_date');
                $table->enum('status', ['draft', 'approved', 'active', 'completed'])->default('draft');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                // Adiciona a chave estrangeira apenas se a tabela referenciada existir
                if (Schema::hasTable('sc_work_centers')) {
                    $table->foreign('work_center_id')->references('id')->on('sc_work_centers')->onDelete('set null');
                }
            });
        }

        // Create mrp_financial_reports table
        if (!Schema::hasTable('mrp_financial_reports')) {
            Schema::create('mrp_financial_reports', function (Blueprint $table) {
                $table->id();
                $table->string('report_number')->unique();
                $table->string('title');
                $table->date('report_date');
                $table->date('period_start');
                $table->date('period_end');
                $table->decimal('total_material_cost', 12, 2)->default(0);
                $table->decimal('total_labor_cost', 12, 2)->default(0);
                $table->decimal('total_overhead_cost', 12, 2)->default(0);
                $table->decimal('total_cost', 12, 2)->default(0);
                $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected'])->default('draft');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->date('approved_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                // Adiciona as chaves estrangeiras apenas se a tabela referenciada existir
                if (Schema::hasTable('users')) {
                    $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                    $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Verifica se cada tabela existe antes de tentar excluí-la
        // Isso é importante para que a migração possa ser revertida de forma segura
        // em ambiente de produção
        
        if (Schema::hasTable('mrp_financial_reports')) {
            Schema::dropIfExists('mrp_financial_reports');
        }
        
        if (Schema::hasTable('mrp_capacity_plans')) {
            Schema::dropIfExists('mrp_capacity_plans');
        }
        
        if (Schema::hasTable('mrp_purchase_plans')) {
            Schema::dropIfExists('mrp_purchase_plans');
        }
        
        if (Schema::hasTable('mrp_production_orders')) {
            Schema::dropIfExists('mrp_production_orders');
        }
        
        if (Schema::hasTable('mrp_production_schedules')) {
            Schema::dropIfExists('mrp_production_schedules');
        }
        
        if (Schema::hasTable('mrp_inventory_levels')) {
            Schema::dropIfExists('mrp_inventory_levels');
        }
        
        if (Schema::hasTable('mrp_bom_details')) {
            Schema::dropIfExists('mrp_bom_details');
        }
        
        if (Schema::hasTable('mrp_bom_headers')) {
            Schema::dropIfExists('mrp_bom_headers');
        }
        
        if (Schema::hasTable('mrp_demand_forecasts')) {
            Schema::dropIfExists('mrp_demand_forecasts');
        }
    }
};
