<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Validator;
use App\Models\Control_domains as Control;
use App\Models\Domains as Domain;
use App\Http\Resources\Control_domains as ControlResource;
use App\Http\Resources\Base_domains as DomainResource;
use App\Models\Control_groups as Group;
use App\Http\Resources\Control_groups as GroupResource;
   
class ControlController extends BaseController
{
    
    /**
    * @OA\Post(
    *     path="/api/control/{uid}/add",
    *     summary="Adds a new control",
    *     tags={"Control"},
    *     @OA\Parameter(
    *         description="Add control for uid",
    *         in="path",
    *         name="uid",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             format="int64",
    *         ),
    *         example=3    
    *     ),     
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *  
    *             @OA\Property(
    *               property="domains", type="array",
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
    public function store(Request $request, $uid)
    {
        $input = $request->all();
        $input['uid'] = $uid;
        $validator = Validator::make($input, [
            'uid' => 'required',
            'domains' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());       
        }
        
        if (is_array($input['domains'])) {
            $domain_arr = $input['domains'];
        } elseif (preg_match('/,/', $input['domains'])) {
            $domain_arr = explode(',', $input['domains']);
        } else {
            $domain_arr = [$input['domains']];
        }
        
        $did_arr = [];
        $domain = new Domain; 

        $i = 0;
        foreach ($domain_arr as $domain_name) {
            $status = $domain->store($domain_name, false);
            //print_r(new DomainResource($domain));
            //if($domain->store($input['domain'], false)) {
            $did_arr[$i]['domain'] = $domain_name;
            $did_arr[$i]['did'] = $domain->did;
            $did_arr[$i]['status'] = $status;
            
            //}
            if ($domain->did) {
              if ($res = Control::where('uid', $input['uid'])->where('did', $domain->did)->first()) {
                  $did_arr[$i]['status'] = 509; //' Control domain exist for this user';
              } else {
                  $res = Control::create([
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
              $did_arr[$i]['cid'] = $res->cid;
            }
            $i++;
        } 
        
        return $this->sendResponse($did_arr, 'control created.');
    }
    
    /**
    * @OA\GET(
    *     path="/api/control/{cid}",
    *     summary="Get control by cid",
    *     tags={"Control"},     
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
     *     path="/api/control/{cid}",
     *     summary="Updates a control",
     *     tags={"Control"},    
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
     *     path="/api/control/{uid}/{cid}",
     *     description="deletes a single control ",
     *     tags={"Control"}, 
     *     @OA\Parameter(
     *         description="uid",
     *         in="path",
     *         name="uid",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64",
     *         ),
     *        example=3    
     *     ),
     *     @OA\Parameter(
     *         description="cid",
     *         in="path",
     *         name="cid",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64",
     *         ),
     *        example=0    
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
    public function destroy($uid, $cid)
    {
        $control = Control::where('uid', $uid)->where('cid', $cid)->first();
        if (is_null($control)) {
            return $this->sendError('control does not exist.');
        }
        $control->delete();
        return $this->sendResponse([], 'control deleted.');
    }
    
    /**
    * @OA\GET(
    *     path="/api/control/{uid}/{cid}/set_lr/{lr}",
    *     summary="Set control lr by uid, cid",
    *     tags={"Control"}, 
    *     @OA\Parameter(
    *         description="uid",
    *         in="path",
    *         name="uid",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             format="int64",
    *         ),
    *       example=4    
    *     ),        
    *     @OA\Parameter(
    *         description="cid",
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
    *       example=1        
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
    public function set_cid_lr($uid, $cid, $lr)
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
    *     path="/api/control/{uid}/{cid}",
    *     summary="Get control by uid",
    *     tags={"Control"},     
    *     @OA\Parameter(
    *         description="Control fetch by uid",
    *         in="path",
    *         name="uid",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             format="int64",
    *         ),
    *        example=3    
    *     ),
    *     @OA\Parameter(
    *         description="Control fetch by cid",
    *         in="path",
    *         name="cid",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             format="int64",
    *         ),
    *        example=0    
    *     ),    
    *     @OA\Parameter(
    *         description="did",
    *         in="query",
    *         name="did",
    *         @OA\Schema(
    *             type="string",
    *         ),
    * example=2    
    *     ),
    *     @OA\Parameter(
    *         description="domain",
    *         in="query",
    *         name="domain",
    *         @OA\Schema(
    *             type="string",
    *         ),
    *         example="site.com"    
    *     ),
    *     @OA\Parameter(
    *         description="select",
    *         in="query",
    *         name="select",
    *         @OA\Schema(
    *             type="string",
    *         ),
    *         example="host_id,hidden,pos_settings"    
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
    public function get_did_data(Request $request, $uid, $cid)
    {
      $input = $request->all();
      $did = null;
      $did_arr = null;
      $select = ['control_domains.*', 'control_groups.group_name', 'nn_base.base_domains.domain'];
      
      if(isset($input['did'])) {
        if (is_array($input['did'])) {
            $did_arr = $input['did'];
        } elseif (preg_match('/,/', $input['did'])) {
            $did_arr = explode(',', $input['did']);
        } else {
            $did_arr = [$input['did']];
        }
        //$did = $input['did'];
      }
      
      if(isset($input['select'])) {
        if (is_array($input['select'])) {
            $select = $input['select'];
        } elseif (preg_match('/,/', $input['select'])) {
            $select = explode(',', $input['select']);
        } else {
            $select = [$input['select']];
        }
        $select=array_merge(array_map('trim', $select), ['control_domains.uid', 'cid', 'control_domains.did', 'domain']);
      }
      
      if (!empty($input['domain'])) {
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
              $domain = Domain::where('domain', trim($domain_name))->first();
              if ($domain->did) {
                  $did_arr[] = $domain->did;
              }
          }
      } 
        
        $control = Control::select($select)
        ->where('control_domains.uid', $uid)
        ->leftJoin('nn_base.base_domains', 'control_domains.did', '=', 'base_domains.did')
        ->leftJoin('control_groups', 'control_domains.gid', '=', 'control_groups.gid')
        //->orderBy('cid')
        ->when($did, function ($query, $did) {
                    return $query->where('control_domains.did', $did);
                })
        ->when($did_arr, function ($query, $did_arr) {
                    return $query->wherein('control_domains.did', $did_arr);
                })
        ->when($cid, function ($query, $cid) {
                    return $query->where('cid', $cid);
                })                                 
        ->get();
        
        return $this->sendResponse(ControlResource::collection($control), 'control fetched.');
    }
    
    
    /**
    * @OA\GET(
    *     path="/api/control/{uid}/groups",
    *     summary="Get groups  list",
    *     tags={"Control"},     
    *     @OA\Parameter(
    *         description="Groups fetch by uid",
    *         in="path",
    *         name="uid",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             format="int64",
    *         ),
    *      example=3   
    *     ),
    *     @OA\Parameter(
    *         description="gid",
    *         in="query",
    *         name="gid",
    *         @OA\Schema(
    *             type="string",
    *         ),
    *         example="1,3"  
    *     ),        
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
    public function get_group_name(Request $request, $uid)
    {
        $input = $request->all();
        $gid_arr = null;
        
        if(isset($input['gid'])) {
          if (is_array($input['gid'])) {
              $gid_arr = $input['gid'];
          } elseif (preg_match('/,/', $input['gid'])) {
              $gid_arr = explode(',', $input['gid']);
          } else {
              $gid_arr = [$input['gid']];
          }
        }
        
          $groups = Group::where('uid', $uid)
          ->when($gid_arr, function ($query, $gid_arr) {
                      return $query->wherein('gid', $gid_arr);
                  })                             
          ->get();
          return $this->sendResponse(GroupResource::collection($groups), 'group fetched.');        
    }
  
}