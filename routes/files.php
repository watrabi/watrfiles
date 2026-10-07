<?php
use watrlabs\router\Routing;

use watrlabs\authentication;
use watrlabs\files;

global $router; // IMPORTANT: KEEP THIS HERE!

// shows the not found page
function filesNotFound() {
    global $twig;
    http_response_code(404);
    echo $twig->render('status_codes/404.twig');
}

// gets a file the current user is allowed to touch, or null
function ownedFile($id) {
    global $currentuser;

    $files = new files();
    $file = $files->get($id);

    if(!$file || !$currentuser || $file->userId != $currentuser->id)
        return null;

    return $file;
}

// all public files
$router->get("/files", function() {
    global $twig;

    $files = new files();
    $result = $files->listFiles($_GET["page"] ?? 1);

    echo $twig->render('files/browse.twig', $result);
});

// your dashboard
$router->get("/home", function() {
    global $twig;

    $user = authentication::requireLogin();

    $files = new files();
    $result = $files->listFiles($_GET["page"] ?? 1, 24, $user->id, true);
    $result["stats"] = $files->stats($user->id);

    echo $twig->render('files/home.twig', $result);
});

$router->get("/upload", function() {
    global $twig;

    authentication::requireLogin();

    echo $twig->render('files/upload.twig', [
        "maxSize"=>files::maxUploadSize(),
    ]);
});

// page for a single file
$router->get("/file/{id}", function($id) {
    global $twig, $currentuser;

    $files = new files();
    $file = $files->get($id);

    if(!$files->canView($file, $currentuser))
        return filesNotFound();

    $files->addView($file);
    $file->views++;

    echo $twig->render('files/view.twig', [
        "file"=>$file,
        "isOwner"=>$currentuser && $currentuser->id == $file->userId,
    ]);
});

// the actual file contents
$router->get("/file/{id}/raw", function($id) {
    global $currentuser;

    $files = new files();
    $file = $files->get($id);

    if(!$files->canView($file, $currentuser))
        return filesNotFound();

    $files->serve($file, isset($_GET["download"]));
});

// someone's public files
$router->get("/users/{id}/profile", function($id) {
    global $twig, $db, $currentuser;

    $user = $db->table("users")->where("id", (int) $id)->first();

    if(!$user)
        return filesNotFound();

    $isSelf = $currentuser && $currentuser->id == $user->id;

    $files = new files();
    $result = $files->listFiles($_GET["page"] ?? 1, 24, $user->id, $isSelf);
    $result["profile"] = $user;
    $result["isSelf"] = $isSelf;

    echo $twig->render('files/profile.twig', $result);
});

$router->group('/api/v1/files', function($router) {

    $router->post("/upload", function() {
        global $currentuser;

        if(!$currentuser)
            return ["status"=>"error", "message"=>"You need to be logged in to upload."];

        // php throws the whole body away when it's over post_max_size
        if(empty($_FILES) && (int) ($_SERVER["CONTENT_LENGTH"] ?? 0) > 0)
            return ["status"=>"error", "message"=>"That file is too big."];

        $files = new files();

        return $files->upload(
            $currentuser->id,
            $_FILES["file"] ?? null,
            $_POST["title"] ?? null,
            ($_POST["public"] ?? "1") === "1"
        );
    });

    $router->post("/{id}/update", function($id) {
        $file = ownedFile($id);

        if(!$file)
            return ["status"=>"error", "message"=>"File not found."];

        $files = new files();

        return $files->update(
            $file,
            $_POST["title"] ?? "",
            ($_POST["public"] ?? "1") === "1"
        );
    });

    $router->post("/{id}/delete", function($id) {
        $file = ownedFile($id);

        if(!$file)
            return ["status"=>"error", "message"=>"File not found."];

        $files = new files();

        return $files->delete($file);
    });

});
