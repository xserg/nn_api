<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Validator;
use App\Models\Scheduler;
use App\Http\Resources\Main_scheduler as SchedulerResource;
use DateTime;
   
class SchedulerController extends BaseController
{
    public $controllers=[
      'pos'=>['name'=>'Позиции', 'cid'=>1],
      'freq'=>['name'=>'Частота', 'cid'=>1],
      'metrika'=>['name'=>'Метрика', 'cid'=>1],
      'webmaster'=>['name'=>'ВебМастер', 'cid'=>1],
    ];
    
    /**
    * @OA\GET(
    *     path="/api/schedulers",
    *     summary="Get schedulers list",
    *     tags={"Scheduler"},         
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="lr response",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/Scheduler")
    *         ),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */    
    public function index()
    {
        $scheduler = Scheduler::get();
        return $this->sendResponse(SchedulerResource::collection($scheduler), 'Scheduler fetched.');
    }
    
    /**
    * @OA\Post(
    *     path="/api/scheduler",
    *     summary="Adds a new scheduler",
    *     tags={"Scheduler"},       
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             required={"uid"},    
    *             required={"cid"},
    *             
    *             @OA\Property(property="uid", type="integer"),                 
    *             @OA\Property(property="cid", type="integer"),        
    *             @OA\Property(property="controller", type="string"),    
    *             @OA\Property(property="week", type="integer"),
    *             @OA\Property(property="month", type="integer"),  
    *             @OA\Property(property="time", type="string"),
    *             @OA\Property(property="input_data", type="string"),      
    *             )
    *         )
    *    ),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="lr response",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/Scheduler")
    *         ),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */       
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'uid' => 'required|numeric|gt:0',
            'cid' => 'required|numeric|gt:0',
            'controller' => 'required',
            'week' => 'required|numeric|gt:0',
            'month' => 'required|numeric|gt:0',
            'time' => 'required',
            'input_data' => 'required'                      
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());       
        }
        if (!isset($this->controllers[$input['controller']])) {
          return $this->sendError('Incorrect controller!'); 
        } 

        $input['input'] = json_encode($input['input_data'], JSON_NUMERIC_CHECK);
        $scheduler = Scheduler::create($input);
        return $this->sendResponse(new SchedulerResource($scheduler), 'Scheduler created.');
    }
        
    /**
    * @OA\GET(
    *     path="/api/schedulers/{sid}",
    *     summary="Get scheduler by sid",
    *     tags={"Scheduler"}, 
    *     @OA\Parameter(
    *         description="sid to fetch",
    *         in="path",
    *         name="sid",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *         )
    *     ),    
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="scheduler response",
    *         @OA\JsonContent(ref="#/components/schemas/Scheduler"),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */        
    public function show($sid)
    {
        $scheduler = Scheduler::find($sid);
        if (is_null($scheduler)) {
            return $this->sendError('Scheduler does not exist.');
        }
        return $this->sendResponse(new SchedulerResource($scheduler), 'Scheduler fetched.');
    }
    
    /**
    * @OA\GET(
    *     path="/api/schedulers/get/{uid}",
    *     summary="Get Scheduler by uid",
    *     tags={"Scheduler"},         
    *     @OA\Parameter(
    *         description="uid",
    *         in="path",
    *         name="uid",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             example="4",
    *         ),
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="lr response",
    *         @OA\JsonContent(ref="#/components/schemas/Scheduler"),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */  
    public function search($uid)
    {
        $scheduler = Scheduler::where('uid', $uid)->get();
        if ($scheduler->isEmpty()) {
            return $this->sendError('scheduler does not exist.');
        }
        return $this->sendResponse(SchedulerResource::collection($scheduler), 'Scheduler fetched.');    
    }
    
    /**
    * @OA\Post(
    *     path="/api/schedulers/get_cid",
    *     summary="Get by params",
    *     tags={"Scheduler"},       
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             
    *             required={"cid"},
    *                 
    *             @OA\Property(property="uid", type="integer"),        
    *             @OA\Property(property="cid", type="array",
    *               @OA\Items( @OA\Property( type="integer")),     
    *             ),
    *             @OA\Property(property="controller", type="string"),   
    *             @OA\Property(property="expect", type="bool"),             
    *                     example={"uid": 4, "cid": {1,2}}    
    *             ),        
    *         )
    *    ),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="lr response",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/Scheduler")
    *         ),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */      
    public function get_cid(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'uid' => 'required',
            'cid' => 'required',                
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());       
        }
        $controller = false;
        if(isset($input['controller'])) {
          $controller = $input['controller'];
          if (!isset($this->controllers[$controller])) { 
            return $this->sendError("Wrong controller"); 
          }          
        }
        $expect = false;
        if(isset($input['expect'])) {
          $expect = $input['expect'];
        }        
        if (is_array($input['cid'])) {
          $cids = $input['cid'];  
        } else {
          $cids = [$input['cid']]; 
        }
          
                 
        $scheduler = Scheduler::where('uid', $input['uid'])->wherein('cid', $cids)
        ->when($controller, function ($query, $controller) {
                    return $query->where('controller', $controller);
                })
        ->when($expect, function ($query) {
                    return $query->whereDate('next_time', '<', date("Y-m-d"));
                })               
        ->get();
        
        if ($scheduler->isEmpty()) {
            return $this->sendError('scheduler does not exist.');
        }
        return $this->sendResponse(SchedulerResource::collection($scheduler), 'Scheduler fetched.');    
    }

    /**
     * @OA\Delete(
     *     path="/api/schedulers/{sid}",
     *     tags={"Scheduler"},            
     *     description="deletes scheduler by sid",
     *     @OA\Parameter(
     *         description="sid to delete",
     *         in="path",
     *         name="sid",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="scheduler deleted"
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\Schema(ref="#/components/schemas/ErrorModel")
     *     )
     * )
     */      
    public function destroy(Scheduler $scheduler)
    {
        $scheduler->delete();
        return $this->sendResponse([], 'Scheduler deleted.');
    }
    
    /**
    * @OA\GET(
    *     path="/api/schedulers/get_expect",
    *     summary="Get expected schedulers",
    *     tags={"Scheduler"},         
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="lr response",
    *         @OA\JsonContent(ref="#/components/schemas/Scheduler"),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */  
    public function get_expect()
    {
      $scheduler = Scheduler::whereRaw('next_time < NOW()')->get();
      if ($scheduler->isEmpty()) {
          return $this->sendError('scheduler does not exist.');
      }
      return $this->sendResponse(SchedulerResource::collection($scheduler), 'Scheduler fetched.');    
    }    
  
    /**
    * @OA\Post(
    *     path="/api/schedulers/is_exists",
    *     summary="Check exist",
    *     tags={"Scheduler"},       
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             
    *             required={"cid"},
    *                 
    *             @OA\Property(property="uid", type="integer"),        
    *             @OA\Property(property="cid", type="integer"),
    *             @OA\Property(property="controller", type="string"),       
    *             @OA\Property(property="week", type="integer"),
    *             @OA\Property(property="month", type="integer"),        
    *             @OA\Property(property="time", type="integer"),        
    *             
    *             @OA\Property(property="input_data", type="string"),         
    *         )
    *    ),),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="lr response",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/Scheduler")
    *         ),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */   
    public function is_exists(Request $request)
    {
      $input = $request->all();
      $validator = Validator::make($input, [
          'uid' => 'required',
          'cid' => 'required',                
      ]);
      if($validator->fails()){
          return $this->sendError($validator->errors());       
      }
      $controller = false;
      if(isset($input['controller'])) {
        $controller = $input['controller'];
        if (!isset($this->controllers[$controller])) { 
          return $this->sendError("Wrong controller"); 
        }          
      }        
               
      $scheduler = Scheduler::where('uid', $input['uid'])->where('cid', $input['cid'])
      ->when($controller, function ($query, $controller) {
                  return $query->where('controller', $controller);
              })           
      ->get();
      
      if ($scheduler->isEmpty()) {
          return $this->sendError('scheduler does not exist.');
      }
      
      //print_r($scheduler);
      foreach ($scheduler as $row) {
          if (strcmp($controller, $row->controller) !=0 ) { 
            continue; 
          }
          if ($input['week'] != $row->week) { 
            continue; 
          }
          if ($input['month'] != $row->month) { 
            continue; 
          }
          if ($input['time'] != $row->time) { 
            continue; 
          }
          if ($input['input_data'] != $row->input) { 
            continue; 
          }
          return $this->sendResponse(new SchedulerResource($row), 'Scheduler exist');  
      }
      return $this->sendError('scheduler does not exist.');  
    }  
  
    /**
    * @OA\Post(
    *     path="/api/schedulers/next_time",
    *     summary="Get next_time",
    *     tags={"Scheduler"},       
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             @OA\Property(property="last_time", type="integer", example=0),    
    *             @OA\Property(property="week", type="integer"),
    *             @OA\Property(property="month", type="integer"),        
    *             @OA\Property(property="time", type="integer"),               
    *         )
    *    ),),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="lr response",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/Scheduler")
    *         ),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */           
    public function next_time(Request $request)
    {
        $input = $request->all();
        $ret = $this->get_next_time($input['last_time'] ?? 0, $input['week'] ?? 0, $input['month'] ?? 0, $input['time'] ?? 0);
        return $this->sendResponse($ret, 'Scheduler next.');    
    }
    
    /**
     * @OA\Put(
     *     path="/api/schedulers/{sid}",
     *     summary="Updates a scheduler",
     *     tags={"Scheduler"},    
     *     @OA\Parameter(
     *         description="sid to update",
     *         in="path",
     *         name="sid",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64",
     *         )
     *     ),    
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             
     *             @OA\Schema(
     *             required={"cid"},
     *                 
     *             @OA\Property(property="uid", type="integer"),        
     *             @OA\Property(property="cid", type="integer"),
     *             @OA\Property(property="controller", type="string"),       
     *             @OA\Property(property="week", type="integer"),
     *             @OA\Property(property="month", type="integer"),        
     *             @OA\Property(property="time", type="integer"),                  
     *             @OA\Property(property="input_data", type="string")
     *                
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     )
     * )
     */    
    public function update(Request $request, Scheduler $scheduler)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
          'uid' => 'required',
          'cid' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());       
        }

        $scheduler->update($input);  
        return $this->sendResponse(new SchedulerResource($scheduler), 'scheduler updated.');
    }
    
    /**
     * Узнать когда должен выполнится в следующий раз
     * /api/scheduler/get-next-time
     *
     * @param timestamp $last_time
     * @param integer $week
     * @param integer $month
     * @param integer $time
     * @return unixtime
     */
    public function get_next_time($last_time=0, $week=0, $month=0, $time=0)
    {
      $next_dt=false;
      $current_dt=new DateTime(); if (!$last_time) { $last_time=$current_dt->format('U'); }
      $current_year=$current_dt->format('Y');
      $need_hh=0; $need_mm=0; 
      //$this->nn->int_to_time($time, $need_hh, $need_mm);
      
      if ($week) // дни недели
      {
        $current_week=date('W');
        $days=$this->bit_to_array($week); if (!$days) { $days=[0]; }
        
        foreach ($days AS $day)
        {
          $dt = (new DateTime())->setISODate($current_year, $current_week, $day); $dt->setTime($need_hh, $need_mm);
          //echo $current_dt->format('Y.m.d H:i:s').' => '.$dt->format('Y.m.d H:i:s').PHP_EOL;
          if ($dt>$current_dt) { $next_dt=$dt; break; }
        }
        if (!$next_dt) { $next_dt = (new DateTime())->setISODate($current_year, $current_week+1, $days[0]); $next_dt->setTime($need_hh, $need_mm); }
      }
      else if ($month) // дни месяца
      {
        $current_month=date('m');
        $days=$this->month_to_days($month, $current_year, $current_month); if (!$days) { $days=[0]; }
        foreach ($days AS $day)
        {
          $dt = new DateTime("$current_year-$current_month-$day $need_hh:$need_mm");
          if ($dt>$current_dt) { $next_dt=$dt; break; }
        }
        if (!$next_dt)
        {
          $current_month=(int)$current_month+1; if ($current_month>12) { $current_month=1; $current_year++; }
          $day=$days[0];
          $next_dt = new DateTime("$current_year-$current_month-$day $need_hh:$need_mm");
        }
      }
      else // каждые несколько часов
      {
        $next_dt = new DateTime(); $next_dt->setTimestamp($last_time+$time*60);
      }
      return $next_dt;
    }
    
    /**
    * @OA\Post(
    *     path="/api/schedulers/update_time/{sid}",
    *     summary="Update time",
    *     tags={"Scheduler"},
    *     @OA\Parameter(
    *         description="sid to update",
    *         in="path",
    *         name="sid",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             format="int64",
    *         )
    *     ),               
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             @OA\Property(property="week", type="integer"),
    *             @OA\Property(property="month", type="integer"),        
    *             @OA\Property(property="time", type="integer"),             
    *         )
    *    ),),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="lr response",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/Scheduler")
    *         ),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */ 
    public function update_time(Request $request, $sid)
    {
      $input = $request->all();
      $dt_next=$this->get_next_time(0, $input['week'] ?? 0, $input['month'] ?? 0, $input['time'] ?? 0);
      $next_time=$dt_next->format('Y-m-d H:i:00');  
      $scheduler = Scheduler::find($sid);
      if (is_null($scheduler)) {
          return $this->sendError('Scheduler does not exist.');
      }
      $scheduler->update(['next_time' => $next_time]);
      return $this->sendResponse(new SchedulerResource($scheduler), 'Scheduler updated.');
    }
    
    /**
     * Преобразует дни в виде битов, в дни
     *
     * @param int $bits
     * @return array
     */
    public function bit_to_array($bits)
    {
      if (!$bits) { return []; }
      
      $output=[];
      for ($i=1; $i<=28; $i++)
      {
        if ($bits & (1<<$i)) { $output[]=$i; }
      }
      return $output;
    }
    
    /**
     * Вернет из битов month корректные дни по текущему месяцу
     *
     * @param integer $bits
     * @param integer $year_nomer
     * @param integer $month_nomer
     * @return void
     */
    public function month_to_days($bits, $year_nomer=2022, $month_nomer=12)
    {
      $output=[];
      $days=$this->bit_to_array($bits);
      if ($bits==536870910) { $days[]=29; $days[]=30; $days[]=31; } // если все дни отмечены, то добавляем недостающие
      foreach ($days AS $day)
      {
        if ($bits!=536870910 && $day==28) // последний день месяца
        {
          $last_day = (new DateTime("$year_nomer-$month_nomer-01"))->format('t');
          $output[]=$last_day;
          break;
        }
        if (checkdate($month_nomer, $day, $year_nomer)) { $output[]=$day; }
        
      }
      return $output;
    }  
    
}