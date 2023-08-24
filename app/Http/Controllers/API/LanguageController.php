<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Validator;
use App\Models\Base_languages as Language;
use App\Http\Resources\Base_languages as LanguageResource;
   
class LanguageController extends BaseController
{
  
    /**
    * @OA\GET(
    *     path="/api/languages",
    *     summary="Get languages list",
    *     tags={"Languages"},     
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="language response",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/Language")
    *         ),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */    
    public function index()
    {
        $language = Language::orderBy('lang')->get();
        return $this->sendResponse(LanguageResource::collection($language), 'Language fetched.');
    }
    
    /**
    * @OA\GET(
    *     path="/api/languages/{lang}",
    *     summary="Get language by lang",
    *     tags={"Languages"},         
    *     @OA\Parameter(
    *         description="laтg to fetch",
    *         in="path",
    *         name="lang",
    *         required=true,
    *         @OA\Schema(
    *             type="string",
    *         )
    *     ),    
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="language response",
    *         @OA\JsonContent(ref="#/components/schemas/Language"),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */     
    public function show($id)
    {
        $language = Language::find($id);
        if (is_null($language)) {
            return $this->sendError('language does not exist.');
        }
        return $this->sendResponse(new LanguageResource($language), 'Language fetched.');
    }
}