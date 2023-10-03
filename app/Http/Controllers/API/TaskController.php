<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Validator;
use App\Models\Tasks as Task;
use App\Http\Resources\Main_tasks as TaskResource;
   
class TaskController extends BaseController
{
    private $status = [
      'new'      => '<span class="badge badge-light">Новый</span>',
      'waitSlot' => '<span class="badge badge-warning">Ожидает</span>',
      'starting' => '<span class="badge badge-primary">Запуск</span>',
      'paused'   => '<span class="badge badge-danger">Пауза</span>',
      'pausing'  => '<span class="badge badge-danger">Пауза</span>',
      'stopping' => '<span class="badge badge-danger">Остановлен</span>',
      'completed'=> '<span class="badge badge-success">Завершение</span>',
      'deleting' => '<span class="badge badge-success">Завершен</span>',
      'work'     => '<span class="badge badge-primary">В работе</span>',
      'progress' => 
      '<div class="progress">
        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
        role="progressbar" style="width:%progress%%;" aria-valuenow="%progress%" aria-valuemin="0" 
        aria-valuemax="100">%progress%%
        </div>
      </div>',
      'other'    => '<span class="badge badge-light">%status%</span>',
    ];

    /**
    * @OA\Post(
    *     path="/api/tasks",
    *     summary="Adds a new tasks",
    *     tags={"Tasks"},       
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             required={"uid"},    
    *             required={"cid"},
    *             
    *             @OA\Property(property="uid", type="integer"),                 
    *             @OA\Property(property="engine", type="string"),  
    *             @OA\Property(property="type", type="string"),
    *             @OA\Property(property="cid", type="integer"),
    *             @OA\Property(property="amount", type="integer"),          
    *             @OA\Property(property="data", type="string"),
    *             @OA\Property(property="status", type="string", example="new"),            
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
    *             @OA\Items(ref="#/components/schemas/Task")
    *         ),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */ 
    //$uid, $engine='aparser', $type='freq', $cid=NULL, $amount=0, $data=[], $status='new'      
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'uid' => 'required|numeric',              
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());       
        }
        $input['engine'] = $input['engine'] ?? 'aparser';
        $input['type'] = $input['type'] ?? 'freq';
        $input['data'] = json_encode($input['data'], JSON_NUMERIC_CHECK);
        $input['success'] = 0;
        $input['fail'] = 0;
        $input['nodata'] = 0;
        $task = Task::create($input);
        return $this->sendResponse(new TaskResource($task), 'Task created.');
    }
    
    /**
    * @OA\GET(
    *     path="/api/tasks/{tid}",
    *     summary="Get task by tid",
    *     tags={"Tasks"}, 
    *     @OA\Parameter(
    *         description="tid to fetch",
    *         in="path",
    *         name="tid",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *         )
    *     ),    
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="lr response",
    *         @OA\JsonContent(ref="#/components/schemas/Task"),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */        
    public function show($tid)
    {
        $task = Task::find($tid);
        if (is_null($task)) {
            return $this->sendError('Task does not exist.');
        }
        return $this->sendResponse(new TaskResource($task), 'Task fetched.');
    }
    
    /**
    * @OA\POST(
    *     path="/api/tasks/get",
    *     summary="Get Task search",
    *     tags={"Tasks"},         
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             @OA\Property(property="select", type="string"),      
    *             @OA\Property(property="uid", type="integer"),                 
    *             @OA\Property(property="engine", type="string"),  
    *             @OA\Property(property="type", type="string"),
    *             @OA\Property(property="cid", type="integer"),
    *             @OA\Property(property="status", type="string", example="new"),
    *    
    *             @OA\Property(property="field_name", type="string"),      
    *             @OA\Property(property="field_op", type="string"),              
    *             @OA\Property(property="field_value", type="string"),
    *             @OA\Property(property="field_time_name", type="string"),                    
    *             @OA\Property(property="field_time_value", type="string"),
    *             @OA\Property(property="limit", type="integer"),                    
    *             )
    *         )
    *    ),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="task response",
    *         @OA\JsonContent(ref="#/components/schemas/Task"),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */
    public function search(Request $request)
    {
        $input = $request->all();
        $task = $this->get_tasks($input);
        if ($task->isEmpty()) {
            return $this->sendError('task does not exist.');
        }      
        return $this->sendResponse(TaskResource::collection($task), 'Task fetched.');    
    }  
     
    public function get_tasks($input)
    {
        $fields = ['tid', 'cid', 'amount', 'success', 'fail', 'nodata', 'progress', 'status'];
        $time_fields = ['create_time','last_time','stop_time'];
        $fields_join = [
          'nn_base.base_domains.domain', 
          'nn_control.control_groups.group_name', 
          'nn_base.base_lr.name as lr_name'
        ];
        $validator = Validator::make($input, [
          'uid' => 'numeric',
          'cid' => 'numeric|gt:0',
          'field_name' => 'in:' . implode(',', $fields),
          'field_time_name' => 'in:' . implode(',', $time_fields),         
        ]);
        
        if($validator->fails()){
            return $this->sendError($validator->errors());       
        }
        $tid_arr = null;
        if(isset($input['tid'])) {
          if (is_array($input['tid'])) {
              $tid_arr = $input['tid'];
          } elseif (preg_match('/,/', $input['tid'])) {
              $tid_arr = explode(',', $input['tid']);
          } else {
              $tid_arr = [$input['tid']];
          }
        }
        $status = $input["status"] ?? null;
        $engine = $input["engine"] ?? null;
        $type = $input["type"] ?? null;
        $uid = $input["uid"] ?? null;
        $cid = $input["cid"] ?? null;
        $limit = $input["limit"] ?? null;
        
        $field = null;
        if (!empty($input["field_name"]) && !empty($input["field_value"])) {
          $field['name'] = $input["field_name"] ?? null;
          $field['op'] = $input["field_op"] ?? '=';
          $field['value'] = $input["field_value"] ?? null;
        }
        
        $field_time = null;
        if (!empty($input["field_time_name"]) && !empty($input["field_time_value"])) {
          $field_time['name'] = $input["field_time_name"];
          $field_time['value'] = $input["field_time_value"];
        }
        $select = ['nn_main.main_tasks.*'];
        if(isset($input['select'])) {
          if (is_array($input['select'])) {
              $select = $input['select'];
          } elseif (preg_match('/,/', $input['select'])) {
              $select = explode(',', $input['select']);
          } else {
              $select = [$input['select']];
          }
          $select=array_merge(array_map('trim', $select), ['tid']);
        }
        $select=array_merge($select, $fields_join);
        
        $task = Task::select($select)
        ->leftJoin('nn_control.control_domains', 'control_domains.cid', '=', 'nn_main.main_tasks.cid')
        ->leftJoin('nn_base.base_domains', 'control_domains.did', '=', 'base_domains.did')
        ->leftJoin('nn_control.control_groups', 'control_domains.gid', '=', 'control_groups.gid')
        ->leftJoin('nn_base.base_lr', 'control_domains.lr', '=', 'base_lr.lr')
        ->when($tid_arr, function ($query, $tid_arr) {
                    return $query->wherein('tid', $tid_arr);
                })
        ->when($engine, function ($query, $engine) {
                    return $query->where('engine', $engine);
                })
        ->when($type, function ($query, $type) {
                    return $query->where('main_tasks.type', $type);
                })
        ->when($uid, function ($query, $uid) {
                    return $query->where('main_tasks.uid', $uid);
                })
        ->when($cid, function ($query, $cid) {
                    return $query->where('main_tasks.cid', $cid);
                })                                        
        ->when($field, function ($query, $field) {
                    if (is_array($field['value'])) {
                      if ($field['op'] == '!=') {
                          return $query->wherenotin($field['name'], $field['value']);
                      }
                        return $query->wherein($field['name'], $field['value']);
                    }
                    return $query->where('main_tasks.'.$field['name'], $field['op'], $field['value']);
                })
        ->when($field_time, function ($query, $field_time) {
                    return $query->whereRaw($field_time['name'] . ' ' . $field_time['value']);
                })
        ->when($limit, function ($query, $limit) {
                    return $query->limit($limit);
                })                                          
        ->get();
        return $task;
    }
    
    /**
    * @OA\Post(
    *     path="/api/tasks/set_status",
    *     summary="Update task status",
    *     tags={"Tasks"},       
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             required={"tid"},
    *             required={"status"},        
    *             @OA\Property(property="tid", type="integer"),          
    *             @OA\Property(property="status", type="string"),  
    *                     example={"tid": 2,  "status": "deleting"}    
    *             )
    *         )
    *    ),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="task response",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/Task")
    *         ),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */       
    public function set_status(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'tid' => 'required',
            'status' => 'required'                      
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());       
        }
              
        $task = Task::find($input['tid']);
        if (is_null($task)) {
            return $this->sendError('task does not exist.');
        }
        $task->status = $input['status'];
        if ($input['status'] == 'deleting') { 
          $task->stop_time = date('Y-m-d H:i:s');
          $task->progress = 100; 
        }
        $task->save();             
        return $this->sendResponse(new TaskResource($task), 'Task status updated.');
    }

    /**
     * @OA\Put(
     *     path="/api/tasks/{tid}",
     *     summary="Updates task",
     *     tags={"Tasks"},    
     *     @OA\Parameter(
     *         description="tid to update",
     *         in="path",
     *         name="tid",
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
     *             @OA\Property(property="uid", type="integer"),        
     *             @OA\Property(property="cid", type="integer"),            
     *             @OA\Property(property="success", type="integer"),
     *             @OA\Property(property="fail", type="integer"),        
     *             @OA\Property(property="nodata", type="integer"),                  
     *             @OA\Property(property="output", type="string"),
     *             @OA\Property(property="status", type="string"),  
     *             @OA\Property(property="progress", type="integer"),                  
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     )
     * )
     */
        
    public function update(Request $request, Task $task)
    {
        $input = $request->all();

        $task->update($input);  
        return $this->sendResponse(new TaskResource($task), 'task updated.');
    }
  
    /**
    * @OA\POST(
    *     path="/api/tasks/is_exists",
    *     summary="Check Task exist",
    *     tags={"Tasks"},         
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             @OA\Property(property="uid", type="integer"),                 
    *             @OA\Property(property="engine", type="string"),  
    *             @OA\Property(property="type", type="string"),
    *             @OA\Property(property="cid", type="integer"),          
    *             @OA\Property(property="data", type="string"),          
    *             )
    *         )
    *    ),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="task response",
    *         @OA\JsonContent(ref="#/components/schemas/Task"),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */  
    public function is_exists(Request $request)
		{
      $input = $request->all();
      $engine = $input["engine"] ?? null;
      $type = $input["type"] ?? null;
      $uid = $input["uid"] ?? null;
      $cid = $input["cid"] ?? null;
      $task = Task::select('*')
      ->when($engine, function ($query, $engine) {
                  return $query->where('engine', $engine);
              })
      ->when($type, function ($query, $type) {
                  return $query->where('type', $type);
              })
      ->when($uid, function ($query, $uid) {
                  return $query->where('uid', $uid);
              })
      ->when($cid, function ($query, $cid) {
                  return $query->where('cid', $cid);
              })                                        
      ->get();
      foreach ($task as $row) {
          if ($row->data == $input["data"]) {
            return $this->sendResponse(new TaskResource($row), 'Task already exist.');
          }
      }
      return $this->sendError('Task does not exist.');     
    }

    /**
    * @OA\GET(
    *     path="/api/tasks/html_status",
    *     summary="Get html status",
    *     tags={"Tasks"}, 
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */          
    function html_status()
    {
      return $this->sendResponse($this->status, 'Task status.');
    }
    
    /**
    * @OA\GET(
    *     path="/api/tasks/html_types",
    *     summary="Get html status",
    *     tags={"Tasks"}, 
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */             
    function html_types()
    {
      $output = [
        'aparser' => [
          'freq' => 'Частота',
          'pos' => 'Позиции',
          'dropdomains' => 'Дроп Домены',
          '' => 'Aparser: %type%',
        ],
        'yml' => [
          'freq' => 'Частота XML',
          'pos' => 'Позиции XML',
          '' => 'YML: %type%',
        ],
        'links' => [
          'import' => 'Импорт доноров',
          '' => 'Links: %type%',
        ],
        'megaindex' => [
          '' => 'MegaIndex: %type%',
        ],
      ];
      return $this->sendResponse($output, 'Task status.');
    }
  
    /**
    * @OA\POST(
    *     path="/api/tasks/html_task",
    *     summary="html task",
    *     tags={"Tasks"},         
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             @OA\Property(property="tid", type="integer"),                 
    *             @OA\Property(property="engine", type="string"),  
    *             @OA\Property(property="type", type="string"),
    *             @OA\Property(property="data", type="string"),
    *             @OA\Property(property="owner", type="integer"),              
    *             )
    *         )
    *    ),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="task response",
    *         @OA\JsonContent(ref="#/components/schemas/Task"),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */    
    function html_task(Request $request)
    {
      $input = $request->all();
      $tid = $input["tid"];
      $engine = $input["engine"] ?? 'aparser';
      $type = $input["type"] ?? 'freq';
      $data = json_decode($input["data"], true) ?? [];
      $owner = $input["owner"] ?? 0;
      
      
      $url='#';    
      if ($engine == 'aparser') {
        if ($type == 'freq') { 
          $url='/control/site/'.$data['cid'].'/request/'; 
        } else if ($type == 'pos') { 
          $url = '/control/site/'.$data['cid'].'/'; 
        } else if ($type == 'dropdomains') { 
          $url = '/tool/megaindex/dropdomains/'; 
        }
      }
      if ($engine == 'yml') {
        if ($type == 'pos') { 
          $url = '/control/site/'.$data['cid'].'/'; 
        }
      } else if ($engine == 'megaindex') {
        if ($type == 'visrep') { 
          $url = '/tool/megaindex/visrep/'; 
        } else { 
          $url = '/tool/megaindex/'; 
        }
      } else if ($engine == 'links' && $type == 'import') {
        $url = '/links/sites/import/';
      }
      
      $owner_text = ''; 
      if ($owner) { 
        $owner_text = ' <i class="fa fa-user-circle text-primary" style="font-size: 75%; vertical-align: middle;"></i>'; 
      }
      $output = '<a href="'.$url.'">#'.$tid.$owner_text.'</a>';
      return $this->sendResponse($output, 'html task.');
    }
    
    /**
    * @OA\POST(
    *     path="/api/tasks/html_table",
    *     summary="Get Tasks as table",
    *     tags={"Tasks"},         
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             @OA\Property(property="select", type="string"),      
    *             @OA\Property(property="uid", type="integer"),                 
    *             @OA\Property(property="engine", type="string"),  
    *             @OA\Property(property="type", type="string"),
    *             @OA\Property(property="cid", type="integer"),
    *             @OA\Property(property="status", type="string", example="new"),
    *    
    *             @OA\Property(property="field_name", type="string"),      
    *             @OA\Property(property="field_op", type="string"),              
    *             @OA\Property(property="field_value", type="string"),
    *             @OA\Property(property="field_time_name", type="string"),                    
    *             @OA\Property(property="field_time_value", type="string"),
    *             @OA\Property(property="limit", type="integer"),                    
    *             )
    *         )
    *    ),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="Task response",
    *         @OA\JsonContent(ref="#/components/schemas/Task"),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */    
		function html_table(Request $request)
		{
      $input = $request->all();
      $tasks = $this->get_tasks($input);
      if ($tasks->isEmpty()) {
          return $this->sendError('task does not exist.');
      }      
			$output='<table>';
			foreach ($tasks AS $k => $task) {	
        $text_status=$this->status[$task->status];
				$output .= '<tr><td>#' . $task->tid . '</td>'
					. '<td>' . $task->create_time . '</td>'
					. '<td>' . $task->domain . ' ' . $task->group_name . ' ' . $task->lr_name . '</td>'
					. '<td>' . $this->status[$task->status] . '</td></tr>';
			}
			$output .= '</table>';			
			return $this->sendResponse($output, 'html table.');
		}
		     
}