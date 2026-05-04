<?php

namespace watrlabs;

class encryption {
    private $key = "";
    private $method = 'aes-256-gcm'; 
    
    public function __construct() {
        $this->key = $_ENV["ENCRYPTION_KEY"];
    }

    public function encrypt($text) {
        $ivLength = openssl_cipher_iv_length($this->method);
        $iv = random_bytes($ivLength);

        $tag = '';
        $ciphertext = openssl_encrypt($text, $this->method, $this->key, OPENSSL_RAW_DATA, $iv, $tag);

        return base64_encode($iv . $tag . $ciphertext);
    }
    
    public function decrypt($data) {
        $data = base64_decode($data);

        $ivLength = openssl_cipher_iv_length($this->method);
        $iv = substr($data, 0, $ivLength);
        $tag = substr($data, $ivLength, 16); 
        $ciphertext = substr($data, $ivLength + 16);

        return openssl_decrypt($ciphertext, $this->method, $this->key, OPENSSL_RAW_DATA, $iv, $tag);
    }
}