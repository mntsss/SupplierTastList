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
      broadcast(new ChatMessageSent(Order::find($request->orderID)))->toOthers();
      return 1;
    }

    public function getMessages($orderID){
        Order::find($orderID)->chatMessages()->where('IsRead', false)->where('UserID', '!=', Auth::user()->id)->update(['IsRead' => true]);
      return json_encode(Order::with('chatMessages.user')->find($orderID));
    }

    public function getUnreadChats(){
      $chatsWithUnreadMesseges = [];
      if(Auth::user()->role == "Vadybininkas"){
        $orders = Order::where(function($q){
          $q->where('status', 'active')->orWhere('status', 'return');
        })->where('whoAdded', Auth::user()->name)->with('chatMessages')->get();
        foreach($orders as $order){
          $messCount = $order->chatMessages->where('UserID', '!=', Auth::user()->id)->where('IsRead', false)->count();
          if($messCount > 0){
            array_push($chatsWithUnreadMesseges, ['orderID' => $order->id, 'messagesCount' => $messCount, 'orderName' => $order->name]);
          }
        }
      }
      else{
        $orders = Order::where(function($q){
          $q->where('status', 'active')->orWhere('status', 'return');
        })->with('chatMessages')->get();
        foreach($orders as $order){
          $messCount = $order->chatMessages->where('UserID', '!=', Auth::user()->id)->where('IsRead', false)->count();
          if($messCount > 0){
            array_push($chatsWithUnreadMesseges, ['orderID' => $order->id, 'messagesCount' => $messCount, 'orderName' => $order->name]);
          }
        }
      }
      return json_encode($chatsWithUnreadMesseges);
    }
}
