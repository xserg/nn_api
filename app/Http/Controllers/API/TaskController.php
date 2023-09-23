<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Validator;
use App\Models\Tasks as Task;
use App\Http\Resources\Main_tasks as TaskResource;
   
class TaskController extends BaseController
{
    /**
    * @OA\GET(
    *     path="/api/tasks",
    *     summary="Get tasks list",
    *     tags={"Tasks"},         
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="lr response",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/Task")
    *         ),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */    
    public function index()
    {
        $task = Task::get();
        return $this->sendResponse(TaskResource::collection($task), 'Task fetched.');
    }
    
    /**
    * @OA\GET(
    *     path="/api/tasks/{lr}",
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
    *     path="/api/tasks/search/{search}",
    *     summary="Get lr search",
    *     tags={"Tasks"},         
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