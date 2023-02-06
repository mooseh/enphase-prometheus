<?php

namespace App\Services\Enphase;

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
    protected $sessionId;
    protected $backbone;

    public function __construct($config)
    {
        $this->config = $config;
        $this->host = $config['host'];
        $this->token = $config['token'];
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

        $cookies = [
            "sessionId" => $this->getSession()
        ];

        $url = "{$this->getProtocol()}://{$this->host}/{$path}";
        $response = Http::timeout($timeout)
            ->withHeaders($headers)
            ->withCookies($cookies, $this->host)
            ->withOptions(['verify' => false])
            ->get($url, $params);

        if($response->status() === 401){
            $this->getSession(true);
            $cookies = [
                "sessionId" => $this->sessionId
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

    public function getSession($refresh=false)
    {

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

    }

    public function getBackbone(){
        $home = Cache::remember("envoy_home_{$this->host}", 10 * 60, function(){
            return $this->get('/home#auth')->body();
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
        return $this->get('/production.json', ["details" => 1], timeout: 20)->json();
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
