<?php

namespace Hwacom\PersonnelInfo;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

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
        //檢查資料夾是否存在 (不存在則建立資料夾)
        (new Filesystem())->ensureDirectoryExists(base_path('app/Repositories/Common'));
        (new Filesystem())->ensureDirectoryExists(base_path('app/Models/HR'));

        $this->info("準備產生EmployeeRepository(HRepository)");
        //建立EmployeeRepository(HRepository)
        $file   = app_path('Repositories\Common\EmployeeRepository.php');
        $output = file_get_contents(__DIR__ . '/stubs/Employee.stub');
        if (!file_exists($file)) {
            if ($fs = fopen($file, 'x')) {
                fwrite($fs, $output);
                fclose($fs);
                usleep(500000);
            }
        } else {
            $this->error('檔案已存在!');
        }

        $this->info("準備產生Employee Model");
        //建立EmployeeModel
        $model  = app_path('Models\HR\Employee.php');
        $output = file_get_contents(__DIR__ . '/stubs/EmployeeModel.stub');
        if (!file_exists($model)) {
            if ($fs = fopen($model, 'x')) {
                fwrite($fs, $output);
                fclose($fs);
                usleep(500000);
            }
        } else {
            $this->error('檔案已存在!');
        }

        if ($this->confirm("需要產生Update Users Table的Migration嗎? [Yes|no]", "Yes")) {
            $this->info("準備產生User Update Migration");
            //建立UpdateUserMigration
            $migration = base_path('database\migrations\2022_08_31_000000_update_users_table.php');
            $output    = file_get_contents(__DIR__ . '/stubs/UpdateUserMigration.stub');
            if (!file_exists($migration)) {
                if ($fs = fopen($migration, 'x')) {
                    fwrite($fs, $output);
                    fclose($fs);
                    usleep(500000);
                }
            } else {
                $this->error('檔案已存在!');
            }
        }
        $this->info("------完成------");
    }
}
