<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\User;
   
class AuthController extends BaseController
{ 
  
  private  $access_list = [
            'domain-add', 'domain-get', 'domain-delete', 'domain-update',
            'control-add', 'control-get', 'control-delete', 'control-update',
            'language-add', 'language-get', 'language-delete', 'language-update',
            'lr-add', 'lr-get', 'lr-delete', 'lr-update',
            'setting-add', 'setting-get', 'setting-delete', 'setting-update',
          ];
        
    /**
    * @OA\Post(
    *     path="/api/token/add",
    *     summary="Add new access token to user",
    *     tags={"Auth"},  
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             required={"enail"},
    *             required={"password"},              
    *             @OA\Property(property="email", type="string"),
    *             @OA\Property(property="password", type="string"),
    *             @OA\Property(property="access", type="array",
    *               @OA\Items( @OA\Property( type="string")), 
    *               example={
    *               "domain-add", "domain-get", "domain-delete", "domain-update",
    *               "control-add", "control-get", "control-delete", "control-update",
    *               "language-add", "language-get", "language-delete", "language-update",
    *               "lr-add", "lr-get", "lr-delete", "lr-update",
    *               "setting-add", "setting-get", "setting-delete", "setting-update",
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
    *             @OA\Items(ref="#/components/schemas/User")
    *         ),
    *     ),
    * )    
    */     
    public function signin(Request $request)
    {
      $validator = Validator::make($request->all(), [
          'email' => 'required|email',
          'password' => 'required',
          'access' => 'required',
      ]);
 
      if($validator->fails()){
          return $this->sendError('Error validation', $validator->errors());       
      }      
      if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
          $authUser = Auth::user(); 
          $success['token'] =  $authUser->createToken('MyAuthApp', $request->access)->plainTextToken; 
          $success['name'] =  $authUser->name;
 
          return $this->sendResponse($success, 'Token created');
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
        $success['token'] =  $user->createToken('MyAuthApp', ["*"])->plainTextToken;
        $success['name'] =  $user->name;
   
        return $this->sendResponse($success, 'User created successfully.');
    }

    /**
    * @OA\Delete(
    *     path="/api/token/delete",
    *     summary="Delete access token by id",
    *     tags={"Auth"},  
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             required={"enail"},
    *             required={"password"},  
    *             required={"token_id"},                 
    *             @OA\Property(property="email", type="string"),
    *             @OA\Property(property="password", type="string"),
    *             @OA\Property(property="token_id", type="integer")
    *         ))
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
    * )    
    */    
    public function delete_token(Request $request) 
    {
      $validator = Validator::make($request->all(), [
          'email' => 'required|email',
          'password' => 'required',
          'token_id' => 'required',
      ]);
 
      if($validator->fails()){
          return $this->sendError('Error validation', $validator->errors());       
      }      
      if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
          $authUser = Auth::user();
          $token = $authUser->tokens()->find($request->token_id);
          if (is_null($token)) {
              return $this->sendError('Token does not exist.');
          }        
          $token->delete();
          return $this->sendResponse([], 'Token deleted');
      } 
      else{ 
          return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
      }       
    }

    /**
    * @OA\Post(
    *     path="/api/token/update",
    *     summary="Update access token by token_id",
    *     tags={"Auth"},  
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *             required={"enail"},
    *             required={"password"}, 
    *             required={"token_id"},                  
    *             @OA\Property(property="email", type="string"),
    *             @OA\Property(property="password", type="string"),
    *             @OA\Property(property="token_id", type="integer"),    
    *             @OA\Property(property="access", type="array",
    *               @OA\Items( @OA\Property( type="string")), 
    *               example={
    *               "domain-add", "domain-get", "domain-delete", "domain-update",
    *               "control-add", "control-get", "control-delete", "control-update",
    *               "language-add", "language-get", "language-delete", "language-update",
    *               "lr-add", "lr-get", "lr-delete", "lr-update",
    *               "setting-add", "setting-get", "setting-delete", "setting-update",
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
    *             @OA\Items(ref="#/components/schemas/User")
    *         ),
    *     ),
    * )    
    */     
    public function update_token(Request $request) 
    {
      $validator = Validator::make($request->all(), [
          'email' => 'required|email',
          'password' => 'required',
          'token_id' => 'required',
          'access' => 'required',
      ]);
 
      if($validator->fails()){
          return $this->sendError('Error validation', $validator->errors());       
      }      
      if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
          $authUser = Auth::user();
          $token = $authUser->tokens()->find($request->token_id);
          if (is_null($token)) {
              return $this->sendError('Token does not exist.');
          }
          $token->abilities = $request->access;
          $token->save();
          return $this->sendResponse([], 'Token updated');
      } 
      else{ 
          return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
      }       
    }
   
}