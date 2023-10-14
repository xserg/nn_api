<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Validator;
use App\Models\Requests as Requests;
use App\Http\Resources\Base_requests as RequestResource;
   
class RequestController extends BaseController
{    
    /**
    * @OA\Post(
    *     path="/api/requests/add",
    *     summary="Adds a new requests from array",
    *     description="Добавить новый запрос.
    
  Обязательное поле: requests array[].
  
  Пример: {
  requests: [
    запрос,
    поисковой,
    купить слона
  ]
}

Возвращает массив результатов: 

    {
      request: поисковой,
      rid: 13,
      status: 200,
    },

Возможные значения status:

    {
      200: Request created,
      502: Request too short error,
      503: Request too long error,
      506: Request Incorect symbols error,
      508: Request already exist  
    },

    ",       
    *     tags={"Requests"},    
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             @OA\Property(
    *               property="requests", type="array",
    *               @OA\Items( @OA\Property( type="string")), 
    *               example={
    *                      "запрос",
    *                      "поисковой",
    *                      "купить слона"
    *               },
    *             ),
    *             )
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/Request"),
    *         
    *         example={
    *  "success": true,
    *  "data": {
    *    {
    *      "request": "поисковой",
    *      "rid": 13,
    *      "status": 200,
    *    },
    *    "status_arr": {
    *    "200": "Request created",
    *    "502": "Request too short error",
    *    "503": "Request too long error",
    *    "506": "Request Incorect symbols error",
    *    "508": "Request already exist",                      
    *     },
    *  },
    *  "message": "Request processed"
    * }),
    *  
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */    
    public function store_arr(Request $request)
    {
      $input = $request->all();
      $validator = Validator::make($input, [
          'requests' => 'required',
      ]);
      if($validator->fails()){
          return $this->sendError($validator->errors());       
      }
      $requests = new Requests;              
      $i = 0;
      foreach ($input['requests'] as $request_name) {
          $status = $requests->store($request_name);
          //print_r(new DomainResource($domain));
          //if($domain->store($input['domain'], false)) {
          $did_arr[$i]['request_name'] = $request_name;
          $did_arr[$i]['rid'] = $requests->rid;
          $did_arr[$i]['status'] = $status;
          $i++;
      } 
      
      return $this->sendResponse($did_arr, 'Request processed');                
    }

    /**
    * @OA\GET(
    *     path="/api/requests/{rid}",
    *     summary="Get request by rid",
    *     tags={"Requests"},
    *     @OA\Parameter(
    *         description="rid to fetch",
    *         in="path",
    *         name="rid",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             format="int64",
    *         )
    *     ),    
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="domain response",
    *         @OA\JsonContent(ref="#/components/schemas/Request"),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */       
    public function show($id)
    {
        $request = Requests::find($id);
        if (is_null($request)) {
            return $this->sendError('request does not exist.');
        }
        return $this->sendResponse(new RequestResource($request), 'Request fetched.');
    }
    
    /**
    * @OA\Post(
    *     path="/api/requests/check",
    *     summary="Check request from array",
    *     tags={"Requests"},
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             @OA\Property(
    *               property="requests", type="array",
    *               @OA\Items( @OA\Property( type="string")), 
    *               example={
    *                      "запрос",
    *                      "поисковой",
    *                      "купить слона"
    *               },
    *             ),
    *             )
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="request response",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/Request"),
    *         
    *         example={
    *  "success": true,
    *  "data": {
    *    {
    *      "request": "поисковой",
    *      "status": 200,
    *    },
    *  },
    *  "message": "Request processed"
    * }),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */    
    public function check(Request $request)
    {
      $input = $request->all();
      $validator = Validator::make($input, [
          'requests' => 'required',
      ]);
      if($validator->fails()){
          return $this->sendError($validator->errors());       
      }
      
      if (is_array($input['requests'])) {
          $request_arr = $input['requests'];
      } elseif (preg_match('/,/', $input['requests'])) {
          $request_arr = explode(',', $input['requests']);
      } else {
          $request_arr = [$input['requests']];
      }
      
      $requests = new Requests;               
      $i = 0;
      foreach ($request_arr as $request_name) {
          //$domain->error = $domain->check_domain_name($domain_name, $check_exist);
          $did_arr[$i]['request'] = $request_name;
          $did_arr[$i]['status'] = $requests->check_request_name($request_name);
          $i++;
      } 
      
      return $this->sendResponse($did_arr, 'Request processed');                
    }

    /**
     * @OA\Put(
     *     path="/api/requests/{rid}",
     *     summary="Updates request",
     *     tags={"Requests"},    
     *     @OA\Parameter(
     *         description="rid to update",
     *         in="path",
     *         name="rid",
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
     *             required={"request"},              
     *          
     *             @OA\Property(property="request", type="string"),               
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     )
     * )
     */
        
    public function update(Request $request, $rid)
    {
        $input = $request->all();
    
        $requests = Requests::find($rid);
        if (is_null($requests)) {
            return $this->sendError('request does not exist.');
        }
        $requests->update(['request' => $input['request']]);  
        return $this->sendResponse(new RequestResource($requests), 'request updated.');
    }
    
    /**
     * @OA\Delete(
     *     path="/api/requests/{rid}",
     *     summary="deletes request by rid",     
     *     description="deletes a single request by rid",
     *     tags={"Requests"}, 
     *     @OA\Parameter(
     *         description="rid",
     *         in="path",
     *         name="rid",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),              
     *     @OA\Response(
     *         response=204,
     *         description="request deleted"
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\Schema(ref="#/components/schemas/ErrorModel")
     *     )
     * )
     */      
    public function destroy($rid)
    {      
        $request = Requests::find($rid);
        if (is_null($request)) {
            return $this->sendError('request does not exist.');
        }
        $request->delete();
        return $this->sendResponse([], 'Request deleted.');
    }
    
    /**
    * @OA\GET(
    *     path="/api/organic/data",
    *     summary="Get organic data",
    *     tags={"Requests"}, 
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */             
    function organic_data()
    {
      $output = [			
				'name' => [
					1=>'Яндекс',
					2=>'Google'
				],
				'html-icon' => [
					1=>'<i class="icon-base icon-base-yandex"></i>',
					2=>'<i class="icon-base icon-base-google"></i>'
				],
				'html-device-icon' => [
					1=>'<i class="fa fa-desktop" aria-hidden="true"></i>',
					2=>'<i class="fa fa-mobile" aria-hidden="true"></i>'
				],
			];
      return $this->sendResponse($output, 'organic_data.');
    }
  
    
}