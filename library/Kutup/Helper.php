<?php
class Kutup_Helper {
    public static function bilgiMesaji($msg) {
    $userSession = new Zend_Session_Namespace('kullaniciSession');
    $userSession->unlock();
    $userSession->bilgi_mesaji = $msg;
    $userSession->lock();
    }

    public static function hataMesaji($msg) {
    $userSession = new Zend_Session_Namespace('kullaniciSession');
    $userSession->unlock();
    $userSession->hata_mesaji = $msg;
    $userSession->lock();
}

    public static function linkcevir($string) {
        $gelen=array("ş","Ş","ı","ü","Ü","ö","Ö","ç","Ç","ş","Ş","ı","ğ","Ğ","İ","ö","Ö","Ç","ç","ü","Ü"," ");
        $duzgun=array("s","S","i","u","U","o","O","c","C","s","S","i","g","G","I","o","O","C","c","u","U","-");
        $string = trim($string);
        $string=str_replace($gelen,$duzgun,$string);
        $string = preg_replace("@[^a-z0-9\-şıüğçİŞĞÜÇ]+@i","",$string);
        $string = strtolower($string);
        return $string;
    }
}
    ?>