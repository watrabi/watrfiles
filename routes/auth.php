<?php
use watrlabs\router\Routing;

use watrlabs\authentication;
use watrlabs\sessions;

global $router; // IMPORTANT: KEEP THIS HERE!

// all of /api/v1/auth endpoints
// (nah really)
$router->group('/api/v1/auth', function($router) {
    
    // test route
    // lets people know if auth is alive ig
    // idk
    $router->get("/status", function () {
        return ["status"=>"ye"];
    });

    // route to let users interact with the login api
    $router->post("/login", function(){

        if(isset($_POST["username"], $_POST["password"])){

            $auth = new authentication();

            return $auth->login($_POST["username"], $_POST["password"]);

        }

        return ["status"=>"error","message"=>"Invalid Request"];

    });

    // route to let users interact with the register api
    $router->post("/register", function () {

        if(isset($_POST["username"], $_POST["password"])){

            $auth = new authentication();

            $username = $_POST["username"];
            $password = $_POST["password"];

            return $auth->createUser($username, $password); // secure

        }

        return ["status"=>"error","message"=>"Invalid Request"];
    });

    // signs you out
    $router->post("/logout", function () {
        $sessions = new sessions();
        $sessions->destroyCurrentSession();

        return ["status"=>"Okay", "message"=>"bye"];
    });
    
});

// public facing auth pages
// where users can register and sign up
$router->group('/auth', function($router) {
    
    // endpoint to show the register page
    $router->get("/register", function () {
        global $twig, $currentuser;

        if($currentuser){
            header("Location: /home");
            return;
        }

        echo $twig->render('auth/register.twig');
    });

    // endpoint to show the login page
    $router->get("/login", function () {
        global $twig, $currentuser;

        if($currentuser){
            header("Location: /home");
            return;
        }

        echo $twig->render('auth/login.twig');
    });
    
});
