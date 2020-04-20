<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{

    protected $guarded = [];

    public function account(){
        return $this->belongsTo(Account::class);
    }
    public function customer(){
        return $this->belongsTo(Customer::class);
    }
}
