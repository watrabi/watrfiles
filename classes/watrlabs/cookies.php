<?php

namespace watrlabs;

use watrlabs\encryption;

class cookies {

    // COPYRIGHT SWORD 2026
    // NO REUSE OR ELSE YOU WILL GET SUED
    // returns cookie name
    private function valcookie($cookieName) {
        if(!$cookieName)
            $cookieName = $_ENV["CookieName"];
        return $cookieName;
    }

    // creates and assigns a cookie
    // has the ability to be encrypted
    public function createCookie($cookieValue, $encrypted = false, $cookieName = "null", $expires = null){

        if($encrypted){
            $encryption = new encryption();

            $cookieValue = $encryption->encrypt($cookieValue);
        }

        $cookieName = $this->valcookie($cookieName);

        if(!$expires){
            $expires = time() + 8600;
        }

        setcookie($cookieName, $cookieValue, $expires, "", "." . $_ENV["Domain"], true);
    }

    // gets the value of an encrypted cookie
    public function getEncryptedCookie($cookieName){

        $encryption = new encryption();

        $cookieValue = null;

        $cookieName = $this->valcookie($cookieName);

        if($_COOKIE[$cookieName]){
            $cookieValue = $_COOKIE[$cookieName];
        }

        return $encryption->decrypt($cookieValue);

    }

    // gets rid of a cookie by setting its expiration date in the past
    // also assigns a bogus value
    function destroyCookie($cookieName = "null"){

        $cookieName = $this->valcookie($cookieName);

        $cookieValue = "Bogus Value. Phak You."; // best auth

        setcookie($cookieName, $cookieValue, 0, "", "." . $_ENV["Domain"], true);
    }

}