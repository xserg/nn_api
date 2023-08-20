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