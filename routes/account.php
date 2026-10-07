<?php
use watrlabs\router\Routing;

use watrlabs\authentication;
use watrlabs\files;

global $router; // IMPORTANT: KEEP THIS HERE!

// account settings
$router->get("/accounts", function() {
    global $twig;

    $user = authentication::requireLogin();

    $files = new files();

    echo $twig->render('account/settings.twig', [
        "stats"=>$files->stats($user->id),
    ]);
});

$router->group('/api/v1/account', function($router) {

    $router->post("/password", function() {
        global $currentuser;

        if(!$currentuser)
            return ["status"=>"error", "message"=>"You need to be logged in."];

        $auth = new authentication();

        return $auth->changePassword(
            $currentuser,
            $_POST["current"] ?? "",
            $_POST["new"] ?? ""
        );
    });

});
