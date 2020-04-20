<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', 'customers');

Route::get('customers', 'CustomersController@listAll');
Route::get('customers/{account}', 'CustomersController@account');
Route::get('statistic', 'CustomersController@statistic');
