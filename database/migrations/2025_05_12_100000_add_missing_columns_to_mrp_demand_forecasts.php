<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            // Check if table exists
            if (Schema::hasTable('mrp_demand_forecasts')) {
                // Check if columns need to be added or modified
                if (!Schema::hasColumn('mrp_demand_forecasts', 'forecast_date')) {
                    Schema::table('mrp_demand_forecasts', function (Blueprint $table) {
                        // Add forecast_date column
                        $table->date('forecast_date')->nullable()->after('product_id')->comment('Data da previsão');
                    });
                    
                    // If year and month columns exist, migrate data to forecast_date
                    if (Schema::hasColumn('mrp_demand_forecasts', 'year') && 
                        Schema::hasColumn('mrp_demand_forecasts', 'month')) {
                        // Update forecast_date based on year and month
                        DB::statement("UPDATE mrp_demand_forecasts SET forecast_date = CONCAT(year, '-', LPAD(month, 2, '0'), '-01')");
                    }
                }
                
                if (!Schema::hasColumn('mrp_demand_forecasts', 'forecast_quantity')) {
                    Schema::table('mrp_demand_forecasts', function (Blueprint $table) {
                        // Add forecast_quantity column
                        $table->integer('forecast_quantity')->nullable()->after('forecast_date')->comment('Quantidade prevista');
                    });
                    
                    // If quantity column exists, migrate data to forecast_quantity
                    if (Schema::hasColumn('mrp_demand_forecasts', 'quantity')) {
                        DB::statement("UPDATE mrp_demand_forecasts SET forecast_quantity = quantity");
                    }
                }
                
                if (!Schema::hasColumn('mrp_demand_forecasts', 'confidence_level')) {
                    Schema::table('mrp_demand_forecasts', function (Blueprint $table) {
                        // Add confidence_level column
                        $table->decimal('confidence_level', 5, 2)->nullable()->after('forecast_quantity')->comment('Nível de confiança (0-100%)');
                    });
                }
                
                if (!Schema::hasColumn('mrp_demand_forecasts', 'forecast_type')) {
                    Schema::table('mrp_demand_forecasts', function (Blueprint $table) {
                        // Add forecast_type column
                        $table->string('forecast_type', 20)->default('manual')->after('confidence_level')->comment('Tipo de previsão');
                    });
                }
                
                if (!Schema::hasColumn('mrp_demand_forecasts', 'created_by')) {
                    Schema::table('mrp_demand_forecasts', function (Blueprint $table) {
                        // Add created_by column
                        $table->unsignedBigInteger('created_by')->nullable()->after('notes');
                        // Add foreign key
                        $table->foreign('created_by')
                              ->references('id')
                              ->on('users')
                              ->onDelete('set null');
                    });
                }
                
                if (!Schema::hasColumn('mrp_demand_forecasts', 'updated_by')) {
                    Schema::table('mrp_demand_forecasts', function (Blueprint $table) {
                        // Add updated_by column
                        $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                        // Add foreign key
                        $table->foreign('updated_by')
                              ->references('id')
                              ->on('users')
                              ->onDelete('set null');
                    });
                }
                
                // Add foreign key for product_id if it doesn't exist
                $foreignKeys = DB::select(
                    "SELECT * FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'mrp_demand_forecasts' 
                    AND REFERENCED_TABLE_NAME = 'sc_products'"
                );
                
                if (empty($foreignKeys)) {
                    Schema::table('mrp_demand_forecasts', function (Blueprint $table) {
                        $table->foreign('product_id')
                              ->references('id')
                              ->on('sc_products')
                              ->onDelete('cascade');
                    });
                }
            }
        } finally {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // This is a fix migration, no reversal needed
    }
};
