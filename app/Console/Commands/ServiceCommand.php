<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to an service';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');

        $directory = app_path('Services');
        $filePath = $directory . '/' . $name . 'Service.php';

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        if (File::exists($filePath)) {
            $this->error('Service already exists!');
            return;
        }

        $stub = <<<EOT
            <?php

                namespace App\Services;

                class {$name}Service
                {
                    public function __construct()
                    {

                    }

                    public function store(): void
                    {

                    }

                    public function update(): void
                    {

                    }

                    public function delete(): void
                    {

                    }
                }

            EOT;

        File::put($filePath, $stub);

        $this->info("Service {$name} created successfully. at {$filePath}");
    }
}
