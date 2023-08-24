<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Validator;
use App\Models\Base_domains as Domain;
use App\Http\Resources\Base_domains as DomainResource;

/**
* @OA\Info(
*     version="1.0",
*     title="API Swagger documentation"
* )
*/   
class DomainController extends BaseController
{
    private $error;

    /**
    * @OA\GET(
    *     path="/api/domains",
    *     summary="Get domains list",
    *     tags={"Domains"},    
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="domain response",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/Domain")
    *         ),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */    
    public function index()
    {
        $domain = Domain::orderBy('did')->get();
        return $this->sendResponse(DomainResource::collection($domain), 'Domains fetched.');
    }
    
    /**
    * @OA\Post(
    *     path="/api/domains/add",
    *     summary="Adds a new domains from array",
    *     tags={"Domains"},    
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
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
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="domain response",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/Domain"),
    *         
    *         example={
    *  "success": true,
    *  "data": {
    *    {
    *      "domain": "google.com",
    *      "did": 13,
    *      "error": "Domain already exist."
    *    },
    *    {
    *      "domain": "yandex.ru",
    *      "did": 18,
    *      "error": "Domain already exist."
    *    },
    *    {
    *      "domain": "site1.net",
    *      "did": 37,
    *      "error": "Domain already exist."
    *    }
    *  },
    *  "message": "Request processed"
    * }),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */    
    public function store_arr(Request $request)
    {
      $input = $request->all();
      $validator = Validator::make($input, [
          'domains' => 'required',
      ]);
      if($validator->fails()){
          return $this->sendError($validator->errors());       
      }
      $domain = new Domain;              
      $i = 0;
      foreach ($input['domains'] as $domain_name) {
          $domain->store($domain_name, false);
          //print_r(new DomainResource($domain));
          //if($domain->store($input['domain'], false)) {
          $did_arr[$i]['domain'] = $domain_name;
          $did_arr[$i]['did'] = $domain->did;
          $did_arr[$i]['error'] = $domain->error;
          $i++;
      } 
      
      return $this->sendResponse($did_arr, 'Request processed');                
    }

    /**
    * @OA\GET(
    *     path="/api/domains/{did}",
    *     summary="Get domain by did",
    *     tags={"Domains"},
    *     @OA\Parameter(
    *         description="did to fetch",
    *         in="path",
    *         name="did",
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
    *         @OA\JsonContent(ref="#/components/schemas/Domain"),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */       
    public function show($id)
    {
        $domain = Domain::find($id);
        if (is_null($domain)) {
            return $this->sendError('Domain does not exist.');
        }
        return $this->sendResponse(new DomainResource($domain), 'Domain fetched.');
    }
    
    /**
    * @OA\Post(
    *     path="/api/domains/check",
    *     summary="Check domains from array",
    *     tags={"Domains"},
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             @OA\Property(property="check_exist", type="bool", example=false),
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
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="domain response",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/Domain"),
    *         
    *         example={
    *  "success": true,
    *  "data": {
    *    {
    *      "domain": "google.com",
    *      "did": 13,
    *      "error": "Domain already exist."
    *    },
    *    {
    *      "domain": "yandex.ru",
    *      "did": 18,
    *      "error": "Domain already exist."
    *    },
    *    {
    *      "domain": "site1.net",
    *      "did": 37,
    *      "error": "Domain already exist."
    *    }
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
          'domain' => 'required',
      ]);
      if($validator->fails()){
          return $this->sendError($validator->errors());       
      }
      $check_exist = $input['check_exist'] ?? false;
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
          //$domain->error = $domain->check_domain_name($domain_name, $check_exist);
          $did_arr[$i]['domain'] = $domain_name;
          $did_arr[$i]['check'] = $domain->check_domain_name($domain_name, $check_exist);
          $did_arr[$i]['error'] = $domain->error;
          $i++;
      } 
      
      return $this->sendResponse($did_arr, 'Request processed');                
    }

    /**
    * @OA\Post(
    *     path="/api/domains/get_did",
    *     summary="Adds a new domains from array",
    *     tags={"Domains"},    
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
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
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="domain response",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/Domain"),
    *         
    *         example={
    *            "success": true,
    *            "data": 
    *            {
    *             {
    *                "domain": "ya.ru",
    *                "did": 19
    *             },
    *             {
    *                "domain": "11111",
    *                "did": false
    *             }
    *           },
    *          "message": "Request processed"
    *      }),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */       
    public function get_did(Request $request)
    {
      $input = $request->all();
      $validator = Validator::make($input, [
          'domain' => 'required',
      ]);
      if($validator->fails()){
          return $this->sendError($validator->errors());       
      }
      $domain_arr = array_map('trim', $input['domain']);   
      foreach ($domain_arr as $domain_name) {
          $domain = Domain::where('domain', $domain_name)->first();
          $did_arr[] = ['domain' => $domain_name, 'did' =>  $domain->did ?? false];
      }       
      return $this->sendResponse($did_arr, 'Got did');            
    }
    
    /**
    * @OA\Post(
    *     path="/api/domains/get_domain",
    *     summary="Get domain domains by did array",
    *     tags={"Domains"},    
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             @OA\Property(
    *               property="did", type="array",
    *               @OA\Items( @OA\Property( type="string")), 
    *               example={
    *                      11,
    *                      12,
    *                      13,
    *               },
    *             ),
    *             )
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="domain response",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/Domain"),
    *         
    *         example={
    *            "success": true,
    *            "data": 
    *            {
    *             {
    *                "domain": "ya.ru",
    *                "did": 19
    *             },
    *             {
    *                "domain": false,
    *                "did": 12
    *             }
    *           },
    *          "message": "Request processed"
    *      }),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */       
    public function get_domain(Request $request)
    {
      $input = $request->all();
      $validator = Validator::make($input, [
          'did' => 'required',
      ]);
      if($validator->fails()){
          return $this->sendError($validator->errors());       
      }
      $did_arr = array_map('trim', $input['did']);   
      foreach ($did_arr as $did) {
          $domain = Domain::find($did);
          $domain_arr[] = ['domain' => $domain->domain ?? false, 'did' =>  $did];
      }       
      return $this->sendResponse($domain_arr, 'Got domain');            
    }
}