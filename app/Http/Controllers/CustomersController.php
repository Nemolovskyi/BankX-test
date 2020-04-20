<?php

namespace App\Http\Controllers;

use App\Account;
use App\Customer;
use App\Transaction;
use Illuminate\Support\Facades\DB;

class CustomersController extends Controller
{
    // list of all clients with tere accounts
    public function listAll(){

        $customers = Customer::with('accounts')->with('transactions')
            ->simplePaginate(4);

        $interest = DB::table('transactions')
            ->selectRaw('sum(sum) as interest')
            ->groupBy('account_id')
            ->get();

        $increment = 0;
        foreach ($customers as &$costumer){
            $age = (date('Y') - date('Y',strtotime($costumer->date_of_birth)));
            $costumer['age'] = $age;

            foreach ($costumer->accounts as &$acc) {
                $acc['interest'] = $interest[$increment]->interest;
                $increment++;
            }
        }

        return view('customers', compact('customers'));
    }

    // all transactions for given account
    public function account($accID){

        $account = Account::find($accID);
        $transactions = Transaction::where('account_id',$accID)->simplePaginate(10);
        $interest = Transaction::where('account_id',$accID)->sum('sum');

        return view('account', compact('account', 'transactions', 'interest'));
    }

    // statistics TODO
    public function statistic(){

        $statistic = 'TODO';

        return view('statistic', compact('statistic'));

    }

}
