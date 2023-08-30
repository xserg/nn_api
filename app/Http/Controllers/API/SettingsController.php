<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Validator;
use App\Models\Control_users_settings as Settings;
use App\Http\Resources\Control_users_settings as SettingsResource;
   
class SettingsController extends BaseController
{
    /**
    * @OA\GET(
    *     path="/api/user-settings/{uid}",
    *     summary="Get settings list for uid",
    *     tags={"User Settings"},
    *     @OA\Parameter(
    *         description="uid",
    *         in="path",
    *         name="uid",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             example=3,
    *         ),
    *     ),         
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="Settings response",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/Settings")
    *         ),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */    
    public function index($uid)
    {
        $setting = Settings::where('uid', $uid)->get();
        return $this->sendResponse(SettingsResource::collection($setting), 'Settings fetched.');
    }
    
    /**
    * @OA\Post(
    *     path="/api/user-settings/{uid}",
    *     summary="Adds a new Settings",
    *     tags={"User Settings"},         
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             required={"uid"},
    *             @OA\Property(property="uid", type="integer"), 
    *             required={"type"},
    *             @OA\Property(property="type", type="string"),
    *             required={"value"},
    *             @OA\Property(property="value", type="string"),    
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
    *             @OA\Items(ref="#/components/schemas/Settings")
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
            'value' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());       
        }
        $setting = Settings::create($input);
        return $this->sendResponse(new SettingsResource($setting), 'Settings created.');
    }

    /**
    * @OA\GET(
    *     path="/api/user-settings/{uid}/{sid}",
    *     summary="Get Settings by {sid}",
    *     tags={"User Settings"},
    *     @OA\Parameter(
    *         description="uid",
    *         in="path",
    *         name="uid",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             example=3,
    *         ),
    *     ),             
    *     @OA\Parameter(
    *         description="cid",
    *         in="path",
    *         name="sid",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             example=3,
    *         ),
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="lr response",
    *         @OA\JsonContent(ref="#/components/schemas/Settings"),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */  
    public function show($uid, $sid)
    {
        $setting = Settings::find($sid);
        if (is_null($setting)) {
            return $this->sendError('Settind does not exist.');
        }        
        return $this->sendResponse(new SettingsResource($setting), 'Settings fetched.');
    }
    
    /**
    * @OA\GET(
    *     path="/api/user-settings/{uid}/search",
    *     summary="Get Settings for user {uid} and optional {type}",
    *     tags={"User Settings"},         
    *     @OA\Parameter(
    *         description="uid",
    *         in="path",
    *         name="uid",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             example=3,
    *         ),
    *     ),
    *     @OA\Parameter(
    *         description="type",
    *         in="query",
    *         name="type",
    *         required=false,
    *         @OA\Schema(
    *             type="string",
    *          example="show", ),
    *     ),            
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="lr response",
    *         @OA\JsonContent(ref="#/components/schemas/Settings"),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */  
    public function search(Request $request, $uid)
    {
        $input = $request->all();
        $type = null;
        if(isset($input['type'])) {
          $type = $input['type'];
        }
        $setting = Settings::where('uid', $uid)
        ->when($type, function ($query, $type) {
                    return $query->where('type', $type);
                })
        ->get();
        return $this->sendResponse(SettingsResource::collection($setting), 'Settings fetched.');
    }
    
        /**
         * @OA\Post(
         *     path="/api/user-settings/{uid}/update",
         *     summary="Updates settings for uid and type",
         *     tags={"User Settings"},            
         *     @OA\Parameter(
         *         description="uid",
         *         in="path",
         *         name="uid",
         *         required=true,
         *         @OA\Schema(
         *             type="integer",
         *             format="int64",
         *         )
         *     ),
         *     @OA\Parameter(
         *         description="type",
         *         in="query",
         *         name="type",
         *         required=true,
         *         @OA\Schema(
         *             type="string",
         *          example="show", ),
         *     ),                         
         *     @OA\RequestBody(
         *         @OA\MediaType(
         *             mediaType="application/json",
         *             @OA\Schema(
         *             required={"value"},
         *             @OA\Property(property="value", type="array",
         *               @OA\Items( @OA\Property( type="string")), 
         *               example={ "column_pos": 0 }        
         *             ),
         *            
         *             )
         *         )
         *    ),
         *     @OA\Response(
         *         response=200,
         *         description="OK"
         *     )
         * )
         */
         public function update2(Request $request, $uid)
         {
             $input = $request->all();
             $input['uid'] = $uid;
            
             $validator = Validator::make($input, [
                 'uid' => 'required',
                 'type' => 'required',
                 'value' => 'required',
             ]);
             if($validator->fails()){
                 return $this->sendError($validator->errors());       
             }
             
             Settings::updateOrInsert(
               ['uid' => $uid, 'type' => $input['type']],
               ['value' => json_encode($input['value'])]
             );
             $setting = Settings::where('uid', $uid)->where('type', $input['type'])->first();
             return $this->sendResponse(new SettingsResource($setting), 'Settings updated.');
         }

    /**
     * @OA\Delete(
     *     path="/api/user-settings/{uid}/{sid}",
     *     description="deletes a single setting by sid",
     *     tags={"User Settings"}, 
     *     @OA\Parameter(
     *         description="uid",
     *         in="path",
     *         name="uid",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),              
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
     *         description="setting deleted"
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\Schema(ref="#/components/schemas/ErrorModel")
     *     )
     * )
     */      
    public function destroy($uid, $sid)
    {
        $setting = Settings::find($sid); 
        $setting->delete();
        return $this->sendResponse([], 'Settings deleted.');
    }
}