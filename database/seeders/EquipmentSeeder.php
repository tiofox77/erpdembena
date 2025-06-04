<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Equipment;
use App\Models\Line;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Lines
        $lines = [
            ['name' => 'Production Line 1', 'description' => 'Main production line'],
            ['name' => 'Production Line 2', 'description' => 'Secondary production line'],
            ['name' => 'Packaging Line', 'description' => 'Final packaging line'],
            ['name' => 'Assembly Line', 'description' => 'Product assembly line'],
        ];

        foreach ($lines as $line) {
            Line::create($line);
        }

        // Create Areas
        $areas = [
            ['name' => 'Plant Area A', 'description' => 'North Plant Facility'],
            ['name' => 'Plant Area B', 'description' => 'South Plant Facility'],
            ['name' => 'Warehouse', 'description' => 'Storage facility'],
            ['name' => 'Maintenance Shop', 'description' => 'Equipment repair area'],
        ];

        foreach ($areas as $area) {
            Area::create($area);
        }

        // Create Equipment
        $equipment = [
            [
                'name' => 'Conveyor Belt System',
                'serial_number' => 'CBS-2023-001',
                'line_id' => 1,
                'area_id' => 1,
                'status' => 'operational',
                'purchase_date' => '2023-01-15',
                'last_maintenance' => '2023-06-10',
                'next_maintenance' => '2023-12-10',
                'notes' => 'Main conveyor system for production line 1',
            ],
            [
                'name' => 'Packaging Machine',
                'serial_number' => 'PKG-2022-102',
                'line_id' => 3,
                'area_id' => 2,
                'status' => 'operational',
                'purchase_date' => '2022-05-20',
                'last_maintenance' => '2023-05-05',
                'next_maintenance' => '2023-11-05',
                'notes' => 'Automated packaging system',
            ],
            [
                'name' => 'Industrial Robot',
                'serial_number' => 'ROB-2021-053',
                'line_id' => 4,
                'area_id' => 1,
                'status' => 'maintenance',
                'purchase_date' => '2021-10-10',
                'last_maintenance' => '2023-07-01',
                'next_maintenance' => '2023-10-01',
                'notes' => 'Assembly robot with maintenance issues',
            ],
            [
                'name' => 'Cooling System',
                'serial_number' => 'CS-2020-321',
                'line_id' => 2,
                'area_id' => 2,
                'status' => 'out_of_service',
                'purchase_date' => '2020-03-25',
                'last_maintenance' => '2023-04-15',
                'next_maintenance' => '2023-10-15',
                'notes' => 'Needs major repair',
            ],
        ];

        foreach ($equipment as $item) {
            Equipment::create($item);
        }
    }
}
