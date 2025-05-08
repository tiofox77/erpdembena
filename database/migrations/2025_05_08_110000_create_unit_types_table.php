<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnitTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unit_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('symbol', 10)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('category')->nullable()->comment('Physical quantity category (length, mass, volume, etc.)');
            $table->timestamps();
            $table->softDeletes();
        });

        // Inserir unidades padrão
        $this->seedDefaultUnitTypes();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('unit_types');
    }

    /**
     * Seed default unit types
     */
    private function seedDefaultUnitTypes()
    {
        $now = now();
        
        $unitTypes = [
            // Quantidade
            ['name' => 'Piece', 'symbol' => 'pcs', 'category' => 'quantity', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Set', 'symbol' => 'set', 'category' => 'quantity', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pair', 'symbol' => 'pair', 'category' => 'quantity', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            
            // Comprimento
            ['name' => 'Meter', 'symbol' => 'm', 'category' => 'length', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Centimeter', 'symbol' => 'cm', 'category' => 'length', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Millimeter', 'symbol' => 'mm', 'category' => 'length', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Inch', 'symbol' => 'in', 'category' => 'length', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Foot', 'symbol' => 'ft', 'category' => 'length', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            
            // Massa/Peso
            ['name' => 'Kilogram', 'symbol' => 'kg', 'category' => 'mass', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Gram', 'symbol' => 'g', 'category' => 'mass', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pound', 'symbol' => 'lb', 'category' => 'mass', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ton', 'symbol' => 't', 'category' => 'mass', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            
            // Volume
            ['name' => 'Liter', 'symbol' => 'L', 'category' => 'volume', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Milliliter', 'symbol' => 'ml', 'category' => 'volume', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Cubic Meter', 'symbol' => 'm³', 'category' => 'volume', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Gallon', 'symbol' => 'gal', 'category' => 'volume', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            
            // Área
            ['name' => 'Square Meter', 'symbol' => 'm²', 'category' => 'area', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Square Centimeter', 'symbol' => 'cm²', 'category' => 'area', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Square Foot', 'symbol' => 'ft²', 'category' => 'area', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            
            // Tempo
            ['name' => 'Hour', 'symbol' => 'h', 'category' => 'time', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Minute', 'symbol' => 'min', 'category' => 'time', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Second', 'symbol' => 's', 'category' => 'time', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Day', 'symbol' => 'd', 'category' => 'time', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            
            // Elétrica
            ['name' => 'Volt', 'symbol' => 'V', 'category' => 'electrical', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ampere', 'symbol' => 'A', 'category' => 'electrical', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Watt', 'symbol' => 'W', 'category' => 'electrical', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kilowatt', 'symbol' => 'kW', 'category' => 'electrical', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            
            // Temperatura
            ['name' => 'Celsius', 'symbol' => '°C', 'category' => 'temperature', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Fahrenheit', 'symbol' => '°F', 'category' => 'temperature', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kelvin', 'symbol' => 'K', 'category' => 'temperature', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ];
        
        DB::table('unit_types')->insert($unitTypes);
    }
}
