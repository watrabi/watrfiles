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

        $username = trim((string) $username);

        $doesExist = $db->table("users")->where("username", $username)->first();

        if($doesExist){
            return ["status"=>"error", "message"=>"Username is already taken."];
        }

        $validation = $this->validateCredentials($username, $password);
        if($validation)
            return $validation;

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

    // returns an error array if the username or password isn't allowed
    private function validateCredentials($username, $password){

        if(strlen($username) < 3){
            return ["status"=>"error", "message"=>"Username is too short."];
        }

        if(strlen($username) > 20){
            return ["status"=>"error", "message"=>"Username is too long."];
        }

        if(!preg_match('/^[A-Za-z0-9_]+$/', $username)){
            return ["status"=>"error", "message"=>"Usernames can only have letters, numbers and underscores."];
        }

        return $this->validatePassword($password);
    }

    private function validatePassword($password){

        if(strlen((string) $password) < 8){
            return ["status"=>"error", "message"=>"Password must be at least 8 characters."];
        }

        return null;
    }

    // checks a username and password and signs the user in
    public function login($username, $password){

        global $db;
        $sessions = new sessions();

        $userInfo = $db->table("users")->where("username", trim((string) $username))->first();

        if($userInfo && password_verify((string) $password, $userInfo->password)){
            $sessions->createSession($userInfo->id);
            return ["status"=>"Okay", "message"=>"you good"];
        }

        return ["status"=>"error", "message"=>"Username or password is incorrect."];
    }

    public function changePassword($user, $currentPassword, $newPassword){

        global $db;

        if(!password_verify((string) $currentPassword, $user->password)){
            return ["status"=>"error", "message"=>"Your current password is wrong."];
        }

        $validation = $this->validatePassword($newPassword);
        if($validation)
            return $validation;

        $db->table("users")->where("id", $user->id)->update([
            "password"=>password_hash($newPassword, PASSWORD_BCRYPT),
            "updated"=>time()
        ]);

        // sign out everywhere else in case someone else had the old password
        (new sessions())->destroyOtherSessions($user->id);

        return ["status"=>"Okay", "message"=>"Your password has been changed."];
    }

    // sends people to the login page if they aren't signed in
    // returns the current user if they are
    static function requireLogin(){

        global $currentuser;

        if(!$currentuser){
            header("Location: /auth/login");
            exit;
        }

        return $currentuser;
    }

}
