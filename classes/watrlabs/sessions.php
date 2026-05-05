<?php

namespace watrlabs;

use watrlabs\encryption;
use watrlabs\cookies;

class sessions {

    // creates a session, you can assign a userid
    public function createSession($userId = null){
        
        global $db;
        $encrpytion = new encryption();
        $cookies = new cookies();

        $sessionId = $encrpytion->genRandString(100); // server go boom

        $insert = [
            "sessionId"=>$sessionId,
            "userAgent"=>$_SERVER['HTTP_USER_AGENT'],
            "created"=>time()
        ];

        if($userId){
            $insert["userId"] = $userId;
        }

        $db->table("sessions")->insert($insert);

        $cookies->createCookie($sessionId, false, "files_auth", time() + 2629743);

        return $sessionId;

    }

    public function getCurrentSession(){

        global $db;

        $currentCookie = null;

        if(isset($_COOKIE[$_ENV["AuthCookieName"]]))
            $currentCookie = $_COOKIE[$_ENV["AuthCookieName"]];
        
        if($currentCookie)
            return $db->table("sessions")->where("sessionId", $currentCookie)->first();

        return null;
    }

}