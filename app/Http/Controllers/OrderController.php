<?php

namespace App\Http\Controllers;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;
use Carbon\Carbon;
use App\Order;
use App\Vehicle;
use App\User;
use App\Action;
use App\Traits\OrderActions;
class OrderController extends Controller
{
     use OrderActions;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function activeOrders(){
      $active_orders = Order::where('timeLimit', '!=', "")->where('timeLimit', '!=', null)->where('status', 'active')->get();

      foreach ($active_orders as $o) {
        if(Carbon::parse($o->timeLimit)<Carbon::now()){
          Action::create(['orderId' => $o->id, 'oldStatus' => $o->status, 'newStatus' => 'deleted', 'user' => 'Sistema']);
          Order::find($o->id)->update(['status' => 'deleted']);

        }
      }

      $orders = Order::where(function($q){
        if(Auth::user()->role == "Vadybininkas")
          $q->where('status', 'active')->orWhere('status','return')->orWhere('status', 'found');
        else {
          $q->where('status', 'active')->orWhere('status','return');
        }
      })->orderBy('important', 'DESC')->orderBy('timeLimit', 'ASC')->paginate(15);

      $param['orders'] = $orders;
      return view('active')->with('param', $param);
    }

    public function deliveredOrders(Request $request)
    {
      $orders = Order::where('status', 'delivered');
      if($request["query"] != "")
        $orders = $orders->where(function($q) use ($request){
          $q->where('make', 'LIKE', '%'.$request["query"].'%')
        ->orWhere('model', 'LIKE', '%'.$request["query"].'%')
        ->orWhere('name', 'LIKE', '%'.$request["query"].'%')
        ->orWhere('code', 'LIKE', '%'.$request["query"].'%')
        ->orWhere('description', 'LIKE', '%'.$request["query"].'%')
        ->orWhere('vin', 'LIKE', '%'.$request["query"].'%');
      });

      if($request["from"] != "")
        $orders = $orders->where('updated_at', '>=', $request["from"]);
      if($request["til"] != "")
        $orders = $orders->where('updated_at', '<=', $request["til"]);

      $param['orders'] = $orders->orderBy('updated_at', 'DESC')->paginate(15);
      $param['lastSearch'] = ['query' => $request["query"], 'from' => $request["from"], 'til' => $request["til"]];
      return view('delivered')->with('param', $param);
    }

    public function deletedOrders(Request $request){
      $orders = Order::where('status', 'deleted');
      if($request["query"] != "")
        $orders = $orders->where(function($q) use ($request){
          $q->where('make', 'LIKE', '%'.$request["query"].'%')
        ->orWhere('model', 'LIKE', '%'.$request["query"].'%')
        ->orWhere('name', 'LIKE', '%'.$request["query"].'%')
        ->orWhere('code', 'LIKE', '%'.$request["query"].'%')
        ->orWhere('description', 'LIKE', '%'.$request["query"].'%')
        ->orWhere('vin', 'LIKE', '%'.$request["query"].'%');
      });

      if($request["from"] != "")
        $orders = $orders->where('updated_at', '>=', $request["from"]);
      if($request["til"] != "")
        $orders = $orders->where('updated_at', '<=', $request["til"]);

      $param['orders'] = $orders->orderBy('updated_at', 'DESC')->paginate(15);
      $param['lastSearch'] = ['query' => $request["query"], 'from' => $request["from"], 'til' => $request["til"]];
      return view('deleted')->with('param', $param);
    }
    public function returnedOrders(Request $request){
      $orders = Order::where('status', 'returned');
      if($request["query"] != "")
        $orders = $orders->where(function($q) use ($request){
          $q->where('make', 'LIKE', '%'.$request["query"].'%')
        ->orWhere('model', 'LIKE', '%'.$request["query"].'%')
        ->orWhere('name', 'LIKE', '%'.$request["query"].'%')
        ->orWhere('code', 'LIKE', '%'.$request["query"].'%')
        ->orWhere('description', 'LIKE', '%'.$request["query"].'%')
        ->orWhere('vin', 'LIKE', '%'.$request["query"].'%');
      });

      if($request["from"] != "")
        $orders = $orders->where('updated_at', '>=', $request["from"]);
      if($request["til"] != "")
        $orders = $orders->where('updated_at', '<=', $request["til"]);

      $param['orders'] = $orders->orderBy('updated_at', 'DESC')->paginate(15);
      $param['lastSearch'] = ['query' => $request["query"], 'from' => $request["from"], 'til' => $request["til"]];
      return view('returned')->with('param', $param);
    }

    public function history(Request $request){
      $actions = Action::leftJoin('orders', function($join) {
      $join->on('actions.orderId', '=', 'orders.id');
    });
      if($request["query"] != "")
        $actions = $actions->where(function($q) use ($request){
          $q->where('make', 'LIKE', '%'.$request["query"].'%')
        ->orWhere('model', 'LIKE', '%'.$request["query"].'%')
        ->orWhere('name', 'LIKE', '%'.$request["query"].'%')
        ->orWhere('code', 'LIKE', '%'.$request["query"].'%')
        ->orWhere('description', 'LIKE', '%'.$request["query"].'%')
        ->orWhere('vin', 'LIKE', '%'.$request["query"].'%');
      });

      if($request["from"] != "")
        $actions = $actions->where('updated_at', '>=', $request["from"]);
      if($request["til"] != "")
        $actions = $actions->where('updated_at', '<=', $request["til"]);
      if($request["user"] != "")
        $actions = $actions->where('user', $request["user"]);
      $param['actions'] = $actions->orderBy('created_at', 'DESC')->paginate(15, ['actions.*', 'orders.name as orderName']);
      $param['users'] = User::get();
      $param['lastSearch'] = ['query' => $request["query"], 'user'=> $request["user"], 'from' => $request["from"], 'til' => $request["til"]];
      return view('history')->with('param', $param);
    }

    public function viewOrder($id)
    {
      $param['order'] = Order::find($id);
      $param['actions'] = Action::where('orderId', $id)->orderBy('created_at', 'DESC')->get();
      return view('view-order')->with('param', $param);
    }


    public function newOrder(){
      return view('add-order');
    }
    public function submitNewOrder(Request $request){
      Validator::make($request->all(), [
        'name' => 'required|string|min:3|max:190',
        'code' => 'max:50',
        'description' => 'max:255',
        'address' => 'max:190',
        'phone' => '|max:15',
        'timeLimit' => 'max:60',
        'postCode' => 'max:12',
        'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:52128',
      ])->validate();
      $imp = false;
      if(isset($request->important)){
        if($request->important == 1)
        {
          $imp = true;
        }
      }
      $request->timeLimit = str_replace('/','-',$request->timeLimit);
      $size = 0;
      if($request->image != ""){
        ini_set('memory_limit','256M');
        $image = $request->file('image');
        $destinationPath = public_path('/media/uploads/');
        $input['imagename'] = time().'.'.$image->getClientOriginalExtension();
        $img = Image::make($image->getRealPath());
        $size = $img->filesize();
        if($img->height() > 720 || $img->width() > 1280)
        {
          $canvas = Image::canvas(1280, 720);
          $img  = $img->resize(1280, 720, function($constraint)
          {
            $constraint->aspectRatio();
          });
          $canvas->insert($img, 'center');
        }
        $img->save($destinationPath.$input['imagename'], 50);

      }else {
        $input['imagename'] = "";
      }

      Order::create([
        'name' => $request->name,
        'code' => $request->code,
        'address' => $request->address,
        'phone' => $request->phone,
        'description' => $request->description,
        'image' => $input['imagename'],
        'carType' => $size,
        'type' => 'order',
        'important' => $imp,
        'timeLimit' => $request->timeLimit,
        'status' => 'active',
        'whoAdded' => Auth::user()->name
      ]);
      $lastOrder = Order::orderBy('id', 'DESC')->first();
      Action::create([
        'orderId' => $lastOrder->id,
        'newStatus' => 'active',
        'oldStatus' => '-',
        'user' => Auth::user()->name
      ]);
      $request->session()->flash('success', 'Naujas užsakymas išsaugotas.');
      return redirect()->route('active');
    }
    public function newSearch(Request $request){

      $param['makes'] = Vehicle::orderBy('popularity', 'DESC')->orderBy('make', 'ASC')->get()->unique('make');
      $param['years'] = Carbon::now()->year;
      $param['previous'] = $request->all();
      return view('add-search')->with('param', $param);
    }

    public function fillModels(Request $request){
      $models = Vehicle::where('make', $request->make)->orderBy('popularity', 'DESC')->orderBy('model', 'ASC')->get();
      return view('include.models-options')->with('models', $models);
    }
/*
    public function newSearchSubmit(Request $request){

      Validator::make($request->all(), [
        'name' => 'required|string|min:3|max:190',
        'code' => 'max:50',
        'description' => 'max:255',
        'make' => 'max:30',
        'model' => 'max:30',
        'fuel' => 'max:30',
        'vin' => 'max:30',
        'cartype' => 'max:30',
        'carchassis' => 'max:30',
        'timeLimit' => 'max:60',
        'power' => 'max:5000|numeric',
        'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:7168'
      ])->validate();

      $imp = false;
      if(isset($request->important)){
        if($request->important == 1)
        {
          $imp = true;
        }
      }
      $request->timeLimit = str_replace('/','-',$request->timeLimit);
      if($request->make == "-Pasirinkite-")
        $request->make = "";
      if($request->model == "--")
        $request->model = "";
      if($request->make == "-Pasirinkite-")
        $request->year = "";
      if($request->fuel == "--")
        $request->fuel = "";
      if($request->cartype == "--")
        $request->cartype = "";

      if($request->image != ""){
        $image = $request->file('image');
        $input['imagename'] = time().'.'.$image->getClientOriginalExtension();
        $destinationPath = public_path('/media/uploads');
        $image->move($destinationPath, $input['imagename']);
      }else {
        $input['imagename'] = "";
      }


     Order::create([
       'make' => $request->make,
       'model' => $request->model,
       'year' => $request->year,
       'fuel' => $request->fuel,
       'vin' => strtoupper($request->vin),
       'name' => $request->name,
       'power' => $request->power,
       'carType' => $request->cartype,
       'chassisNr' => $request->carchassis,
       'code' => $request->code,
       'description' => $request->description,
       'image' => $input['imagename'],
       'type' => 'search',
       'status' => 'active',
       'whoAdded' => Auth::user()->name,
       'important' => $imp,
       'timeLimit' => $request->timeLimit
     ]);
     Vehicle::where('make', $request->make)->increment('popularity');
     $order = Order::orderBy('created_at', 'desc')->first();
     Action::create([
       'orderId' => $order->id,
       'newStatus' => 'active',
       'oldStatus' => '-',
       'user' => Auth::user()->name
     ]);
     $request->session()->flash('success', 'Naujas užsakymas išsaugotas.');
     return view('preview-search')->with('order', $order);
   }*/

   public function newSearchSubmit(Request $request){

     Validator::make($request->all(), [
       'carinfo' => 'string|min:3|max:190',
       'name' => 'required|string|min:3|max:190',
       'code' => 'max:50',
       'description' => 'max:255',
       'timeLimit' => 'max:60',
       'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:52128'
     ])->validate();

     $imp = false;
     if(isset($request->important)){
       if($request->important == 1)
       {
         $imp = true;
       }
     }
     $request->timeLimit = str_replace('/','-',$request->timeLimit);



     if($request->image != ""){
       ini_set('memory_limit','256M');
       $image = $request->file('image');
       $destinationPath = public_path('/media/uploads/');
       $input['imagename'] = time().'.'.$image->getClientOriginalExtension();
       $img = Image::make($image->getRealPath());
       if($img->height() > 720 || $img->width() > 1280)
       {
         $canvas = Image::canvas(1280, 720);
         $img  = $img->resize(1280, 720, function($constraint)
         {
           $constraint->aspectRatio();
         });
         $canvas->insert($img, 'center');
       }
       $img->save($destinationPath.$input['imagename'], 50);

     }else {
       $input['imagename'] = "";
     }

     $info = preg_split("/[\t]/", $request->carinfo);

     if(!isset($info[0]))
      $info[0] = "";
     if(!isset($info[1]))
       $info[1] = "";
     if(!isset($info[2]))
        $info[2] = "";
     if(!isset($info[3]))
        $info[3] = "";
     if(!isset($info[4])){
       $info[4] = 0;
     }else {
       if($info[4] == "")
         $info[4] = 0;
     }
     if(!isset($info[5])){
       $info[5] = 0;
     }else {
       if($info[5] == "")
         $info[5] = 0;
     }
     if(!isset($info[6]))
         $info[6] = "";
     if(!isset($info[7]))
         $info[7] = "";

    Order::create([
      'make' => "",
      'model' => "",
      'year' => $info[3],
      'fuel' => $info[6],
      'vin' => strtoupper($info[7]),
      'capacity' => $info[4],
      'name' => $info[1]." ".$request->name,
      'power' => $info[5],
      'carType' => $request->cartype,
      'chassisNr' => $request->carchassis,
      'vehicleNr' => $info[0],
      'code' => $request->code,
      'description' => $request->description,
      'image' => $input['imagename'],
      'type' => 'search',
      'status' => 'active',
      'whoAdded' => Auth::user()->name,
      'important' => $imp,
      'timeLimit' => $request->timeLimit
    ]);
    Vehicle::where('make', $request->make)->increment('popularity');
    $order = Order::orderBy('created_at', 'desc')->first();
    Action::create([
      'orderId' => $order->id,
      'newStatus' => 'active',
      'oldStatus' => '-',
      'user' => Auth::user()->name
    ]);
    $request->session()->flash('success', 'Naujas užsakymas išsaugotas.');
    return view('preview-search')->with('order', $order);
   }

   public function orderEdit($id){
     $param['order'] = Order::find($id);
     if($param['order']->type == "search")
      return view('edit-search')->with('param', $param);
    else {
      return view('edit-order')->with('param', $param);
      }
   }

   public function searchEditSubmit(Request $request){
     Validator::make($request->all(), [
       'id' => 'min:1|max:4',
       'name' => 'required|string|min:3|max:190',
       'code' => 'max:50',
       'description' => 'max:255',
       'timeLimit' => 'max:60',
     ])->validate();

     $imp = 0;

     if(isset($request->important)){
       if($request->important == 1)
       {
         $imp = 1;
       }
     }

     $request->timeLimit = str_replace('/','-',$request->timeLimit);

     Order::find($request->id)->update(['name' => $request->name, 'code' => $request->code, 'description' => $request->description, 'timeLimit' => $request->timeLimit,'important' => $imp]);

     $request->session()->flash('success', 'Užsakymas redaguotas.');
     return redirect()->route('active');
   }

   public function orderEditSubmit(Request $request){
     Validator::make($request->all(), [
       'id' => 'min:1|max:4',
       'name' => 'required|string|min:3|max:190',
       'code' => 'max:50',
       'description' => 'max:255',
       'address' => 'max: 50',
       'phone' => 'max: 20',
       'timeLimit' => 'max:60',
     ])->validate();

     $imp = 0;

     if(isset($request->important)){
       if($request->important == 1)
       {
         $imp = 1;
       }
     }
     $request->timeLimit = str_replace('/','-',$request->timeLimit);

     Order::find($request->id)->update(['name' => $request->name, 'code' => $request->code, 'description' => $request->description, 'timeLimit' => $request->timeLimit, 'address' => $request->address, 'phone' => $request->phone, 'important' => $imp]);

     $request->session()->flash('success', 'Užsakymas redaguotas.');
     return redirect()->route('active');
   }

    public function delivered(Request $request, $id){
      if(!Order::exists($id)){
        $request->session()->flash('error', 'Klaida: tokio užsakymo nėra.');
        return redirect()->route('active');
      }
      $order = Order::find($id);
      Action::create([
        'orderId' => $id,
        'newStatus' => 'delivered',
        'oldStatus' => $order->status,
        'user' => Auth::user()->name
      ]);
      Order::find($id)->update(['status'=> 'delivered']);
      $request->session()->flash('success', 'Užsakymo išsaugotas kaip pristatytas.');
      return redirect()->route('active');
    }
    public function delete(Request $request, $id){
      if(!Order::exists($id)){
        $request->session()->flash('error', 'Klaida: tokio užsakymo nėra.');
        return redirect()->route('active');
      }
      if(Order::find($id)->status == "found"){
        $request->session()->flash('error', 'Klaida: negalima atšaukti užsakymo, kurį tiekėjas jau gavęs.');
        return redirect()->route('active');
      }
      $order = Order::find($id);
      Action::create([
        'orderId' => $id,
        'newStatus' => 'deleted',
        'oldStatus' => $order->status,
        'user' => Auth::user()->name
      ]);
      Order::find($id)->update(['status'=> 'deleted']);
      $request->session()->flash('success', 'Užsakymas atšauktas.');
      return redirect()->route('active');
    }

    public function return(Request $request, $id){
      if(!Order::exists($id)){
        $request->session()->flash('error', 'Klaida: tokio užsakymo nėra.');
        return redirect()->route('active');
      }
      $order = Order::find($id);
      Action::create([
        'orderId' => $id,
        'newStatus' => 'return',
        'oldStatus' => $order->status,
        'user' => Auth::user()->name
      ]);
      Order::find($id)->update(['status'=> 'return']);
      $request->session()->flash('success', 'Užsakymas išsaugotas grąžinimui.');
      return redirect()->route('active');
    }

    public function returned(Request $request, $id){
      if(!Order::exists($id)){
        $request->session()->flash('error', 'Klaida: tokio užsakymo nėra.');
        return redirect()->route('active');
      }
      if(Order::find($id)->status != "return"){
        $request->session()->flash('error', 'Klaida: užsakymas nebuvo pažymėtas grąžinimui.');
        return redirect()->route('active');
      }
      $order = Order::find($id);
      Action::create([
        'orderId' => $id,
        'newStatus' => 'returned',
        'oldStatus' => $order->status,
        'user' => Auth::user()->name
      ]);
      Order::find($id)->update(['status'=> 'returned']);
      $request->session()->flash('success', 'Užsakymas grąžintas.');
      return redirect()->route('active');
    }

    public function found(Request $request, $id){

      return view('order-found')->with('id', $id);
    }

    public function foundSubmit(Request $request){
      if(!Order::exists($request->id)){
        $request->session()->flash('error', 'Klaida: tokio užsakymo nėra.');
        return redirect()->route('active');
      }
      if(Order::find($request->id)->status != "active"){
        $request->session()->flash('error', 'Klaida: nežinomas užsakymas.');
        return redirect()->route('active');
      }
      $order = Order::find($request->id);
      Action::create([
        'orderId' => $request->id,
        'newStatus' => 'found',
        'oldStatus' => $order->status,
        'user' => Auth::user()->name
      ]);
      Order::find($request->id)->update(['status'=> 'found', 'cooX' => $request->cooX, 'cooY' => $request->cooY, 'phone' => $request->orderPhone, 'address' => $request->orderAddress, 'price' => $request->price]);
      $request->session()->flash('success', 'Užsakymas pažymėtas kaip gautas.');
      return redirect()->route('active');
    }
    public function refreshOrder(Request $request, $id){
      if(!Order::exists($id)){
        $request->session()->flash('error', 'Klaida: tokio užsakymo nėra.');
        return redirect()->route('active');
      }
      if(Order::find($id)->status != "deleted"){
        $request->session()->flash('error', 'Klaida: užsakymas nebuvo atmestas.');
        return redirect()->route('active');
      }
      $order = Order::find($id);
      Action::create([
        'orderId' => $id,
        'newStatus' => 'active',
        'oldStatus' => $order->status,
        'user' => Auth::user()->name
      ]);
      Order::find($id)->update(['status'=> 'active']);
      $request->session()->flash('success', 'Užsakymas atnaujintas.');
      return redirect()->route('active');
    }

    public function updatePhoto(Request $request){

      Validator::make($request->all(), [
        'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:52128',
        'id' => 'exists:orders'
      ])->validate();
      ini_set('memory_limit','256M');
      $image = $request->file('image');
      $destinationPath = public_path('/media/uploads/');
      $input['imagename'] = time().'.'.$image->getClientOriginalExtension();
      $img = Image::make($image->getRealPath());
      if($img->height() > 720 || $img->width() > 1280)
      {
        $canvas = Image::canvas(1280, 720);
        $img  = $img->resize(1280, 720, function($constraint)
        {
          $constraint->aspectRatio();
        });
        $canvas->insert($img, 'center');
      }
      $img->save($destinationPath.$input['imagename'], 50);

      Order::find($request->id)->update(['image' => $input['imagename']]);

      $request->session()->flash('success', 'Nuotrauka įkelta.');

      return redirect()->back();
    }

    public function checkForUpdates(Request $request){
      if(Auth::user()->role == 'Tiekėjas'){
        $date = $request->date;
        $a = Action::where('updated_at', '>=', $date)->where(function($q){
          $q->where('newStatus', 'active')->orWhere('newStatus', 'return')->orWhere('newStatus', 'deleted');
        })->first();
        if(!is_null($a))
          return strip_tags($a->stringOutput());
        else return 0;
      }
      else{
        $date = $request->date;
        $a =  Action::where('updated_at', '>=', $date)->where(function($q){
          $q->where('newStatus', 'found')->orWhere('newStatus', 'returned');
        })->first();
        if(!is_null($a))
          return strip_tags($a->stringOutput());
        else return 0;
      }
    }

    public function getNotificationInfo(){
      if(Auth::user()->role == 'Tiekėjas'){
        $a = Action::orderBy('updated_at', 'DESC')->where(function($q){
          $q->where('newStatus', 'active')->orWhere('newStatus', 'return')->orWhere('newStatus', 'deleted');
        })->first();
        if(!is_null($a))
          return strip_tags($a->stringOutput());
        else return 0;
      }
      else{
        $a =  Order::where('whoAdded', Auth::user()->name)->orderBy('updated_at', 'DESC')->first()->actions()->where(function($q){
          $q->where('newStatus', 'found')->orWhere('newStatus', 'returned');
        })->first();
        if(!is_null($a))
          return strip_tags($a->stringOutput());
        else return 0;
      }
    }

    public function setSupplierOrderColorGreen($id){
      Order::find($id)->update(['supplier' => 'bg-success']);
      return back();
    }

    public function setSupplierOrderColorYellow($id){
      Order::find($id)->update(['supplier' => 'bg-warning']);
      return back();
    }

    public function setSupplierOrderColorNone($id){
      Order::find($id)->update(['supplier' => null]);
      return back();
    }

}
