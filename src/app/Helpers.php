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
    $final = [];
    $dotted = \Arr::dot($array);
    foreach($dotted as $key => $dot){
        $newKey = str_replace(".", "_", $key);
        $final[$newKey] = $dot;
    }

    return $final;
}
