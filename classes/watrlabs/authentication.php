<?php

namespace watrlabs;

use watrlabs\sessions;

global $db;

class authentication {

    // returns current user information based on 
    // the currently logged in user or provided id
    public function getUserInfo($id = null){

        global $db;
        $sessions = new sessions();

        if($id)
            return $db->table("users")->where("id", $id)->first();

        $currentSession = $sessions->getCurrentSession();

        if($currentSession){
            return $db->table("users")->where("id", $currentSession->userId)->first();
        }


        return null;
        
    }

    // MIGHT return a bool if the current user is signed in
    public function isLoggedIn(){ 
        return (bool) $this->getUserInfo();
    }

    // creates a user and automatically signs them in
    // returns an array if an error occurs.
    public function createUser($username, $password) {

        global $db;
        $sessions = new sessions();

        $doesExist = $db->table("users")->where("username", $username)->first();

        if($doesExist){
            return ["status"=>"error", "message"=>"Username is already taken."];
        }

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

        $userId = $db->table("users")->insert($insert);

        $sessions->createSession($userId);

        return ["status"=>"Okay", "message"=>"Your account has been created!"];

    }


    
}