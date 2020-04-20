<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Customer;
use App\Account;
use App\Transaction;
use Faker\Generator as Faker;

$factory->define(Customer::class, function (Faker $faker) {
    return [
        'identification_number' => $faker->bankAccountNumber,
        'name' => $faker->firstName,
        'lastname' => $faker->lastName,
        'gender' => $faker->randomElement(['man' ,'woman']),
        'date_of_birth' => $faker->dateTimeBetween('1978-01-01', '2000-12-31')
            ->format('Y-m-d'),
    ];
});

$factory->define(Account::class, function (Faker $faker) {
    return [
        'customer_id' => factory(Customer::class),
        'account_number' => $faker->bankAccountNumber,
        'balance' => $faker->numberBetween(30,500)*100,
        'margin' => $faker->randomFloat(2,7,12),
        'started' => $faker->dateTimeBetween('2015-01-01', now())->format('Y-m-d')
    ];
});

$factory->define(Transaction::class, function (Faker $faker) {
    return [
        'customer_id' => factory(Customer::class),
        'account_id' => factory(Account::class),
        'type' => $faker->randomElement(['commission' ,'percentage']),
        'sum' => $faker->numberBetween(30,500)*100,
        'billed' => $faker->dateTimeBetween('2015-01-01', now())->format('Y-m-d')
    ];
});


