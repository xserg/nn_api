<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Validator;
use App\Models\Control_domains as Control;
use App\Models\Base_domains as Domain;
use App\Http\Resources\Control_domains as ControlResource;
use App\Http\Resources\Base_domains as DomainResource;
   
class ControlController extends BaseController
{
  /**
  * @OA\GET(
  *     path="/api/controls",
  *     summary="Get controls list",
  *     @OA\Response(
  *         response=200,
  *         description="OK",
  *         response=200,
  *         description="control response",
  *         @OA\JsonContent(
  *             type="array",
  *             @OA\Items(ref="#/components/schemas/Control_domain")
  *         ),
  *     ),
  *     security={ * {"sanctum": {}}, * },
  * )
  */    
    public function index()
    {
        $control = Control::orderBy('cid')->get();
        return $this->sendResponse(ControlResource::collection($control), 'control fetched.');
    }
    
    /**
    * @OA\Post(
    *     path="/api/controls",
    *     summary="Adds a new control",
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             required={"uid"},
    *             @OA\Property(property="uid", type="integer"),
    *             @OA\Property(
    *               property="domain", type="array",
    *               @OA\Items( @OA\Property( type="string")), 
    *               example={
    *                      "google.com",
    *                      "yandex.ru",
    *                      "site1.net",
    *               },
    *             ),
    *             )
    *         )
    *    ),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="control response",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/Control_domain")
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
            'domain' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());       
        }
        
        if (is_array($input['domain'])) {
            $domain_arr = $input['domain'];
        } elseif (preg_match('/,/', $input['domain'])) {
            $domain_arr = explode(',', $input['domain']);
        } else {
            $domain_arr = [$input['domain']];
        }
        
        $did_arr = [];
        $domain = new Domain; 

        $i = 0;
        foreach ($domain_arr as $domain_name) {
            $domain->store($domain_name, false);
            //print_r(new DomainResource($domain));
            //if($domain->store($input['domain'], false)) {
            $did_arr[$i]['domain'] = $domain_name;
            $did_arr[$i]['did'] = $domain->did;
            $did_arr[$i]['error'] = $domain->error;
            
            //}
            if ($domain->did) {
              if (Control::where('uid', $input['uid'])->where('did', $domain->did)->first()) {
                  $did_arr[$i]['error'] .= ' Control domain exist for this user';
              } else {
                  Control::create([
                    'uid' => $input['uid'], 
                    'did' => $domain->did,
                    'yid' => 0,	
                    'metrika_id' => 0,	
                    'yandex_id' => 0,	            
                    'lr' => 0,
                    'hidden' => 0,
                    'request_all' => 0,
                    'request_trash' => 0,
                    'pos_settings' => '',
                    'external_excluded' => ''
                ]);
              }
            }
            $i++;
        } 
        
        return $this->sendResponse($did_arr, 'control created.');
    }
    
    /**
    * @OA\GET(
    *     path="/api/controls/{cid}",
    *     summary="Get control by cid",
    *     @OA\Parameter(
    *         description="Control to fetch",
    *         in="path",
    *         name="cid",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             format="int64",
    *         ),
    * example=4    
    *     ),    
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="control response",
    *         @OA\JsonContent(ref="#/components/schemas/Control_domain"),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */     
    public function show($id)
    {
        $control = Control::find($id);
        if (is_null($control)) {
            return $this->sendError('control does not exist.');
        }
        return $this->sendResponse(new ControlResource($control), 'control fetched.');
    }

    /**
    * @OA\GET(
    *     path="/api/search/{uid}",
    *     summary="Get control by cid",
    *     @OA\Parameter(
    *         description="Control to fetch",
    *         in="path",
    *         name="cid",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             format="int64",
    *         ),
    * example=4    
    *     ),    
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="control response",
    *         @OA\JsonContent(ref="#/components/schemas/Control_domain"),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */     
    public function search($uid)
    {
        $control = Control::find($id);
        if (is_null($control)) {
            return $this->sendError('control does not exist.');
        }
        return $this->sendResponse(new ControlResource($control), 'control fetched.');
    }


    /**
     * @OA\Put(
     *     path="/api/controls/{cid}",
     *     summary="Updates a control",
     *     @OA\Parameter(
     *         description="cid to update",
     *         in="path",
     *         name="cid",
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
     *                 @OA\Property(
     *                     property="control",
     *                     type="string"
     *                 ),
     *                 example={"control": "google.com"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     )
     * )
     */    
    public function update(Request $request, Control $control)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
          'uid' => 'required',
          'did' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());       
        }
        $control->uid = $input['uid'];
        $control->did = $input['did'];
        $control->save();
        
        return $this->sendResponse(new ControlResource($control), 'control updated.');
    }
    /**
     * @OA\Delete(
     *     path="/api/controls/{cid}",
     *     description="deletes a single control based on the lamg supplied",
     *     @OA\Parameter(
     *         description="lang of control to delete",
     *         in="path",
     *         name="cid",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="control deleted"
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\Schema(ref="#/components/schemas/ErrorModel")
     *     )
     * )
     */    
    public function destroy(Control $control)
    {
        $control->delete();
        return $this->sendResponse([], 'control deleted.');
    }
    
    /**
    * @OA\GET(
    *     path="/api/controls/set_lr/{cid}/{lr}",
    *     summary="Set control lr by cid",
    *     @OA\Parameter(
    *         description="Control to fetch",
    *         in="path",
    *         name="cid",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             format="int64",
    *         ),

    *       example=4    
    *     ),
    *     @OA\Parameter(
    *         description="lr",
    *         in="path",
    *         name="lr",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             format="int64",
    *         ), 
    *     ),       
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="control response",
    *         @OA\JsonContent(ref="#/components/schemas/Control_domain"),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */     
    public function set_cid_lr($cid, $lr)
    {
        $control = Control::find($cid);
        if (is_null($control)) {
            return $this->sendError('control does not exist.');
        }
        
        $control->update(['lr' => $lr]);        
        return $this->sendResponse(new ControlResource($control), 'control fetched.');
    }
    
    /**
    * @OA\GET(
    *     path="/api/controls/did_data/{uid}",
    *     summary="Get control by cid",
    *     @OA\Parameter(
    *         description="Control fetch by uid",
    *         in="path",
    *         name="uid",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             format="int64",
    *         ),
    * example=4    
    *     ),    
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="control response",
    *         @OA\JsonContent(ref="#/components/schemas/Control_domain"),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */  
    public function get_did_data(Request $request, $uid)
    {
      $input = $request->all();
      $did = null;
      $did_arr = null;
      if(isset($input['did'])) {
        $did = $input['did'];
      }
      
      if (is_array($input['domain'])) {
          $domain_arr = $input['domain'];
      } elseif (preg_match('/,/', $input['domain'])) {
          $domain_arr = explode(',', $input['domain']);
      } else {
          $domain_arr = [$input['domain']];
      }
      
      $domain = new Domain; 

      $i = 0;
      foreach ($domain_arr as $domain_name) {
          $domain = Domain::where('domain', $domain_name)->first();
          if ($domain->did) {
              $did_arr[] = $domain->did;
          }
      }
       
        $control = Control::where('uid', $uid)->orderBy('cid')
        ->when($did, function ($query, $did) {
                    return $query->where('did', $did);
                })
                ->when($did_arr, function ($query, $did_arr) {
                            return $query->wherein('did', $did_arr);
                        })                
        ->get();
        
        return $this->sendResponse(ControlResource::collection($control), 'control fetched.');
    }
  
}