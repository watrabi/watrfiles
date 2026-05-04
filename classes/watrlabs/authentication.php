<?php

namespace watrlabs;

use watrlabs\sitefunctions;
use watrlabs\watrkit\sanitize;
use watrlabs\logging\discord;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;

global $db;

class authentication {

    private $cookiename = "";
    private $currentuser = false;

    public function __construct(){
        $this->cookiename = $_ENV["CookieName"];
    }

    public function getCookie(){
        return $_COOKIE[$this->cookiename];
    }

    public function getUserInfo($id = null){

        global $db;

        if($id){
            return $db->table("users")->where("id", $id)->first();
        } else {
            $sessionInfo = $this->getSessionInfo($this->getCookie());

            if($sessionInfo){
                $userInfo = $db->table("users")->where("id", $sessionInfo->userid)->first();

                if($userInfo){
                    $this->currentuser = $userInfo;
                    return $userInfo;
                }

            } else {
                return null;
            }

        }
    }

    public function isLoggedIn(){ 
        return  $this->getUserInfo();
    }

    public function getSessionInfo($Session){
        global $db;
        $sessionInfo = $db->table("sessions")->where("session", $Session)->first();
        return $sessionInfo;
    }

    public function create($username, $password) {

        global $db;

        $doesExist = $db->table("users")->where("username", $username)->first();

        if($doesExist)[
            return ["status"=>"error", "message"=>"Username is already taken."];
        ]

        if(strlen($username) > 20){
            return ["status"=>"error", "message"=>"Username is too long."];
        }

        if (trim($str) && str_contains($str, ' ') == false) {
            return ["status"=>"error", "message"=>"Username cannot have spaces at the beginning or end."];
        }

        // ok good enough

        $insert = [
            "username"=>$username,
            "password"=>password_hash($password, PASSWORD_BCRYPT),
            "created"=>time()
        ];

        $db->table("users")->insert($insert);

        

    }


    
}