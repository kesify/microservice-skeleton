<?php


namespace Kesify\MicroserviceSkeleton\Services;


class KeyService
{
    public function __construct(){

    }

    public function createAlphanumericCode($length = 4){
        $token='';
        $chars = "1234567890";
        for ($i=0;$i<$length; $i++) {
            $num = rand() % strlen($chars);
            $tmp = substr($chars, $num, 1);
            $token .= $tmp;
        }

        return $token;
    }

    public function createToken($length = 255){
        $token='';
        $chars = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz&%$!#=";
        for ($i=0;$i<$length; $i++) {
            $num = rand() % strlen($chars);
            $tmp = substr($chars, $num, 1);
            $token .= $tmp;
        }

        return $token;
    }

    public function createTokenWithoutSpecialCharacters($length = 255){
        $token='';
        $chars = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        for ($i=0;$i<$length; $i++) {
            $num = rand() % strlen($chars);
            $tmp = substr($chars, $num, 1);
            $token .= $tmp;
        }

        return $token;
    }

    public function generateName($length = 32){
        $token='';
        $chars = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        for ($i=0;$i<$length; $i++) {
            $num = rand() % strlen($chars);
            $tmp = substr($chars, $num, 1);
            $token .= $tmp;
        }

        return $token;
    }

}
