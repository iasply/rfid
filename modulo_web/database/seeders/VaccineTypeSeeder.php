<?php

namespace Database\Seeders;

use App\Models\VaccineType;
use Illuminate\Database\Seeder;

class VaccineTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name'         => 'Febre Aftosa',
                'description'  => 'Campanha obrigatória MAPA — aplicação em maio e novembro. Dose de 2 mL subcutânea, cobertura mínima de 90%.',
                'interval_days' => 180,
                'season_months' => [4, 5, 10, 11],
            ],
            [
                'name'         => 'Brucelose',
                'description'  => 'Obrigatória para fêmeas jovens de 3–8 meses. Prazo: 31 de maio (1º semestre) e até novembro (2º semestre). Vacinas B19 ou RB51.',
                'interval_days' => 365,
                'season_months' => [1, 2, 3, 4, 5, 10, 11],
            ],
            [
                'name'         => 'Raiva',
                'description'  => 'Regiões endêmicas (morcegos hematófagos). Pico de risco na época das chuvas (novembro–abril). Revacinação anual.',
                'interval_days' => 365,
                'season_months' => [1, 2, 3, 4, 11, 12],
            ],
            [
                'name'         => 'Clostridiose',
                'description'  => 'Polivalente — cobre Carbúnculo Sintomático, Gangrena Gasosa e Botulismo. Inicial com reforço 30 dias após; revacinação anual.',
                'interval_days' => 365,
                'season_months' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            ],
            [
                'name'         => 'Carbúnculo Sintomático',
                'description'  => 'A cada 6 meses em bovinos até 2 anos, depois anual. Comum em bovinos jovens de 3 meses a 2 anos.',
                'interval_days' => 180,
                'season_months' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            ],
            [
                'name'         => 'Leptospirose',
                'description'  => 'A cada 6 meses para todo o rebanho. Protocolo inicial: 1ª dose + reforço após 4 semanas. Associada a abortos e infertilidade.',
                'interval_days' => 180,
                'season_months' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            ],
            [
                'name'         => 'IBR/BVD',
                'description'  => 'Rhinotraqueíte Infecciosa Bovina e Diarreia Viral Bovina. Aplicar 60–30 dias antes da estação reprodutiva (março e setembro).',
                'interval_days' => 365,
                'season_months' => [2, 3, 4, 8, 9, 10],
            ],
            [
                'name'         => 'Verminose',
                'description'  => 'Esquema 5-7-9: maio, julho e setembro (Sul/Sudeste) ou 5-8-11 (Centro-Oeste). Animais do desmame até 24 meses.',
                'interval_days' => 120,
                'season_months' => [4, 5, 6, 7, 8, 9, 10, 11],
            ],
            [
                'name'         => 'Tristeza Parasitária',
                'description'  => 'Causada por Babesia bovis, B. bigemina e Anaplasma marginale. Maior risco na estação seca com alta infestação de carrapatos (junho–setembro).',
                'interval_days' => 180,
                'season_months' => [4, 5, 6, 7, 8, 9, 10],
            ],
            [
                'name'         => 'Botulismo',
                'description'  => 'Revacinação anual. Maior risco na estação seca (junho–outubro) quando os animais ingerem carcaças ou ossos contaminados.',
                'interval_days' => 365,
                'season_months' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            ],
        ];

        foreach ($types as $type) {
            VaccineType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
