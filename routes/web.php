<?php
use watrlabs\router\Routing;
use watrlabs\watrkit\sanitize;
use watrlabs\files;

global $router; // IMPORTANT: KEEP THIS HERE!
global $pagebuilder;

$router->get("/", function() {
    global $twig;

    $files = new files();
    $result = $files->listFiles(1, 12);

    echo $twig->render('default.twig', $result);
});

$router->set404(function(){
    global $twig;
    echo $twig->render('status_codes/404.twig');
});

/*
$router->group('/group', function($router) {
    
    $router->get("/hi", function () {
        echo "test<br>";
    });
    
});
*/
