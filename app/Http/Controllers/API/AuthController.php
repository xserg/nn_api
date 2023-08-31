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
        /*
        $access_list = [
          'domain-add', 'domain-get', 'domain-delete', 'domain-update',
          'control-add', 'control-get', 'control-delete', 'control-update',
          'language-add', 'language-get', 'language-delete', 'language-update',
          'lr-add', 'lr-get', 'lr-delete', 'lr-update',
          'setting-add', 'setting-get', 'setting-delete', 'setting-update',
        ];
        */
        $success['token'] =  $user->createToken('MyAuthApp', $access_list)->plainTextToken;
        $success['name'] =  $user->name;
   
        return $this->sendResponse($success, 'User created successfully.');
    }
   
}