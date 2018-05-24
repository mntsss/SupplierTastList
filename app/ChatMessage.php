<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable =['OrderID', 'UserID', 'ChatMessage', 'IsRead'];
    protected $primaryKey = 'ChatMessageID';

    public function order(){
      return $this->belongsTo('App\Order', 'OrderID');
    }

    public function user(){
      return $this->hasOne('App\User', 'id', 'UserID');
    }
}
