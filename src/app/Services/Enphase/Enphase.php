<?php

namespace App\Services\Enphase;

use Log;
use Http;
use Cache;

/**
 * The Enphase class for communicating to the envoy
 */

class Enphase {

    protected $token;
    protected $serial;
    protected $config;
    protected $host;
    protected $backbone;

    public function __construct($config)
    {
        $this->config = $config;
        $this->host = $config['host'];
        $this->backbone = $this->getBackbone();
        $this->token = $this->getToken();
    }

    protected function getProtocol(){
        $protocol = "https";
        if(! $this->config['ssl']){
            $protocol = "http";
        }
        return $protocol;
    }

    /**
     * The primary get method
     */

     protected function get($path, $params = [], $cookies = [], $headers = [], $timeout=15){
        $path = ltrim($path, "/");
        $url = "{$this->getProtocol()}://{$this->host}/{$path}";

        $cookies = array_merge([
            "sessionId" => $this->getSession(true)
        ], $cookies);

        $response = Http::timeout($timeout)
            ->withHeaders($headers)
            ->withCookies($cookies, $this->host)
            ->withOptions(['verify' => false])
            ->get($url, $params);

        if($response->status() === 401){
            $cookies = [
                "sessionId" => $this->getSession(true)
            ];

            $response = Http::timeout($timeout)
                ->withHeaders($headers)
                ->withCookies($cookies, $this->host)
                ->withOptions(['verify' => false])
                ->get($url, $params);

            if(!$response->ok()){
                throw new \Exception("Envoy returns {$response->status()} after refreshing session");
            }

        }

        return $response;
    }

    function getEntrezSession(){

        $formData = [
            "username" => $this->config['email'],
            "password" => $this->config['password'],
            "authFlow" => "entrezSession",
            "codeChallenge" => null,
            "redirectUri" => null,
            "client" => null,
            "clientId" => null,
            "serialNum" => null,
            "grantType" => null,
            "state" => null,
        ];

        $headers = [
            "accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "referer" => "https://entrez.enphaseenergy.com/login_main_page",
        ];

        //cache the session for 1 hour
        return Cache::remember("entrez_session", 60 * 60, function() use ($formData, $headers){

            Log::info("fetching new session from https://entrez.enphaseenergy.com/login");
            $response = Http::withHeaders($headers)->asForm()->post('https://entrez.enphaseenergy.com/login', $formData);

            if($response->ok()){
                return $response->cookies()->getCookieByName('SESSION')->getValue();
            }

            throw new \Exception("Login into https://entrez.enphaseenergy.com/login failed with your username and password provided");

        });
    }

    public function getToken($refresh = false){

        if($refresh){
            Cache::forget('entrez_token');
        }

        if (Cache::has('entrez_token')){
            return Cache::get('entrez_token');
        }

        $session = $this->getEntrezSession();
        $formData = [
            "uncommissioned" => "on",
            "Site" => $this->config['site'],
            "serialNum" => $this->backbone['serial'],
        ];

        $cookies = [
            "SESSION" => $session,
        ];

        Log::info("fetching new token from https://entrez.enphaseenergy.com/entrez_tokens");
        $response = Http::asForm()->withCookies($cookies, "entrez.enphaseenergy.com")->post("https://entrez.enphaseenergy.com/entrez_tokens", $formData);

        if($response->ok()){
            if(strpos($response->body(), '<textarea name="accessToken" id="JWTToken" cols="30" rows="10" >') === false){
                throw new Exception("no token was found in entrez response, likely incorrect login details provided!");
            }

            $token = get_string_between($response->body(), '<textarea name="accessToken" id="JWTToken" cols="30" rows="10" >', '</textarea>');
            Cache::put("entrez_token", $token, now()->addHours(6));
            return $token;
        }

        throw new \Exception("url https://entrez.enphaseenergy.com/entrez_tokens responded with {$response->status()}");

    }

    public function getSession($refresh=false)
    {

        $token = $this->getToken($refresh);

        if($refresh){
            Cache::forget("enphase_session_{$this->host}");
        }

        //1 hour cache
        $response = Cache::remember("enphase_session_{$this->host}", 60 * 60, function(){
            return Http::timeout(10)
            ->withHeaders([
                "Authorization" => "Bearer {$this->token}"
            ])
            ->withOptions(['verify' => false])
            ->get("{$this->getProtocol()}://{$this->host}/auth/check_jwt");
        });

        if($response->ok()){
            return $response->cookies()->getCookieByName('sessionId')->getValue();
        }

        Cache::forget("enphase_session_{$this->host}");

    }

    public function getBackbone(){
        Cache::forget("envoy_home_{$this->host}");

        $home = Cache::remember("envoy_home_{$this->host}", now()->addMinutes(10), function(){
            $response = Http::withOptions(['verify' => false])->get("{$this->getProtocol()}://{$this->host}/home#auth");
            if(!$response->ok()){
                throw new \Exception("could not reach envoy at {$this->host}");
            }
            return $response->body();
        });
        $backboneString = get_string_between($home, "window.BackboneConfig = {\n", "}\n");
        $backboneString = preg_replace('/\s+/', '', $backboneString);
        $backboneString = str_replace("\"", "", $backboneString);
        return keyValue($backboneString);
    }

    /**
     * A Method to get an asset straight from the envoy
     */

    function getAsset($path)
    {
        return Cache::remember("asset_{$path}", 60, function() use ($path){

            $response = $this->get($path);
            $headers = $response->headers();
            if(isset($headers['Transfer-Encoding'])){
                unset($headers['Transfer-Encoding']);
            }

            return response($response->body())->withHeaders($headers);

        });
    }

    /**
     * A Method to get the production from the envoy
     */

    public function production()
    {
        $res =  $this->get('/production.json', ["details" => 1], timeout: 20, cookies: ['SESSION' => $this->token])->json();
        $res['production'] = collect($res['production'])->mapWithKeys(function($type, $key){
            return ["{$type['type']}_{$key}" => $type];
        })->toArray();
        $res['consumption'] = collect($res['consumption'])->mapWithKeys(function($type, $key){
            if(isset($type['measurementType'])){
                return ["{$type['measurementType']}" => $type];
            }
            return ["{$type['type']}_{$key}" => $type];
        })->toArray();
        return $res;
    }

    /**
     * A Method to get the network info
     */

    public function network()
    {
        return $this->get('/production', ["details" => 1])->json();

    }

    /**
     * A Method to get the inverter statuses (installer login only)
     */

    public function invertersStatus()
    {
        return $this->get('/installer/agf/inverters_status.json', timeout: 20)->json();
    }

    /**
     * A Method to get the inverters (installer login only)
     */

     public function inverters()
     {
         return $this->get('/api/v1/production/inverters', timeout: 20)->json();
     }

}
