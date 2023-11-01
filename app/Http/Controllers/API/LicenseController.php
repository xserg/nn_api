<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Validator;
use App\Models\Data;
use App\Models\Keys;
use App\Models\Pins;
use App\Models\Domains as Domain;
use App\Http\Resources\Plugins_data as DataResource;
use App\Http\Resources\Plugins_keys as KeysResource;
use App\Http\Resources\Plugins_pins as PinsResource;
use Illuminate\Support\Facades\Http;
use DB;

class LicenseController extends BaseController
{
    /**
    * @OA\GET(
    *     path="/api/wordpress/get_plugins",
    *     summary="Get plugins data list",
    *     tags={"WordPress"},
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="data response",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/Data")
    *         ),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */
    public function index()
    {
        $data = Data::get();
        return $this->sendResponse(DataResource::collection($data), 'Data fetched.');
    }

    /**
    * @OA\Post(
    *     path="/api/wordpress/add_license",
    *     summary="Adds a new license",
    *     tags={"WordPress"},
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="multipart/form-data",
    *             @OA\Schema(
    *             required={"plugin"},
    *             @OA\Property(property="plugin", type="string", example="syncer"),
    *             required={"domain"},
    *             @OA\Property(property="domain", type="string", example="dev1.nn_api.test"),
    *             @OA\Property(property="uid", type="integer", example="0", default=0),
    *             )
    *         )
    *    ),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="key response",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/Keys")
    *         ),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */
    public function add_license(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            //'uid' => 'required',
            'plugin' => 'required',
            'domain' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $data = Data::where('ident', $input['plugin'])->first();
        if (is_null($data)) {
            return $this->sendError('plugin does not exist.');
        }
        $domain = new Domain;
        $check = $domain->check_domain_name($input['domain']);
        if ($check != 200) {
            return $this->sendError('Error domain ' . $check);
        }
        $key = $this->generate_license_key($input['plugin']);
        //echo "key: " . $key;
        $input['db_key'] = $this->xor_encode(env('ENCRYPT_KEY', 'forge'), $key);
        $input['pid'] = $data->toArray()['pid'];
        unset($input['plugin']);
        //$keys = new Keys;
        $keys = Keys::create($input);
        $out = new KeysResource($keys);
        $out['key'] = $key;
        return $this->sendResponse($out, 'Licence created.');
    }

    /**
  	 * Сгенерировать лицензионный ключ
  	 *
  	 * @param string $ident
  	 * @return string
  	 */
  	private function generate_license_key($ident = '')
  	{
  		$key="$ident";
      $num=25;
      $rnd="";
  		$max=71;
      $symbol = [
        '0','1','2','3','4','5','6','7','8','9',
        'q','Q','w','W','e','E','r','R','t','T','y','Y','u','U','i','I','o','O','p','P',
        'a','A','s','S','d','D','f','F','g','G','h','H','j','J','k','K','l','L','z','Z',
        'x','X','c','C','v','V','b','B','n','N','m','M','0',
        '1','2','3','4','5','6','7','8','9'
      ];
  		for ($a = 0; $a < $num; $a++) {
        $rnd = $symbol[rand(0, $max)];
        $key = $key . $rnd;
      }
  		return $key;
  	}

    /**
     * XOR Шифрование
     */
    private function xor_encode($key, $text)
    {
      $i=0;
      $encrypted = '';
      foreach (str_split($text) as $char) {
        $encrypted .= chr(ord($char) ^ ord($key[$i++ % strlen($key)]));
      }
      return base64_encode($encrypted);
    }

    private function xor_decode($key, $text)
    {
      $text=base64_decode($text);
      $i=0; $encrypted = '';
      foreach (str_split($text) as $char) {
        $encrypted .= chr(ord($char) ^ ord($key[$i++ % strlen($key)]));
      }
      return $encrypted;
    }

    private function get_license($key)
    {
      $db_key = $this->xor_encode(env('ENCRYPT_KEY', 'forge'), $key);
      $select = [
        'nn_plugins.plugins_data.ident',
        'nn_plugins.plugins_data.name',
        'nn_plugins.plugins_keys.kid',
        'nn_plugins.plugins_keys.uid',
        'nn_plugins.plugins_keys.domain',
        'nn_plugins.plugins_keys.status',
      ];
      $keys = Keys::select($select)->selectRaw(
        'UNIX_TIMESTAMP(nn_plugins.plugins_keys.last_time) as lasttime, UNIX_TIMESTAMP() AS unix_time')
      ->leftJoin('nn_plugins.plugins_data', 'nn_plugins.plugins_data.pid', '=', 'nn_plugins.plugins_keys.pid')
      ->where('db_key', $db_key)->first();
      if (is_null($keys)) {
          return false;
      }
      return $keys->toArray();
    }

    /**
    * @OA\Post(
    *     path="/api/wordpress/check_license",
    *     summary="Check license",
    *     tags={"WordPress"},
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="multipart/form-data",
    *             @OA\Schema(
    *             required={"plugin", "key", "version", "callback"},
    *             @OA\Property(property="plugin", type="string", example="syncer"),
    *             @OA\Property(property="key", type="string", example="syncer4PVaqi2OkIO57EqF9dKcAOBGJ"),
    *             @OA\Property(property="version", type="string", example="23.06.06"),
    *             @OA\Property(property="callback", type="string", example="http://nn_api.test/api/wordpress/callback"),
    *             )
    *         )
    *    ),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="key response",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/Keys")
    *         ),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */
    public function check_license(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'plugin' => 'required',
            'key' => ['required', 'regex:/^[A-z0-9_\-]{25,50}$/'],
            'version' => 'required',
            'callback' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $domain = new Domain;
        $domain_name = parse_url($input['callback'], PHP_URL_HOST);
        $check = $domain->check_domain_name($domain_name);
        if ($check != 200) {
            return $this->sendError('Error callback ' . $check);
        }
        $data = Data::where('ident', $input['plugin'])->first();
        if (is_null($data)) {
            return $this->sendError('plugin does not exist.');
        }

        if (!$license_data =$this->get_license($input['key'])) {
            return $this->sendError('key does not exist.');
        }
        $version_sql = (int)str_replace(".", "", $input['version']);
        if ($license_data['ident'] != $input['plugin']) {
            return $this->sendError('key does not match plugin.');
        }
        if ($license_data['status'] == 2) {
            return $this->sendError('plugin blocked.');
        }
        $output['status'] = $license_data['status'];

        if ($license_data['unix_time'] - $license_data['lasttime'] > 600) {
          if (in_array($_SERVER['REMOTE_ADDR'], explode(',', env('ADMIN_IP')))) {
            $output['ignore'] = true;
          } else {
            $res = $this->check_hash($input['key'], $input['callback'], $license_data['name'], $version_sql);
            if (!$res) {
              return $this->sendError('hash error.');
            }
            $output['hash'] = true;
          }
        }
        return $this->sendResponse($output, 'Licence checked');

    }

    public function check_hash($key, $callback, $name, $version = null)
    {
        if (!$key || !$callback) {
          return false;
        }
        $db_key = $this->xor_encode(env('ENCRYPT_KEY', 'forge'), $key);
    		$hash = $this->get_hash($key, $callback);
        $url = $callback.'?action='.$name.'_license';
        $res = Http::get($url)->body();
        $res=json_decode($res, true);
    		if (!$res || strcmp($res['hash'], $hash) != 0) {
            return false;
        }
        $keys = Keys::where('db_key', $db_key)->first();
        $data = ['callback' => $callback];
        if ($version) {
            $data['version'] = $version;
        }
        $keys->update($data);
        return true;
    }

    /**
     * Вернет HASH
     *
     * @param string $key = "syncerF1rd82g4CCYM8uOQ0m0DPk53r"
     * @param string $callback = "https://1.dev.test/wp-admin/admin-ajax.php"
     * @return string
     */
    public function get_hash(string $key, string $callback)
    {
      $hash = md5( md5($key).$callback );
      return $hash;
    }

    /**
    * @OA\GET(
    *     path="/api/wordpress/callback",
    *     summary="Plugin callback mock",
    *         description="action",
    *     tags={"WordPress"},
    *     @OA\Parameter(
    *         in="query",
    *         name="action",
    *         @OA\Schema( type="string" )
    *     ),
    *     @OA\Parameter(
    *         in="query",
    *         name="key",
    *         required=true,
    *         @OA\Schema( type="string" )
    *     ),
    *     @OA\Parameter(
    *         in="query",
    *         name="callback",
    *         required=true,
    *         @OA\Schema( type="string" )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="callback response",
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */
    public function callback(Request $request)
    {
        $input = $request->all();
        //$input['key'] = 'syncer1Am5tK0sxv3zSdagLsZ0oV7oZ';
        //$input['callback'] = 'http://nn_api.test/api/wordpress/callback';
        $res = ['hash' => $this->get_hash($input['key'], $input['callback'])];
        return json_encode($res);
    }

    /**
    * @OA\Post(
    *     path="/api/wordpress/syncer/add_site",
    *     summary="Adds a new site",
    *     tags={"WordPress"},
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="multipart/form-data",
    *             @OA\Schema(
    *             required={"pin"},
    *             @OA\Property(property="pin", type="string" , example="348461"),
    *             required={"domain"},
    *             @OA\Property(property="domain", type="string", example="1.dev.test"),
    *             )
    *         )
    *    ),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="key response",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/Keys")
    *         ),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */
    public function add_site(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            //'uid' => 'required',
            'pin' => 'required',
            'domain' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $domain = new Domain;
        $check = $domain->check_domain_name($input['domain']);
        if ($check != 200) {
            return $this->sendError('Error domain ' . $check);
        }

        $select = ['data.name', 'keys.callback', 'keys.db_key', 'keys.status'];

        $keys = Keys::from('nn_plugins.plugins_keys as keys')->select($select)->selectRaw(
          'UNIX_TIMESTAMP(pins.expired_time) AS expired_time, UNIX_TIMESTAMP() AS currenttime')
        ->leftJoin('nn_plugins.plugins_data as data', 'data.pid', '=', 'keys.pid')
        ->leftJoin('nn_plugins.plugins_pins as pins', 'pins.kid', '=', 'keys.kid')
        ->where('pins.pin', $input['pin'])
        ->where('keys.domain', $input['domain'])->first();

        if (is_null($keys)) {
            return $this->sendError('pin does not exist.');
        }
        $license_data = $keys->toArray();
    		if ($license_data['currenttime'] >= $license_data['expired_time']) {
            return $this->sendError('pin expired, get new pin.');
        }
    		if ((int)$license_data['status'] !=1 ) {
            return $this->sendError('pin is not active.');
        }
        $key = $this->xor_decode(env('ENCRYPT_KEY', 'forge'), $license_data['db_key']);
    		$api_url=$license_data['callback']."?action=nn_syncer";
    		$res = $this->check_hash($key, $license_data['callback'], $license_data['name']);
        if (!$res) {
          return $this->sendError('hash error.');
        }
    		$output=['success'=>true, 'url'=>$api_url, 'domain'=>$input['domain']];
        return $this->sendResponse($output, 'Added site');

    }

    /**
    * @OA\Post(
    *     path="/api/wordpress/syncer/update_pin",
    *     summary="Update pin",
    *     tags={"WordPress"},
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="multipart/form-data",
    *             @OA\Schema(
    *             required={"key"},
    *             @OA\Property(property="key", type="string", example="syncer1Am5tK0sxv3zSdagLsZ0oV7oZ"),
    *             @OA\Property(property="plugin", type="string", example="syncer"),
    *             )
    *         )
    *    ),
    *     @OA\Response(
    *         response=200,
    *         description="OK",
    *         response=200,
    *         description="key response",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(ref="#/components/schemas/Keys")
    *         ),
    *     ),
    *     security={ * {"sanctum": {}}, * },
    * )
    */
    public function update_pin(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'key' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        if (!$license_data =$this->get_license($input['key'])) {
            return $this->sendError('key does not exist.');
        }

        $r = new \Random\Randomizer();
        $pin=$r->getInt(100000, 999999);

        $pins = Pins::selectRaw(
          "kid, pin, expired_time, UNIX_TIMESTAMP(CONVERT_TZ(expired_time, '+00:00', 'SYSTEM')) AS expiredtime, UNIX_TIMESTAMP() AS currenttime"
          )
          ->where('kid', $license_data['kid'])->first();
        if ($pins ) {
          $pins_data = $pins->toArray();
          if ($pins_data['currenttime'] < $pins_data['expiredtime']) {
            $output=[
              'success'=>true,
              'pin' => $pins_data['pin'],
              'current_time' => date('Y-m-d H:i:s', $pins_data['currenttime']),
              'expired_time' => date('Y-m-d H:i:s', $pins_data['expiredtime']),
              'already'=>true
            ];
            return $this->sendResponse($output, 'Pin ok');
          }
        }
        $pins = Pins::selectRaw('UNIX_TIMESTAMP() AS currenttime')->first();
        $expired_time = date('Y-m-d H:i:s', $pins->toArray()['currenttime'] + env('EXPIRED_PIN_SECONDS', '300'));
        $pins = Pins::updateOrInsert(['kid' => $license_data['kid']], ['pin' => $pin, 'expired_time' => $expired_time]);

        $output=[
          'success'=>true,
          'pin'=>$pin,
          'current_time' => date('Y-m-d H:i:s'),
          'expired_time'=>$expired_time
        ];
        return $this->sendResponse($output, 'Pin updated');
    }

}
