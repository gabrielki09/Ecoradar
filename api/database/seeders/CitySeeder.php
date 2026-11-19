<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('data/municipios.csv');
        $file = fopen($path, 'rb');

        if ($file === null) throw new RuntimeException("Não foi possível abrir: {$path}");

        $filter = stream_filter_append(
            $file,
            'convert.iconv.ISO-8859-1/UTF-8',
            STREAM_FILTER_READ
        );

        if ($filter === false) {
            fclose($file);
            throw new RuntimeException('Não foi possível aplicar a conversão do CSV.');
        }

        fgetcsv($file, 0, ';', '"', '');
        try {
            $batch = [];

            while (($row = fgetcsv($file, 0, ';', '"', '')) !== false) {
                if (count($row) !== 5) throw new RuntimeException('Linha inválida no CSV de cidades.');

                [$codigoTOM, $codigoIBGE, $municipioTOM, $municipioIBGE, $uf] = $row;

                $batch[] = [
                    'codigo_tom' => trim($codigoTOM),
                    'codigo_ibge' => trim($codigoIBGE),
                    'municipio_tom' => trim($municipioTOM),
                    'municipio_ibge' => trim($municipioIBGE),
                    'uf' => $uf
                ];

                if (count($batch) === 500)
                {
                    $this->saveBatch($batch);
                    $batch = [];
                }
            }

            if ($batch !== [])
            {
                $this->saveBatch($batch);
            }
        } finally {
            fclose($file);
        }
    }

    private function saveBatch(array $batch): void
    {
        DB::table('cities')->upsert(
            $batch,
            ['codigo_ibge'],
            ['codigo_tom', 'municipio_ibge', 'municipio_tom', 'uf', 'updated_at']
        );
    }
}
