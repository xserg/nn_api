<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Validator;
use App\Models\Notify;
use App\Http\Resources\Main_notify as NotifyResource;
   
class NotifyController extends BaseController
{
    public $notify_types=['default', 'success', 'info', 'primary', 'warning', 'danger', 'dark'];
    public $notify_groups=['all', 'metrika', 'webmaster', 'freq', 'pos', 'megaindex', 'yml'];
    
    /**
    * @OA\Post(
    *     path="/api/notify",
    *     summary="Adds a new notify",
    *     tags={"Notify"},       
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             required={"uid"},
    *             required={"type"},
    *             required={"group_name"},        
    *             @OA\Property(property="uid", type="integer"),        
    *             @OA\Property(property="type", type="string"),    
    *             @OA\Property(property="group_name", type="string"),
    *             @OA\Property(property="message", type="string"),  
    *             @OA\Property(property="data", type="string"),
    *             @OA\Property(property="status", type="integer"),      
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
    *             @OA\Items(ref="#/components/schemas/Location")
    *         ),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */       
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'uid' => 'required',
            'type' => 'required',
            'group_name' => 'required',
            'message' => 'required',
            'data' => 'required',
            'status' => 'required'                      
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());       
        }
        if (!in_array($input['type'],  $this->notify_types))  { 
          return $this->sendError('Incorrect type!'); 
        } 
        if (!in_array($input['group_name'], $this->notify_groups)) { 
          return $this->sendError('Incorrect group!');
        } 
        $input['data'] = json_encode($input['data'], JSON_NUMERIC_CHECK);
        $notify = Notify::create($input);
        return $this->sendResponse(new NotifyResource($notify), 'Notify created.');
    }
        
    /**
    * @OA\GET(
    *     path="/api/notify/{nid}",
    *     summary="Get notify by nid",
    *     tags={"Notify"}, 
    *     @OA\Parameter(
    *         description="nid",
    *         in="path",
    *         name="nid",
    *         required=true,
    *         @OA\Schema( type="integer" )
    *     ),    
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="lr response",
    *         @OA\JsonContent(ref="#/components/schemas/Notify"),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */        
    public function show($id)
    {
        $notify = Notify::find($id);
        if (is_null($notify)) {
            return $this->sendError('Notify does not exist.');
        }
        return $this->sendResponse(new NotifyResource($notify), 'Notify fetched.');
    }
    
    /**
    * @OA\GET(
    *     path="/api/notify/get/{uid}/{status}",
    *     summary="Get notify by uid",
    *     tags={"Notify"},         
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
    *     @OA\Parameter(
    *         description="status",
    *         in="path",
    *         name="status",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             example="0",
    *         ),
    *     ),    
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="lr response",
    *         @OA\JsonContent(ref="#/components/schemas/Notify"),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */  
    public function search($uid, $status)
    {
      $notify = Notify::where('uid', $uid)->where('status', $status)->get();
      return $this->sendResponse(NotifyResource::collection($notify), 'Notify fetched.');      
    }
    
    /**
     * @OA\Put(
     *     path="/api/notify/{nid}",
     *     summary="Updates notify",
     *     tags={"Notify"},            
     *     @OA\Parameter(
     *         description="nid to fetch",
     *         in="path",
     *         name="nid",
     *         required=true,
     *         allowEmptyValue=false,         
     *         @OA\Schema(
     *             type="integer",
     *             format="int64",
     *         )
     *     ),    
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *             required={"uid"},
     *             required={"type"},
     *             required={"group_name"}, 
     *             @OA\Property(property="uid", type="integer"),        
     *             @OA\Property(property="type", type="string"),    
     *             @OA\Property(property="group_name", type="string"),
     *             @OA\Property(property="message", type="string"),  
     *             @OA\Property(property="data", type="string"),
     *             @OA\Property(property="status", type="integer"),  
     *             )
     *         )
     *    ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     )
     * )
     */      
    public function update(Request $request, Notify $notify)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            //'nid' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());       
        }

        $notify->update($input);  
        return $this->sendResponse(new NotifyResource($notify), 'notify updated.');
    }

    /**
     * @OA\Delete(
     *     path="/api/notify/{nid}",
     *     tags={"Notify"},            
     *     description="deletes notify by nid",
     *     @OA\Parameter(
     *         description="nid to delete",
     *         in="path",
     *         name="nid",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="notify deleted"
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\Schema(ref="#/components/schemas/ErrorModel")
     *     )
     * )
     */      
    public function destroy(Notify $notify)
    {
        $notify->delete();
        return $this->sendResponse([], 'Notify deleted.');
    }    
    
    /**
    * @OA\Post(
    *     path="/api/notify/set_status",
    *     summary="Update notify status",
    *     tags={"Notify"},       
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             required={"uid"},
    *             required={"nid"},
    *             required={"status"},        
    *             @OA\Property(property="uid", type="integer"),        
    *             @OA\Property(property="nid", type="array",
    *               @OA\Items( @OA\Property( type="integer")),     
    *             ),    
    *             @OA\Property(property="status", type="integer"),  
    *                     example={"uid": 4, "nid": {1,2}, "status": 1}    
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
    *             @OA\Items(ref="#/components/schemas/Notify")
    *         ),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */       
    public function set_status(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'uid' => 'required',
            'nid' => 'required',
            'status' => 'required'                      
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());       
        }
        
        if (is_array($input['nid'])) {
          $nids = $input['nid'];  
        } else {
          $nids = [$input['nid']]; 
        }
              
        $notify = Notify::where('uid', $input['uid'])->wherein('nid', $nids)->get();
        if ($notify->isEmpty()) {
            return $this->sendError('notify does not exist.');
        }
        
        foreach ($notify as $not) {
            $not->status = $input['status'];
            $not->update();
        }          
        return $this->sendResponse(NotifyResource::collection($notify), 'Notify status updated.');
    }
}