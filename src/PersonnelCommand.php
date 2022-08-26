<?php

namespace Hwacom\PersonnelInfo;

use App\Notifications\News\ArticlePublishNotification;
use App\Repositories\News\ArticleRepository;
use App\Services\News\ArticleService;
use Illuminate\Console\Command;

class PersonnelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'personnel:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'install EmployeeRepository';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $file   = app_path('Repositories\Common\EmployeeRepository.php');
        $output = file_get_contents(__DIR__ . '/stubs/Employee.stub');
        if (!file_exists($file)) {
            if ($fs = fopen($file, 'x')) {
                fwrite($fs, $output);
                fclose($fs);
                usleep(500000);
            }
        }
    }
}
