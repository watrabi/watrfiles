<?php

namespace watrlabs;

use watrlabs\encryption;

class cookies {

    function createCookie($cookieValue, $encrypted = false, $cookieName = "null", $expires = null){

        if($encrypted){
            $encryption = new encryption();

            $cookieValue = $encryption->encrypt($cookieValue);
        }

        if(!$cookieName){
            $cookieName = $_ENV["CookieName"];
        }

        if(!$expires){
            $expires = time() + 8600;
        }

        setcookie($cookieName, $cookieValue, $expires, "", "." . $_ENV["Domain"], true);
    }

    function getEncryptedCookie($cookieName){

        $cookieValue = null;

        if(!$cookieName){
            $cookieName = $_ENV["CookieName"];
        }

        if($_COOKIE[$cookieName]){
            $cookieValue = $_COOKIE[$cookieName];
        }

        if()

    }

    function destroyCookie($cookieName = "null"){

        if(!$cookieName){
            $cookieName = $_ENV["CookieName"];
        }

        if($_COOKIE[$cookieName]){
            $cookieValue = $_COOKIE[$cookieName];
        } else {
            $cookieValue = "Bogus Value. Phak You."; // best auth
        }

        setcookie($cookieName, $cookieValue, 0, "", "." . $_ENV["Domain"], true);
    }

}