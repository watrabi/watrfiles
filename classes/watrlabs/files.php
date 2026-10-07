<?php

namespace watrlabs;

use Aws\S3\S3Client;

// handles everything to do with uploaded files
// stores them locally in storage/files unless R2 is configured in .env
class files {

    // what people are allowed to upload
    // mime type => [file type, extension]
    const ALLOWED_TYPES = [
        "image/png"       => ["image", "png"],
        "image/jpeg"      => ["image", "jpg"],
        "image/gif"       => ["image", "gif"],
        "image/webp"      => ["image", "webp"],
        "video/mp4"       => ["video", "mp4"],
        "video/webm"      => ["video", "webm"],
        "video/quicktime" => ["video", "mov"],
        "audio/mpeg"      => ["audio", "mp3"],
        "audio/ogg"       => ["audio", "ogg"],
        "audio/wav"       => ["audio", "wav"],
        "audio/x-wav"     => ["audio", "wav"],
    ];

    const LOCAL_PATH = __DIR__ . "/../../storage/files/";

    // max upload size in bytes, configurable with MAX_UPLOAD_MB
    static function maxUploadSize(){
        $mb = (int) ($_ENV["MAX_UPLOAD_MB"] ?? 100);
        return max(1, $mb) * 1024 * 1024;
    }

    static function usingR2(){
        return !empty($_ENV["R2_BUCKET"]) && !empty($_ENV["R2_ACCOUNT_ID"]);
    }

    private function r2Client(){
        return new S3Client([
            "region" => "auto",
            "version" => "latest",
            "endpoint" => "https://" . $_ENV["R2_ACCOUNT_ID"] . ".r2.cloudflarestorage.com",
            "credentials" => [
                "key" => $_ENV["R2_KEY_ID"] ?? "",
                "secret" => $_ENV["R2_SECRET"] ?? "",
            ],
        ]);
    }

    // ids are lowercase because the router lowercases every url
    private function genPublicId(){
        global $db;

        $characters = "0123456789abcdefghijklmnopqrstuvwxyz";

        do {
            $id = "";
            for ($i = 0; $i < 10; $i++) {
                $id .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while ($db->table("files")->where("publicId", $id)->first());

        return $id;
    }

    // takes an entry from $_FILES and saves it
    public function upload($userId, $file, $title = null, $public = true){
        global $db;

        if(!is_array($file) || !isset($file["error"]) || is_array($file["error"])){
            return ["status"=>"error", "message"=>"No file was uploaded."];
        }

        switch ($file["error"]) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return ["status"=>"error", "message"=>"Pick a file to upload first."];
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ["status"=>"error", "message"=>"That file is too big."];
            default:
                return ["status"=>"error", "message"=>"The upload failed, try again."];
        }

        if($file["size"] > self::maxUploadSize()){
            return ["status"=>"error", "message"=>"That file is too big. The limit is " . self::formatSize(self::maxUploadSize()) . "."];
        }

        // never trust what the browser says the type is
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file["tmp_name"]);

        if(!isset(self::ALLOWED_TYPES[$mime])){
            return ["status"=>"error", "message"=>"You can only upload images, videos and audio."];
        }

        [$fileType, $extension] = self::ALLOWED_TYPES[$mime];

        $originalName = mb_substr(basename((string) $file["name"]), 0, 255);

        $title = trim((string) $title);
        if($title === ""){
            $title = pathinfo($originalName, PATHINFO_FILENAME);
        }
        $title = mb_substr($title, 0, 100);
        if($title === ""){
            $title = "Untitled";
        }

        $publicId = $this->genPublicId();
        $storedName = $publicId . "." . $extension;

        if(self::usingR2()){
            $this->r2Client()->putObject([
                "Bucket" => $_ENV["R2_BUCKET"],
                "Key" => $storedName,
                "SourceFile" => $file["tmp_name"],
                "ContentType" => $mime,
            ]);
        } else {
            if(!is_dir(self::LOCAL_PATH)){
                mkdir(self::LOCAL_PATH, 0775, true);
            }

            if(!move_uploaded_file($file["tmp_name"], self::LOCAL_PATH . $storedName)){
                return ["status"=>"error", "message"=>"Couldn't save your file, try again later."];
            }
        }

        $db->table("files")->insert([
            "publicId"=>$publicId,
            "userId"=>$userId,
            "title"=>$title,
            "originalName"=>$originalName,
            "storedName"=>$storedName,
            "mimeType"=>$mime,
            "fileType"=>$fileType,
            "size"=>$file["size"],
            "public"=>$public ? 1 : 0,
            "views"=>0,
            "created"=>time(),
        ]);

        return ["status"=>"Okay", "message"=>"Uploaded!", "id"=>$publicId, "url"=>"/file/" . $publicId];
    }

    // gets a file with its uploader's username
    public function get($publicId){
        global $db;

        return $db->table("files")
            ->select(["files.*", "users.username"])
            ->leftJoin("users", "users.id", "=", "files.userId")
            ->where("files.publicId", $publicId)
            ->first();
    }

    // can this user see the file?
    public function canView($file, $user){
        if(!$file)
            return false;

        if($file->public)
            return true;

        return $user && $user->id == $file->userId;
    }

    public function addView($file){
        global $db;

        $db->table("files")->where("id", $file->id)->update(["views"=>$db->raw("views + 1")]);
    }

    // lists files, newest first, with paging
    // pass a userId to only get that user's files
    public function listFiles($page = 1, $perPage = 24, $userId = null, $includePrivate = false){
        global $db;

        $query = $db->table("files")
            ->select(["files.*", "users.username"])
            ->leftJoin("users", "users.id", "=", "files.userId");

        if($userId !== null)
            $query->where("files.userId", $userId);

        if(!$includePrivate)
            $query->where("files.public", 1);

        $total = (clone $query)->count();
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) $page), $pages);

        $results = $query->orderBy("files.created", "DESC")
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->get();

        return [
            "files"=>$results,
            "page"=>$page,
            "pages"=>$pages,
            "total"=>$total,
        ];
    }

    public function stats($userId){
        global $db;

        $row = $db->table("files")
            ->select($db->raw("COUNT(*) AS fileCount, COALESCE(SUM(size), 0) AS totalSize, COALESCE(SUM(views), 0) AS totalViews"))
            ->where("userId", $userId)
            ->first();

        return [
            "count"=>(int) $row->fileCount,
            "size"=>(int) $row->totalSize,
            "views"=>(int) $row->totalViews,
        ];
    }

    public function update($file, $title, $public){
        global $db;

        $title = mb_substr(trim((string) $title), 0, 100);

        if($title === ""){
            return ["status"=>"error", "message"=>"Title can't be empty."];
        }

        $db->table("files")->where("id", $file->id)->update([
            "title"=>$title,
            "public"=>$public ? 1 : 0,
        ]);

        return ["status"=>"Okay", "message"=>"Saved!"];
    }

    public function delete($file){
        global $db;

        if(self::usingR2()){
            $this->r2Client()->deleteObject([
                "Bucket" => $_ENV["R2_BUCKET"],
                "Key" => $file->storedName,
            ]);
        } else {
            $path = self::LOCAL_PATH . basename($file->storedName);
            if(is_file($path)){
                unlink($path);
            }
        }

        $db->table("files")->where("id", $file->id)->delete();

        return ["status"=>"Okay", "message"=>"Deleted."];
    }

    // sends the actual file to the browser
    public function serve($file, $download = false){

        $disposition = ($download ? "attachment" : "inline") . "; filename=\"" . addcslashes($file->originalName, "\"\\") . "\"";

        if(self::usingR2()){
            $client = $this->r2Client();
            $command = $client->getCommand("GetObject", [
                "Bucket" => $_ENV["R2_BUCKET"],
                "Key" => $file->storedName,
                "ResponseContentDisposition" => $disposition,
            ]);
            $url = (string) $client->createPresignedRequest($command, "+1 hour")->getUri();
            header("Location: " . $url, true, 302);
            return;
        }

        $path = self::LOCAL_PATH . basename($file->storedName);

        if(!is_file($path)){
            http_response_code(404);
            return;
        }

        $size = filesize($path);
        $start = 0;
        $end = $size - 1;

        // range support so videos can seek
        if(isset($_SERVER["HTTP_RANGE"]) && preg_match('/^bytes=(\d*)-(\d*)$/', $_SERVER["HTTP_RANGE"], $m)){
            if($m[1] === "" && $m[2] !== ""){
                $start = max(0, $size - (int) $m[2]);
            } else {
                $start = (int) $m[1];
                if($m[2] !== "")
                    $end = min((int) $m[2], $size - 1);
            }

            if($start > $end || $start >= $size){
                http_response_code(416);
                header("Content-Range: bytes */" . $size);
                return;
            }

            http_response_code(206);
            header("Content-Range: bytes $start-$end/$size");
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header("Content-Type: " . $file->mimeType);
        header("Content-Length: " . ($end - $start + 1));
        header("Content-Disposition: " . $disposition);
        header("Accept-Ranges: bytes");
        header("X-Content-Type-Options: nosniff");
        header("Cache-Control: " . ($file->public ? "public" : "private") . ", max-age=86400");

        $handle = fopen($path, "rb");
        fseek($handle, $start);
        $left = $end - $start + 1;

        while ($left > 0 && !feof($handle)) {
            $chunk = fread($handle, min(8192, $left));
            echo $chunk;
            flush();
            $left -= strlen($chunk);
        }

        fclose($handle);
    }

    static function formatSize($bytes){
        $units = ["B", "KB", "MB", "GB", "TB"];
        $i = 0;
        $bytes = (float) $bytes;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return ($i == 0 ? (int) $bytes : round($bytes, 1)) . " " . $units[$i];
    }

}
