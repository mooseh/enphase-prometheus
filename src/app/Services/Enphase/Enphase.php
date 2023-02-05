<?php

namespace App\Services\Enphase;

use Http;

/**
 * The Enphase class for communicating to the envoy
 */

class Enphase {

    protected $config;
    protected $host;
    protected $sessionId;

    public function __construct($config)
    {
        $this->config = $config;
        $this->host = $config['host'];
        //$this->sessionId = $config['session'];
        $this->sessionId = $this->challenge();

    }

    protected function getProtocol(){
        $protocol = "https";
        if(! $this->config['ssl']){
            $protocol = "http";
        }
        return $protocol;
    }

    protected function challenge(){
        // first load the home page
        $body = $this->get('/home')->body();
        dd($body);
    }

    protected function get($path, $params = []){
        $path = ltrim($path, "/");
        $cookies = [
            "sessionId" => $this->sessionId
        ];

        $url = "{$this->getProtocol()}://{$this->host}/{$path}";
        $response = Http::withCookies($cookies, $this->host)
            ->withOptions(['verify' => false])
            ->get($url, $params);

        if($response->ok()){
            return $response;
        }

        dd($response);

    }

    /**
     * A Method to get all the home data
     */

    /**
     * A Method to get the production from the envoy
     */

    public function production()
    {
        return $this->get('/production.json', ["details" => 1])->json();
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
        return $this->get('/inverters_status.json', ["details" => 1])->json();
    }

}
