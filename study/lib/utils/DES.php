<?php
namespace Lib\Utils;

class DES {
    private $_iv = null;
    private $_key = null;
    public function __construct($key,$iv)
    {
        if(strlen($key) != 8){
            throw new Exception("key must be equal 8 length");
        }
        if(strlen($iv) != 16){
            throw new Exception("key must be equal 16 length");
        }
        $this->_iv = hex2bin($iv);
        $this->_key = $key;
    }

    private function _pkcs5Pad($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    private function _pkcs5Unpad($text) {
        $pad = ord($text {strlen($text) - 1});
        if ($pad > strlen($text)){
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad){
            return false;
        }
        return substr($text, 0, - 1 * $pad);
    }

    /**
     * 解密
     */
    public function decrypt($str){
        $strBin = base64_decode($str);
        $str = mcrypt_decrypt(MCRYPT_DES, $this->_key, $strBin,MCRYPT_MODE_CBC, $this->_iv);
        return $this->_pkcs5Unpad($str);
    }

    /**
     * 加密
     */
    public function encrypt($str){
        $size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_CBC);
        $str = $this->_pkcs5Pad($str, $size);
        $strBin = mcrypt_encrypt(MCRYPT_DES, $this->_key, $str, MCRYPT_MODE_CBC, $this->_iv);
        return base64_encode($strBin);
    }
}
/*
$des = new DES('kakakeka','1234567890abcdef');
echo $des->decrypt('bl38LPBkRAiWYwHkK/56/w==');
echo "\r\n";
echo $des->encrypt('88888888');
echo "\r\n";*/