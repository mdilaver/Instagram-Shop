<?php 

class Yonetim_Hash {

    protected $key;     // Secret Key to secure hashed password
    protected $etype;   // Encryption Algorithm
    protected $mcmod;   // Mcrypt Mod
    protected $rand;    // Mcrypt Random Number generator
    protected $iv;      // Mcrypt initialization vector
    

    public function __construct()
    {
        $this->etype = MCRYPT_RIJNDAEL_256;
        $this->mcmod = MCRYPT_MODE_ECB;
        $this->rand  = MCRYPT_RAND;
        $this->key   = 'mnbnpg3l_Fuke2QD^V*4n&NgsPeteTyn';
        $this->iv    = @mcrypt_create_iv(@mcrypt_get_iv_size($this->etype, $this->mcmod), $this->rand);

        if(!function_exists('mcrypt_create_iv'))
        {
            exit('<strong>HASH Error:</strong> Class needs Mcrypt library to work.');
        }

        if(version_compare(PHP_VERSION, '5.3.0') === -1)
        {
            exit('<strong>HASH Error:</strong> Class needs at least PHP 5.3.0 to work.');
        }
    }

    public function make($password, $key = FALSE)
    {
        return trim(bin2hex(mcrypt_encrypt($this->etype, $this->key($key), $password, $this->mcmod, $this->iv)));
    }

    public function take($protected, $key = FALSE)
    {
        return trim(mcrypt_decrypt($this->etype, $this->key($key), hex2bin($protected), $this->mcmod, $this->iv));
    }
}
?>