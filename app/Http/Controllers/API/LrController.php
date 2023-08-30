<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Validator;
use App\Models\Lr as Lr;
use App\Http\Resources\Base_lr as LrResource;
   
class LrController extends BaseController
{
    /**
    * @OA\GET(
    *     path="/api/lrs",
    *     summary="Get lrs list",
    *     tags={"Lr"},         
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
    * @OA\GET(
    *     path="/api/lrs/{lr}",
    *     summary="Get location by lr",
    *     tags={"Lr"}, 
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
        $lr = Lr::find($id);
        if (is_null($lr)) {
            return $this->sendError('Lr does not exist.');
        }
        return $this->sendResponse(new LrResource($lr), 'Lr fetched.');
    }
    
    /**
    * @OA\GET(
    *     path="/api/lrs/search/{search}",
    *     summary="Get lr search",
    *     tags={"Lr"},         
    *     @OA\Parameter(
    *         description="search",
    *         in="path",
    *         name="search",
    *         required=true,
    *         @OA\Schema(
    *             type="string",
    *             example="Мос",
    *         ),
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
    public function search($search)
    {
        $lr = Lr::where('name', 'like', $search . '%')->get();
        return $this->sendResponse(LrResource::collection($lr), 'Lrs fetched.');
    }
}