<?php

namespace App\Console\Commands;

use App\Models\Cattle;
use App\Models\User;
use App\Models\Vaccine;
use App\Models\Workstation;
use Illuminate\Console\Command;

class ExportTestDataCommand extends Command
{
    protected $signature = 'app:export-test-data';
    protected $description = 'Export the current test dataset to CSV files';

    public function handle()
    {
        $this->info('Starting export...');

        $this->exportToCsv('cattle_test_data.csv', ['rfid_tag', 'name', 'weight', 'registration_date', 'user_id'], Cattle::all());
        $this->exportToCsv('vaccines_test_data.csv', ['rfid_tag', 'vaccine_type', 'current_weight', 'vaccination_date', 'user_id', 'workstation_id'], Vaccine::all());
        $this->exportToCsv('vets_test_data.csv', ['id', 'name', 'email', 'vet_rfid'], User::where('is_veterinarian', true)->get());
        $this->exportToCsv('workstations_test_data.csv', ['id', 'hash', 'desc'], Workstation::all());

        $this->info('Export completed! Files are in storage/app/exports/');
    }

    private function exportToCsv($filename, $columns, $data)
    {
        $directory = storage_path('app/exports');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory . DIRECTORY_SEPARATOR . $filename;
        $file = fopen($path, 'w');

        // Add UTF-8 BOM for Excel compatibility
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header
        fputcsv($file, $columns);

        // Data
        foreach ($data as $row) {
            $line = [];
            foreach ($columns as $column) {
                $line[] = $row->{$column};
            }
            fputcsv($file, $line);
        }

        fclose($file);
        $this->line("Exported: $filename (to $path)");
    }
}
