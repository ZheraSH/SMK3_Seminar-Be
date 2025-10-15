<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BaseEloquentInterfaceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:base-eloquent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to make some base eloquent for interface in solid principle';

    /**
     * The files and their content to be created.
     *
     * @var array
     */
    protected $files = [
        'BaseInterface.php' => "<?php

namespace App\Contracts\Interfaces\Eloquent;

interface BaseInterface extends GetInterface, StoreInterface, ShowInterface, UpdateInterface, DeleteInterface
{
    //
}
",
        'GetInterface.php' => "<?php

namespace App\Contracts\Interfaces\Eloquent;

interface GetInterface
{
    /**
     * Handle the Get all data event from models.
     *
     * @return mixed
     */

    public function get(): mixed;
}
",
        'StoreInterface.php' => "<?php

namespace App\Contracts\Interfaces\Eloquent;

interface StoreInterface
{
    /**
     * Handle store data event to models.
     *
     * @param array \$data
     *
     * @return mixed
     */

    public function store(array \$data): mixed;
}
",
        'UpdateInterface.php' => "<?php

namespace App\Contracts\Interfaces\Eloquent;

interface UpdateInterface
{
    /**
     * Handle show method and update data instantly from models.
     *
     * @param mixed \$id
     * @param array \$data
     *
     * @return mixed
     */

    public function update(mixed \$id, array \$data): mixed;
}
",
        'DeleteInterface.php' => "<?php

namespace App\Contracts\Interfaces\Eloquent;

interface DeleteInterface
{
    /**
     * Handle show method and delete data instantly from models.
     *
     * @param mixed \$id
     *
     * @return mixed
     */

    public function delete(mixed \$id): mixed;
}
",
        'ShowInterface.php' => "<?php

namespace App\Contracts\Interfaces\Eloquent;

interface ShowInterface
{
    /**
     * Handle get the specified data by id from models.
     *
     * @param mixed \$id
     *
     * @return mixed
     */

    public function show(mixed \$id): mixed;
}
",
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $directory = app_path('Contracts/Interfaces/Eloquent');

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
            $this->info('Directory created successfully.');
        }

        foreach ($this->files as $filename => $content) {
            $filePath = $directory . '/' . $filename;
            if (File::exists($filePath)) {
                $this->error("File [$filePath] already exists!");
                continue;
            }
            File::put($filePath, $content);
            $absolutePath = realpath($filePath);
            $this->info("Eloquent [{$absolutePath}] created successfully.");
        }
    }
}
