<?php

namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;

class BaseController extends Controller
{
    /**
     * @OA\Schema(
     *  schema="Result",
     *  title="Responce",
     * 	@OA\Property(
     * 		property="success",
     * 		type="string"
     * 	),
     * 	@OA\Property(
     * 		property="data",
     * 		type="object"
     * 	),
     * 	@OA\Property(
     * 		property="message",
     * 		type="string"
     * 	),
     *    example={
     *    "200": "Dpmain created",
     *    "501": "Domain not registered error",
     *    "502": "Domain too short error",
     *    "503": "Domain too long error",
     *    "504": "Domain should start from letter",
     *    "505": "Wrong domain error",
     *    "506": "Domain Incorect symbols error",
     *    "508": "Domain already exist", 
     *    "509": "Domain control already exist",                                 
     *     },     
     * )
     */
    public function sendResponse($result, $message)
    {
    	$response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];
        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
    	$response = [
            'success' => false,
            'message' => $error,
        ];
        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }
        return response()->json($response, $code);
    }
}