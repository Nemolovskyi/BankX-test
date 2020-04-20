<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    //
    protected $guarded = [];

    public function accounts(){
        return $this->hasMany(Account::class);
    }
    public function transactions(){
        return $this->hasMany(Transaction::class);
    }

}
