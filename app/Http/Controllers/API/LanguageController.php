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
    * @OA\Post(
    *     path="/api/languages",
    *     summary="Adds a new language",
    *     tags={"Languages"},         
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             required={"lang"},
    *             @OA\Property(property="lang", type="string"),
    *             required={"name"},
    *             @OA\Property(property="name", type="string"),
    *             @OA\Property(property="aparser", type="string"),
    *             @OA\Property(property="three", type="string"),
    *             @OA\Property(property="rucaptcha", type="integer"),
    *             )
    *         )
    *    ),
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
    public function store(Request $request)
    {
        $input = $request->all();
        
        if ($input['lang'] && $language = Language::find($input['lang'])) {
            return $this->sendError('language already exist.'); 
        }
        
        $validator = Validator::make($input, [
            'lang' => 'required',
            'name' => 'required',
            'aparser' => 'required',
            'three' => 'required',
            'rucaptcha',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());       
        }
        
        $language = Language::create($input);
        $language = Language::find($input['lang']);
        //print_r($language);
        return $this->sendResponse(new LanguageResource($language), 'Language created.');
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

    /**
     * @OA\Put(
     *     path="/api/languages/{lang}",
     *     summary="Updates a language",
     *     tags={"Languages"},          
     *     @OA\Parameter(
     *         description="did to fetch",
     *         in="path",
     *         name="lang",
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
     *                     property="language",
     *                     type="string"
     *                 ),
     *                 example={"language": "google.com"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     )
     * )
     */    
    public function update(Request $request, Language $language)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
          'lang' => 'required',
          'name' => 'required',
          'aparser' => 'required',
          'three' => 'required',
          'rucaptcha',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());       
        }
        $language->lang = $input['lang'];
        $language->name = $input['name'];
        $language->aparser = $input['aparser'];
        $language->three = $input['three'];
        $language->rucaptcha = $input['rucaptcha'];
        $language->save();
        
        return $this->sendResponse(new LanguageResource($language), 'Language updated.');
    }
    /**
     * @OA\Delete(
     *     path="/api/languages/{lang}",
     *     description="deletes a single language based on the lamg supplied",
     *     tags={"Languages"},          
     *     @OA\Parameter(
     *         description="lang of language to delete",
     *         in="path",
     *         name="lang",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="language deleted"
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\Schema(ref="#/components/schemas/ErrorModel")
     *     )
     * )
     */    
    public function destroy(Language $language)
    {
        $language->delete();
        return $this->sendResponse([], 'Language deleted.');
    }
}