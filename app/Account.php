<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{

    protected $guarded = [];


    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    public function transactions(){
        return $this->hasMany(Transaction::class);
    }
}
