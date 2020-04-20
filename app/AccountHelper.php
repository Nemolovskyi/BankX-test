<?php

namespace App;

use Illuminate\Support\Facades\DB;


class AccountHelper
{

    protected $accID;
    protected $customer_id;
    protected $balance;
    protected $margin;
    protected $started;

    protected $monthlyFee;


    /// Cache data for currend account
    function __construct($accID) {
        $this->accID = $accID;
        $acc = DB::table('accounts')->where('id', '=', $accID)->get()->toArray();
        $this->customer_id = $acc[0]->customer_id;
        $this->balance = $acc[0]->balance;
        $this->margin = $acc[0]->margin;
        $this->started = $acc[0]->started;

        $this->countFee();
    }

    /// calculate fee for current account including interest for all period
    protected function countFee(){

        // get capitalisation and add to balance
        $interest = DB::table('transactions')
            ->where('billed', '<', now())
            ->where('account_id','=', $this->accID)
            ->sum('sum');

        $balance = $this->balance + $interest;

        if ($balance < 1000){         // allying %5 fee
            $fee = $balance * 0.05;
        } else if ($balance < 10000){ // allying %6 fee
            $fee = $balance * 0.07;
        } else {                            // allying %7 fee
            $fee = $balance * 0.07;
        }
        if ($fee < 50 ) $fee = 50;
        if ($fee > 5000 ) $fee = 5000;

        $this->monthlyFee = $fee / 12 * -1;
    }

    /// Public method to pay Percentage for account
    public function Percentage(){
        $closestDate = $this->getPercentagePayDate();
        $sum = ($this->balance * ($this->margin / 100 )) / 12;
        return $this->writePercentage($sum, $closestDate);
    }

    /// Public method to pay Commission for account
    public function Commission(){
        $closestDate = date('Y-m') . '-01';
        return $this->writeCommission( $closestDate);
    }




    /// if there is more then one payment doc missing we need to repair all data
    public function repairAllData(){
        //$fees = $this->getNumberOfInvoices(false);
        $invoices = $this->getNumberOfInvoices();
        $incrementDate = $this->started;

        for ($i=1; $i<=$invoices; $i++) {

            $closestDate = $this->getIncrementedDatesSet($incrementDate);

            $interest = DB::table('transactions')
                ->where('billed', '<', $closestDate['percentage'])
                ->where('account_id','=', $this->accID)
                ->sum('sum');

            $sum = (($this->balance + $interest )* ($this->margin / 100 )) / 12;
            if ( $closestDate['percentage'] ) $pStat = $this->writePercentage($sum , $closestDate['percentage']);
            if ( $closestDate['commission'] ) $cStat = $this->writeCommission($closestDate['commission']);

            $incrementDate = $closestDate['commission'];
        }
        return true;
    }


    /// generates date for next payment
    protected function getIncrementedDatesSet($date){

        $from = explode('-', date('Y-m-d', strtotime($date)));  // 2000 01 03

        if (intval($from[1]) == 12){
            $percentageDate = ( intval($from[0]) + 1 ) . '-01';
            $commissionDate = ( intval($from[0]) + 1 ) . '-01-01';
            $month = 1;
        } else {
            $percentageDate = $from[0] . '-' . ( intval($from[1]) + 1 );
            $commissionDate = $from[0] . '-' . ( intval($from[1]) + 1 ) . '-01';
            $month = intval($from[1]) + 1;
        }

        /// Max day in month correction
        $day = intval ( date ('d', strtotime ($this->started)));
        if ( $day > 30 ) {
            $percentageDate .= '-' . $this->daysInMonth($commissionDate);
        } else if ( $month == 2 && $day > 29 ) {   /// February max days correction
            $percentageDate .= '-' . $this->daysInMonth($commissionDate);
        } else {
            $percentageDate .= '-' . $day;
        }
        return array('percentage'=>$this->validateDate($percentageDate), 'commission'=>$this->validateDate($commissionDate));
    }


    // checking time frame for date: need to be not lower then account started and more then now
    protected function validateDate($date){
        // lets check if there a time to pay at all, need more days to pass then in the smallest month
        $daysFromOpening = ( strtotime(date('Y-m-d', strtotime($date))) - strtotime( date('Y-m-d', strtotime($this->started)))) / 86400;
        $daysFromNow = ( strtotime( date('Y-m-d')) - strtotime(date('Y-m-d', strtotime($date))) ) / 86400;
        if ( $daysFromOpening < 0 ) return false;
        if ( $daysFromNow < 0 ) return false;

        return date('Y-m-d', strtotime($date));
    }


    // writing Percentage to DB
    protected function writePercentage($sum, $billed){

        $checkLatestInterestPayment = DB::table('transactions')
            ->where('billed', '=', $billed)
            ->where('account_id','=', $this->accID)
            ->where('type','=', 'percentage')
            ->get()->toArray();

        if (empty($checkLatestInterestPayment)){
            $payInterest = new Transaction();

            $payInterest->customer_id = $this->customer_id;
            $payInterest->account_id = $this->accID;
            $payInterest->type = 'percentage';
            $payInterest->sum = round($sum,2);
            $payInterest->billed = $billed;

            $payInterest->save();
        } else {
            return false;
        }
        return $payInterest;
    }
    // writing Commission to DB
    protected function writeCommission( $date){

        $billed = date('Y-m', strtotime($date)) . '-01';
        $daysFromStart = ( strtotime(date('Y-m-d', strtotime($date))) - strtotime( date('Y-m-d', strtotime($this->started)))) / 86400;
        $daysInMonthOfStart = $this->daysInMonth($this->started);

        // this is not first not full payment
        if ($daysFromStart < $daysInMonthOfStart){
            $started = intval(date('d', strtotime($this->started)));
            $fee = $this->monthlyFee / ($daysInMonthOfStart - $started);
        } else {
            $fee = $this->monthlyFee;
        }

        $checkLatestFeePayment = DB::table('transactions')
            ->where('billed', '=', $billed)
            ->where('account_id','=', $this->accID)
            ->where('type','=', 'commission')
            ->get()->toArray();

        if (empty($checkLatestFeePayment)){
            $payFee = new Transaction();

            $payFee->customer_id = $this->customer_id;
            $payFee->account_id = $this->accID;
            $payFee->type = 'commission';
            $payFee->sum = round($fee,2);
            $payFee->billed = $billed;

            $payFee->save();
        } else {
            return false;
        }
        return $payFee;
    }


    // generates correct date for current invoice
    protected function getPercentagePayDate(){

        // lets check if there a time to pay at all, need more days to pass then in the smallest month
        $daysFromOpening = ( strtotime( date('Y-m-d')) - strtotime(date('Y-m-d', strtotime($this->started))) ) / 86400;
        if ( $daysFromOpening < 28 ) return false;

        $from = explode('-', date('Y-m-d', strtotime($this->started)));  // 2000 01 03

        // By default generate date for last invoice
        $now = explode('-', date('Y-m-d')); /// 2000 02 02

        // Composing requested date with change of year
        $payThisMonth = intval($now[2]) - intval($from[2]);
        if ($payThisMonth >= 0) {
            $date = $now[0] . '-' . $now[1];
        } else {
            if ( intval($now[1]) == 1) {
                $date = ( intval($now[0]) - 1 ) . '-12';
            } else {
                $date = $now[0] . '-' . ( intval($now[1]) -1 );
            }
        }

        /// Max day in month correction
        if ( intval($from[2]) > 30 ) {
            $date .= '-' . $this->daysInMonth($date);
        } else if ( intval($now[1]) == 2 && intval($from[2]) > 29 ) {   /// February max days correction
            $date .= '-' . $this->daysInMonth($date);
        } else {
            $date .= '-' . $from[2];
        }

        return date('Y-m-d', strtotime($date));
    }


    // counts number of invoices from creation date till now
    function getNumberOfInvoices ($fullMonthCounts = true){

        $from = explode('-', date('Y-m-d',strtotime($this->started)));
        $now = explode('-', date('Y-m-d'));

        $return = ( ( intval($now[0]) - intval($from[0]) ) * 12 ) + intval($now[1]) - intval($from[1]);

        $restOfMonth = intval($now[2]) - intval($from[2]);
        if ( $fullMonthCounts  == false && $restOfMonth < 0 ) {
            $return --;
        }
        return  $return;
    }


    /// returns number of days in given month
    protected function daysInMonth($date){
        return cal_days_in_month(CAL_GREGORIAN, date('m',strtotime($date)), date('Y',strtotime($date)));
    }
}
