<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\School;
use App\Models\Student;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Scholarship;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
      
        // Call RoleSeeder first
        $this->call([
            RoleSeeder::class,
        ]);

        // Now seed Schools, Students, Fees, Invoices, Payments, Scholarships

    }

    protected function disableForeignKeyChecks()
    {
        $driver = DB::getDriverName();

        switch ($driver) {
            case 'mysql':
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                break;
            case 'sqlite':
                DB::statement('PRAGMA foreign_keys = OFF;');
                break;
        }
    }

    protected function enableForeignKeyChecks()
    {
        $driver = DB::getDriverName();

        switch ($driver) {
            case 'mysql':
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                break;
            case 'sqlite':
                DB::statement('PRAGMA foreign_keys = ON;');
                break;
        }
    }
}
