<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bodega;
use App\Models\Maquina;
use App\Models\MaquinaSlot;

class MaquinaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar datos previos
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MaquinaSlot::truncate();
        Maquina::truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $bodegaCcu = Bodega::where('nombre', 'Bodega CCU')->first();
        $bodegaCocaCola = Bodega::where('nombre', 'Bodega Coca-Cola')->first();

        if (!$bodegaCcu) {
            $bodegaCcu = Bodega::create(['nombre' => 'Bodega CCU']);
        }
        if (!$bodegaCocaCola) {
            $bodegaCocaCola = Bodega::create(['nombre' => 'Bodega Coca-Cola']);
        }

        // --- MÁQUINA CCU 1 ---
        $maquinaCcu1 = Maquina::create([
            'nombre' => 'Máquina CCU 1',
            'bodega_id' => $bodegaCcu->id,
        ]);

        $this->createSlots($maquinaCcu1->id, 1, 1, 8, 4); // Piso 1: 3L (8 sabores x 4 botellas)
        $this->createSlots($maquinaCcu1->id, 1, 2, 5, 5); // Piso 2: 2L (5 sabores x 5 botellas)
        $this->createSlots($maquinaCcu1->id, 1, 3, 8, 6); // Piso 3: 600ml (8 sabores x 6 botellas)
        $this->createSlots($maquinaCcu1->id, 1, 4, 6, 6); // Piso 4: Cachantun 1.6L (6 sabores x 6 botellas)
        $this->createSlots($maquinaCcu1->id, 1, 5, 6, 5); // Piso 5: Cachantun / Manantial (6 sabores x 5 botellas)
        $this->createSlots($maquinaCcu1->id, 1, 6, 3, 5); // Piso 6: 1.25L (3 sabores x 5 botellas)
        $this->createSlots($maquinaCcu1->id, 1, 7, 3, 5); // Piso 7: Gatorade (3 sabores x 5 botellas)

        // --- MÁQUINA CCU 2 ---
        $maquinaCcu2 = Maquina::create([
            'nombre' => 'Máquina CCU 2',
            'bodega_id' => $bodegaCcu->id,
        ]);

        $this->createSlots($maquinaCcu2->id, 1, 1, 7, 6); // Piso 1: Cachantun sabor 600ml
        $this->createSlots($maquinaCcu2->id, 1, 2, 6, 6); // Piso 2: Cachantun sabor 1.6L
        $this->createSlots($maquinaCcu2->id, 1, 3, 6, 5); // Piso 3: Jugos Watts 1.5L
        $this->createSlots($maquinaCcu2->id, 1, 4, 5, 4); // Piso 4: Bebidas retornables 3L/2.5L

        // --- MÁQUINA COCA-COLA ---
        $maquinaCoca = Maquina::create([
            'nombre' => 'Máquina Coca-Cola (3 Secciones)',
            'bodega_id' => $bodegaCocaCola->id,
        ]);

        // Secciones 1, 2 y 3
        for ($seccion = 1; $seccion <= 3; $seccion++) {
            // Piso 1
            $this->createSpecificSlots($maquinaCoca->id, $seccion, 1, [
                5, // Express 237ml
                9, // 591ml
                6, // Zero 591ml
                6, // Light 591ml
            ]);

            // Piso 2
            $this->createSpecificSlots($maquinaCoca->id, $seccion, 2, [
                5, // 1L Vidrio
                5, // 1L Vidrio
                6, // 1.5L
                6, // 1L
                6, // 1.5L Light
                6, // 1L Light
            ]);

            // Piso 3
            $this->createSpecificSlots($maquinaCoca->id, $seccion, 3, [
                6, // 3L Ret
                6, // 2L Ret
                6, // 2L Zero
                6, // 2L Des
                6, // 1.5L Zero
                6, // 1L Des
            ]);

            // Piso 4
            $this->createSpecificSlots($maquinaCoca->id, $seccion, 4, [
                4, // 3L Ret
                4, // 3L Zero Ret
                5, // 3L Des
                5, // 3L Zero Des
                5, // 3L Light Des
            ]);
        }
    }

    /**
     * Crea slots uniformes para un piso.
     */
    private function createSlots($maquinaId, $seccion, $piso, $cantidadSabores, $capacidadPorSabor)
    {
        for ($pos = 1; $pos <= $cantidadSabores; $pos++) {
            MaquinaSlot::create([
                'maquina_id' => $maquinaId,
                'seccion' => $seccion,
                'piso' => $piso,
                'posicion' => $pos,
                'capacidad_maxima' => $capacidadPorSabor,
                'cantidad_actual' => 0,
            ]);
        }
    }

    /**
     * Crea slots específicos con distintas capacidades para un piso.
     */
    private function createSpecificSlots($maquinaId, $seccion, $piso, $capacidades)
    {
        foreach ($capacidades as $index => $capacidad) {
            MaquinaSlot::create([
                'maquina_id' => $maquinaId,
                'seccion' => $seccion,
                'piso' => $piso,
                'posicion' => $index + 1,
                'capacidad_maxima' => $capacidad,
                'cantidad_actual' => 0,
            ]);
        }
    }
}
