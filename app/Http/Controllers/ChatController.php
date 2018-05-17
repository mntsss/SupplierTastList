<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ChatMessage;
use App\User;
use App\Order;
use Auth;

use App\Events\ChatMessageSent;

class ChatController extends Controller
{

    public function __construct(){
      $this->middleware('auth');
    }

    public function index($orderID){
      $chatOrder = Order::with('chatMessages.user')->find($orderID);

      return view('include/chat')->with('chatOrder', $chatOrder);
    }

    public function newMessage(Request $request){
      ChatMessage::create([
        'UserID' => Auth::user()->id,
        'OrderID' => $request->orderID,
        'ChatMessage' => $request->chatMessage
      ]);

      $message = ChatMessage::where('OrderID', $request->orderID)->orderBy('created_at', 'DESC')->first();
      broadcast(new ChatMessageSent($request->orderID))->toOthers();
      return 1;
    }

    public function getMessages($orderID){
      return json_encode(Order::with('chatMessages.user')->find($orderID));
    }
}
