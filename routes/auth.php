<?php
use watrlabs\router\Routing;



global $router; // IMPORTANT: KEEP THIS HERE!

$router->group('/api/v1/auth', function($router) {
    
    $router->get("/status", function () {
        return ["status"=>"ye"];
    });
    
}, '');

$router->group('/auth', function($router) {
    
    $router->get("/register", function () {
        global $twig;
        echo $twig->render('auth/register.twig');
    });

    $router->get("/login", function () {
        global $twig;
        echo $twig->render('auth/login.twig');
    });
    
});