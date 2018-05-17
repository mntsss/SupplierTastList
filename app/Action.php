<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\OrderActions;

class Action extends Model
{
  use OrderActions;
  
    protected $fillable = ['orderId', 'oldStatus', 'newStatus', 'user'];

    public function stringOutput(){
      return $this->Print($this);
    }
}
