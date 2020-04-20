<?php

namespace App\Http\Middleware;

use Closure;
use App\Customer;
use App\Account;
use App\AccountHelper;

class TransactionsCrontab
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $accounts = Account::with('transactions')->get();

        foreach ($accounts as $acount){

            $bill = new AccountHelper($acount->id);

            /// lets count already submitted docs
            $allTransactions = $acount->transactions->toArray();
            $percentage = 0; $commission = 0;

            foreach ($allTransactions as $transaction){
                if ($transaction["type"] == 'percentage') $percentage++;
                if ($transaction["type"] == 'commission') $commission++;
            }

            /// lets get amount of docks that should be for this account
            $nOfPercentageDoc = $bill->getNumberOfInvoices(false);
            $differenceInCount = $nOfPercentageDoc - $percentage;

            /// if only one document missing then we need to create regular Percentage
            if ( $differenceInCount == 1){          // make regular interest payment
                $bill->Percentage();
            } else if ( $differenceInCount > 1 ) {    // something wrong lets check
                $repared = $bill->repairAllData();
            }

            $nOfCommissionDoc = $bill->getNumberOfInvoices();
            $differenceInCount = $nOfCommissionDoc - $commission;
            if ( $differenceInCount == 1 && $repared != true ){          // make regular fee payment
                $bill->Commission();
            } else if ( $differenceInCount > 1 && $repared != true ) {    // something wrong lets check
                $bill->repairAllData();
            }
        }
        return $next($request);
    }
}
