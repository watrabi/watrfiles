<?php

namespace watrlabs;

use watrlabs\encryption;
use watrlabs\cookies;

class sessions {

    const SESSION_LENGTH = 2629743; // about a month

    // name of the cookie that holds the session id
    static function cookieName(){
        return $_ENV["AuthCookieName"] ?? "files_auth";
    }

    // creates a session, you can assign a userid
    public function createSession($userId = null){
        
        global $db;
        $encrpytion = new encryption();
        $cookies = new cookies();

        $sessionId = $encrpytion->genRandString(100); // server go boom

        $insert = [
            "sessionId"=>$sessionId,
            "userAgent"=>mb_substr($_SERVER['HTTP_USER_AGENT'] ?? "", 0, 150),
            "created"=>time()
        ];

        if($userId){
            $insert["userId"] = $userId;
        }

        $db->table("sessions")->insert($insert);

        $cookies->createCookie($sessionId, false, self::cookieName(), time() + self::SESSION_LENGTH);

        return $sessionId;

    }

    public function getCurrentSession(){

        global $db;

        $currentCookie = $_COOKIE[self::cookieName()] ?? null;

        if(!$currentCookie)
            return null;

        $session = $db->table("sessions")->where("sessionId", $currentCookie)->first();

        if($session && $session->created + self::SESSION_LENGTH < time())
            return null;

        return $session;
    }

    // logs out the current session
    public function destroyCurrentSession(){

        global $db;
        $cookies = new cookies();

        $currentCookie = $_COOKIE[self::cookieName()] ?? null;

        if($currentCookie)
            $db->table("sessions")->where("sessionId", $currentCookie)->delete();

        $cookies->destroyCookie(self::cookieName());
    }

    // logs a user out everywhere except the current session
    public function destroyOtherSessions($userId){

        global $db;

        $currentCookie = $_COOKIE[self::cookieName()] ?? "";

        $db->table("sessions")
            ->where("userId", $userId)
            ->where("sessionId", "!=", $currentCookie)
            ->delete();
    }

}
