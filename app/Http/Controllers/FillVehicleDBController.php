<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;
use Carbon\Carbon;
use App\Order;
use App\Vehicle;
class FillVehicleDBController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function newOrder(){
      return view('add-order');
    }
    public function submitNewOrder(Request $request){
      Validator::make($request->all(), [
        'name' => 'required|string|min:3|max:190',
        'code' => 'max:50',
        'address' => 'required|string|min:5|max:190',
        'phone' => 'required|string|min:6|max:15',
        'timeLimit' => 'max:60',
        'postCode' => 'max:12',
      ])->validate();
      $imp = false;
      if(isset($request->important)){
        if($request->important == 1)
        {
          $imp == true;
        }
      }
      if($request->timeLimit == null)
      {
        $timeLimit = Carbon::now()->addMonth();
      }
      else{
        $timeLimit = $request->timeLimit;
      }
      Order::create([
        'name' => $request->name,
        'code' => $request->code,
        'address' => $request->address,
        'phone' => $request->phone,
        'important' => $imp,
        'timeLimit' => $timeLimit,
        'postCode' => $request->postCode,
        'status' => 'active',
        'whoAdded' => Auth::user()->name
      ]);
      $request->session()->flash('success', 'Naujas užsakymas išsaugotas.');
      return redirect()->route('home');
    }
    public function newSearch(){
      return view('add-search');
    }

    public function fillVehicles(){
      $file = file('makes.txt');
      $makes = [];
      foreach($file as $line)
      {
        $line = trim(preg_replace('/\s\s+/', ' ', $line));
        $line = explode('=', $line);
        array_push($makes, $line);
      }
      foreach($makes as $m){
        $url = 'https://autoplius.lt';
        $data = array('parent_id' => $m[0], 'target_id' => 'model_id', 'project' => 'autoplius', 'category_id' => '2', 'type' => 'search', 'my_adds' => 'false', '__block' => 'ann_ajax_0_plius', '__opcode' => 'ajaxGetChildsTo');

        // use key 'http' even if you send the request to https://...
        $options = array(
          'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
          )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
      if ($result === FALSE) { echo "Ivyko klaida..."; }
      else{
        $result = str_replace('<option value=\"\">- Pasirinkite -<\/option>', '', $result);
        $result = str_replace('"', '', $result);
        $results = explode('<\/option>', $result);
        $models = [];
        foreach($results as $r){
          $model = explode('\>', $r);
          if(count($model) < 2) continue;
          Vehicle::create([
            'make' => $m[1],
            'model' => $model[1],
            'popularity' => 0,
          ]);
        }
      }
      }
      echo "baigta...";
    }
}
