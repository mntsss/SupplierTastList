<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['name', 'code', 'address', 'code', 'phone', 'capacity', 'cooX', 'cooY', 'make', 'model', 'year', 'price', 'fuel', 'power', 'vin', 'carType', 'chassisNr', 'description', 'type', 'vehicleNr', 'image', 'important', 'timeLimit', 'status', 'whoAdded', 'supplier'];
    public function actions()
    {
        return $this->hasMany('App\Action', 'orderId');
    }

    public function chatMessages(){
      return $this->hasMany('App\ChatMessage', 'OrderID');
    }
}
