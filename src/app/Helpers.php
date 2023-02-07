<?php

function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

function keyValue($string, $lineDelimiter=",", $keyValue = ":")
{
    $data = [];
    $lineArray = explode($lineDelimiter, $string);

    foreach($lineArray as $line){
        $tmp = preg_split("~{$keyValue}~", $line, 2);
        $data[$tmp[0]] = $tmp[1];
    }
    return $data;
}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

function prometheus($array)
{
    $dotted = \Arr::dot($array);
    $dataString = collect($dotted)->map(function($value, $key){
        if(intval($value)){
            $key = str_replace(".", "_", $key);
            $key = str_replace("-", "_", $key);
            $value = intval($value);
            return "{$key} {$value}";
        }
        return null;
    })->filter()->implode("\n");
    return "{$dataString}\n";
}
