<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Validator;
use App\Models\Base_lr as Lr;
use App\Http\Resources\Base_lr as LrResource;
   
class LrController extends BaseController
{
    /**
    * @OA\GET(
    *     path="/api/lrs",
    *     summary="Get lrs list",
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
    public function index()
    {
        $lr = Lr::orderBy('lr')->get();
        return $this->sendResponse(LrResource::collection($lr), 'Lr fetched.');
    }
    /**
    * @OA\Post(
    *     path="/api/lrs",
    *     summary="Adds a new lr",
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             required={"lr"},
    *             @OA\Property(property="lr", type="integer"),
    *             required={"parent_id"},
    *             @OA\Property(property="parent_id", type="integer"),    
    *             required={"name"},
    *             @OA\Property(property="name", type="string"),
    *             @OA\Property(property="sort", type="integer"),
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
            'name' => 'required',
            'parent_id' => 'required',
            'sort',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());       
        }
        $lr = Lr::create($input);
        return $this->sendResponse(new LrResource($lr), 'Lr created.');
    }

    
    /**
    * @OA\GET(
    *     path="/api/lrs/{lr}",
    *     summary="Get location by lr",
    *     @OA\Parameter(
    *         description="lr to fetch",
    *         in="path",
    *         name="lr",
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
    *         @OA\JsonContent(ref="#/components/schemas/Location"),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */        
    public function show($id)
    {
        $domain = Lr::find($id);
        if (is_null($domain)) {
            return $this->sendError('Lr does not exist.');
        }
        return $this->sendResponse(new LrResource($domain), 'Lr fetched.');
    }
    
        /**
         * @OA\Put(
         *     path="/api/lrs/{lr}",
         *     summary="Updates a lr",
         *     @OA\Parameter(
         *         description="lr to fetch",
         *         in="path",
         *         name="lr",
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
         *             required={"lr"},
         *             @OA\Property(property="lr", type="integer"),
         *             required={"parent_id"},
         *             @OA\Property(property="parent_id", type="integer"),    
         *             required={"name"},
         *             @OA\Property(property="name", type="string"),
         *             @OA\Property(property="sort", type="integer"),
         *             )
         *         )
         *    ),
         *     @OA\Response(
         *         response=200,
         *         description="OK"
         *     )
         * )
         */      
    public function update(Request $request, Lr $domain)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'lr' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());       
        }
        $domain->domain = $input['domain'];
        $domain->save();
        
        return $this->sendResponse(new LrResource($domain), 'Lr updated.');
    }

    /**
     * @OA\Delete(
     *     path="/api/lrs/{lr}",
     *     description="deletes a single lr based on the lamg supplied",
     *     @OA\Parameter(
     *         description="lang of lr to delete",
     *         in="path",
     *         name="lr",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="lr deleted"
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\Schema(ref="#/components/schemas/ErrorModel")
     *     )
     * )
     */      
    public function destroy(Lr $domain)
    {
        $domain->delete();
        return $this->sendResponse([], 'Lr deleted.');
    }
}