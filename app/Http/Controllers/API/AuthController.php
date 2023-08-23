<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\User;
   
class AuthController extends BaseController
{
  /**
  * @OA\Post(
  *     path="/api/login",
  *     summary="Login user",
  *     tags={"Auth"},  
  *     @OA\RequestBody(
  *         @OA\MediaType(
  *             mediaType="application/json",
  *             @OA\Schema(
  *             required={"enail"},
  *             required={"password"},              
  *             @OA\Property(property="email", type="string"),
  *             @OA\Property(property="password", type="string"),
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
  *             @OA\Items(ref="#/components/schemas/User")
  *         ),
  *     ),
  *     security={ * {"sanctum": {}}, * },
  * )    
  */     
    public function signin(Request $request)
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $authUser = Auth::user(); 
            $success['token'] =  $authUser->createToken('MyAuthApp')->plainTextToken; 
            $success['name'] =  $authUser->name;
   
            return $this->sendResponse($success, 'User signed in');
        } 
        else{ 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 
    }

    /**
    * @OA\Post(
    *     path="/api/register",
    *     summary="Adds a new user",
    *     tags={"Auth"},     
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             required={"name"},
    *             required={"enail"},
    *             required={"name"},
    *             required={"password"},
    *             required={"confirm_password"},                    
    *             @OA\Property(property="name", type="string"),
    *             @OA\Property(property="email", type="string"),
    *             @OA\Property(property="password", type="string"),
    *             @OA\Property(property="confirm_password", type="string"),
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
    *             @OA\Items(ref="#/components/schemas/User")
    *         ),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )    
    */      
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Error validation', $validator->errors());       
        }
   
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyAuthApp')->plainTextToken;
        $success['name'] =  $user->name;
   
        return $this->sendResponse($success, 'User created successfully.');
    }
   
}