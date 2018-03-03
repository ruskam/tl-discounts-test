<?php

class  NetworkUtils
{
    /**
     * Retrieves JSON data via external REST API
     * @param $url
     * @return mixed|null
     */
    public static function getData($url){
        /*
         * In real life data are consumed via web API using the function below
         * $parsedProducts = file_get_contents($url, false, stream_context_create($sslContextOptions));
         *
         * For this test the data are read from the file
         */
        try {
            $data = file_get_contents($url);
            return json_decode($data, true);
        } catch (Exception $e){
            echo $e->getMessage();
        }
        return null;
    }
}
