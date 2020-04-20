<?php

use Illuminate\Database\Seeder;
use \App\Account;

class AccountTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        /// отвечает за количество сгенерированных клиентов
        $maxCustomers = 4;

        factory(Account::class, $maxCustomers)->create();

        for ($i = 1; $i <= $maxCustomers/2; $i++) {
            factory(Account::class)->create([
                'customer_id' => rand(1,$maxCustomers),
            ]);
        }
    }
}

