<?php

class Kutup_Sifreleme
{
    private $_id;
    private $_key = 'insTa*3MvD?72-xMy_zSbC';

    public function __construct()
    {
        $this->_baseUrl = Zend_Controller_Front::getInstance()->getRequest()->getBaseUrl();
        $this->_controllerName = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
    }

    static function encode($md5)
    {
        $key = 'inSta*3MvD?72-xMy_zSbC'; //$this->_key; this->key bozuyor bunu
        $md5 .= "|1905";
        $iv_size = mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $crypt = mcrypt_encrypt(MCRYPT_3DES, $key, $md5, MCRYPT_MODE_ECB, $iv);
        return bin2hex(($crypt));
    }

    static function decode($crypt)
    {
        $key = 'inSta*3MvD?72-xMy_zSbC'; //$this->_key;
        $crypt = pack("H*", $crypt);
        $iv_size = mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $decrypt = mcrypt_decrypt(MCRYPT_3DES, $key, $crypt, MCRYPT_MODE_ECB, $iv);
        $decrypt = explode("|", $decrypt);
        return $decrypt[0];
    }
}