<?php
declare(strict_types=1);
/*
 |  Media       The advanced Media & File Manager for Bludit
 |  @file       ./system/admin.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.2.0 [0.1.0] - Beta
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
    defined("BLUDIT") or die("Go directly to Jail. Do not pass Go. Do not collect 200 Cookies!");

    // Main Administration
    class MediaAdmin {
        const SE_STATUS = "MEDIA-STATUS";
        const SE_MESSAGE = "MEDIA-STATUS-MESSAGE";
        const SE_DATA = "MEDIA-STATUS-DATA";

        /*
         |  API :: AVAILABLE METHODs
         */
        protected $methods = [];

        /*
         |  API :: CURRENT METHOD
         */
        public $method = null;

        /*
         |  API :: CURRENT QUERY
         */
        public $query = [];

        /*
         |  CUSTOM ADMIN REQUEST
         */
        public $custom = false;

        /*
         |  AJAX REQUEST
         */
        public $ajax = false;

        /*
         |  LAST STATUS
         */
        public $status = [];


        /*
         |  CONSTRUCTOR
         |  @since  0.1.0
         */
        public function __construct() {
            if(!Session::started()) {
                Session::start();
            }

            // Get Last Status
            if(isset($_SESSION[self::SE_STATUS]) && isset($_SESSION[self::SE_MESSAGE])) {
                $this->status = [$_SESSION[self::SE_STATUS], $_SESSION[self::SE_MESSAGE]];
                if(isset($_SESSION[self::SE_DATA])) {
                    $this->status[] = $_SESSION[self::SE_DATA];
                }
            }
            unset($_SESSION[self::SE_STATUS]);
            unset($_SESSION[self::SE_MESSAGE]);
            unset($_SESSION[self::SE_DATA]);

            // Check if AJAX
            $this->ajax = strtolower($_SERVER["HTTP_X_REQUESTED_WITH"] ?? "") === "xmlhttprequest";

            // Set API Data
            $this->methods = [
                "list"      => ["GET", "POST"],
                "upload"    => ["POST"],
                "create"    => ["POST"],
                "move"      => ["POST"],
                "rename"    => ["POST"],
                "delete"    => ["GET", "POST"],
            ];
        }

        /*
         |  HELPER :: BUILD ADMIN URL
         |  @since  0.1.0
         |
         |  @param  string  The path after the admin slug (without slashes on both sides).
         |  @param  multi   The additional http query.
         |  @param  bool    TRUE to replace the current query with the additional one,
         |                  FALSE to merge both queries and use the result.
         |
         |  @return string  The admin URL using the path and the query, if set.
         */
        public function buildURL(string $path, array $query = [ ], bool $replace = true): string {
            if(strpos($path, "?") !== false) {
                $path = substr($path, 0, strpos($path));
            }

            // Handle Query
            if(!$replace) {
                $query = array_merge($this->query, $query);
            }
            $query = !empty($query)? "?" . http_build_query($query): "";

            // Return URL
            return DOMAIN_ADMIN . $path . $query;
        }

        /*
         |  API :: HANDLE RESPONSE
         |  @since  0.2.0
         |
         |  @param  bool    TRUE if the request was success, FALSE if not.
         |  @param  string  The status message.
         |  @param  array   The additional data array for AJAX requests.
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        public function response(bool $status, string $message, array $data = []) {
            if(isset($data["path"])) {
                $data["path"] = MediaManager::slug($data["path"]);
            }

            // Execute AJAX Request
            if($this->ajax) {
                header(($status)? "HTTP/1.1 200 OK": "HTTP/1.1 400 Bad Request");
                print(json_encode(array_merge([
                    "status"    => $status? "success": "error",
                    "message"   => $message,
                ], $data)));
                die();
            }

            // Add Message
            $_SESSION[self::SE_STATUS] = $status;
            $_SESSION[self::SE_MESSAGE] = $message;
            $_SESSION[self::SE_DATA] = $data;

            // Prepare Redirect
            $query = ["status" => $status? "success": "error"];
            $referrer = $_SERVER["HTTP_REFERER"] ?? "";
            if(isset($data["path"])) {
                $query["path"] = $data["path"];
                if(is_file(MediaManager::absolute($data["path"])) && !empty($referrer) && strpos($referrer, $this->rename ?? basename($query["path"])) === false) {
                    $query["path"] = dirname($query["path"]);
                }
            } else {
                if(!empty($referrer) && strpos($referrer, "?") !== false) {
                    $temp = parse_str(substr($referrer, strpos($referrer, "?")+1));
                    $referrer = $temp["path"] ?? "";
                } else {
                    $referrer = "";
                }
                if(empty($referrer)) {
                    $referrer = $_POST["path"] ?? $_GET["path"] ?? "/";
                }

                if(MediaManager::absolute($referrer) !== null) {
                    $query["path"] = $referrer;
                }
            }

            // Redirect
            unset($this->rename);
            Redirect::url($this->buildURL("media", $query, true));
            die();
        }

        /*
         |  API :: HANDLE REQUEST
         |  @since  0.2.0
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        public function request() {
            global $url;
            global $security;
            global $media_manager;

            // Get Method
            if(!array_key_exists($this->method, $this->methods)) {
                return $this->response(false, bt_("The request is unknown or invalid."));
            }

            // Get Query
            if($_SERVER["REQUEST_METHOD"] === "GET" && in_array("GET", $this->methods[$this->method])) {
                if((isset($_GET["tokenCSRF"]) || isset($_GET["token"])) && isset($_GET["path"])) {
                    $this->query = $_GET;
                }
            } else if($_SERVER["REQUEST_METHOD"] === "POST" && in_array("POST", $this->methods[$this->method])) {
                if((isset($_POST["tokenCSRF"]) || isset($_POST["token"])) && isset($_POST["path"])) {
                    $this->query = $_POST;
                }
            }
            if(empty($this->query)) {
                return $this->response(false, bt_("The request is invalid or empty."));
            }
            $this->query["path"] = ltrim(urldecode($this->query["path"]), "/\\");

            // Check Token
            if($security->validateTokenCSRF($this->query["tokenCSRF"] ?? $this->query["token"]) === false) {
                return $this->response(false, bt_("The CSRF token is invalid or missing."));
            }

            // Check Path
            $path = MediaManager::absolute($this->query["path"]);
            if($path === null && !(($this->query["temp"] ?? "false") === "true" && in_array($this->method, ["list", "upload"]))) {
                return $this->response(false, bt_("The passed path is invalid or does not exist."));
            }

            // Temporary Path
            if($path === null) {
                $path = MediaManager::absolute(dirname($this->query["path"]));
                if($path === null) {
                    return $this->response(false, bt_("The passed path is invalid or does not exist."));
                }

                if($this->method === "upload") {
                    if(($error = $media_manager->create($path, basename($this->query["path"]), "folder")) !== true) {
                        return $this->response(false, $error);
                    }
                }

                $path .= DS . basename($this->query["path"]);
            }

            // Handle Request
            return $this->{"_{$this->method}"}($path, $this->query);
        }


##
##  API METHODs
##

        /*
         |  METHOD :: UPLOAD FILEs
         |  @type   POST
         |  @since  0.1.0
         |
         |  @param  string  The absolute path, where the action should be happen.
         |  @param  array   The requested data array.
         |                      'path'      The base path, where the files should be uploaded.
         |                      'revision'  True if the upload is a revision.
         |                      'overwrite' True to overwrite the existing files.
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        protected function _upload(string $path, array $data) {
            global $media_plugin;
            global $media_manager;
            global $media_history;

            // Check Arguments
            if(empty($_FILES["media"])) {
                return $this->response(false, bt_a("The action :action was called incorrectly.", [":action" => "/upload"]));
            }

            // Check Root
            if($path === PAW_MEDIA_ROOT && !$media_plugin->getValue("allow_root_upload")) {
                return $this->response(false, bt_("You cannot upload files to the root directory."));
            }


            // Set Data
            $files = $_FILES["media"];
            $errors = [];
            $success = [];
            $revision = ($data["revision"] ?? "0") === "1";
            $overwrite = ($data["overwrite"] ?? "0") === "1";

            // Loop Files
            $count = is_array($files["name"])? count($files["name"]): 1;
            for($i = 0; $i < $count; $i++) {
                if(is_array($files["name"])) {
                    [$name, $type, $tmp, $error, $size] = array_column($files, $i);
                } else {
                    [$name, $type, $tmp, $error, $size] = array_values($files);
                }

                // Upload File
                if(($status = $media_manager->upload($path, [$name, $type, $tmp, $error, $size], $overwrite, $revision)) !== true) {
                    $errors[$name] = $status;
                    continue;
                }
                $success[MediaManager::slug($media_manager->lastFile[3])] = $media_manager->lastFile[0];

                // Handle History
                if(is_file($path) && file_exists($path) && $overwrite) {
                    $media_history->log("revise", ($media_manager->lastRevise)? MediaManager::slug($media_manager->lastRevise): null, MediaManager::slug($path));
                }
            }

            // Error
            if(!empty($errors)) {
                $msg = bt_n("The file could not be uploaded.", "Not all files could be uploaded", $count);
            } else {
                $msg = bt_n("The file could be successfully uploaded.", "The files could be successfully uploaded.", $count);
            }

            // Success
            return $this->response(true, $msg, [
                "path"      => $path,
                "files"     => $success,
                "errors"    => $errors
            ]);
        }

        /*
         |  METHOD :: CREATE FOLDER OR FILE
         |  @type   POST
         |  @since  0.1.0
         |
         |  @param  string  The absolute path, where the action should be happen.
         |  @param  array   The requested data array.
         |                      'item'      The new directory or file name.
         |                      'type'      The type of item 'file' or 'folder'.
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        protected function _create(string $path, array $data) {
            global $media_manager;

            // Check Arguments
            if(!isset($data["item"]) || !in_array(($data["type"] ?? ""), ["file", "folder"])) {
                return $this->response(false, bt_a("The action :action was called incorrectly.", [":action" => "/create"]));
            }

            // Create Item
            if(($status = $media_manager->create($path, $data["item"], $data["type"])) !== true) {
                return $this->response(false, $status);
            }

            // Success
            return $this->response(true, bt_a("The new item ':name' could be successfully created.", [':name' => $data["item"]]), [
                "path"  => $path,
                "item"  => MediaManager::slug($path . DS . $data["item"]),
                "type"  => $data["type"]
            ]);
        }


        /*
         |  METHOD :: MOVE FOLDER OR FILE
         |  @type   POST
         |  @since  0.1.0
         |
         |  @param  string  The absolute path, where the action should be happen.
         |  @param  array   The requested data array.
         |                      'newpath'   The new path (without the file or directory name).
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        protected function _move(string $path, array $data) {
            global $media_manager;
            global $media_history;

            // Check Arguments
            if(!isset($data["newpath"])) {
                return $this->response(false, bt_a("The action :action was called incorrectly.", [":action" => "/move"]));
            }
            if(($data["newpath"] = MediaManager::absolute($data["newpath"])) === null) {
                return $this->response(false, bt_("The passed new path is invalid or does not exist."));
            }
            $slug = MediaManager::slug($path);

            // Move Item
            if(($status = $media_manager->move($path, $data["newpath"])) !== true) {
                return $this->response(false, $status);
            }

            // Handle History
            if(file_exists($data["newpath"] . DS . basename($path))) {
                $media_history->log("move", $slug, MediaManager::slug($data["newpath"] . DS . basename($path)));
            }

            // [PLUS] Handle Favourites
            if(method_exists($this, "updateFavorites")) {
                $this->updateFavorites($slug, MediaManager::slug($data["newpath"] . DS . basename($path)));
            }

            // Success
            return $this->response(true, bt_("The item could be moved successfully."), [
                "path"  => $data["newpath"] . DS . basename($path)
            ]);
        }

        /*
         |  METHOD :: RENAME FOLDER OR FILE
         |  @type   POST
         |  @since  0.2.0
         |
         |  @param  string  The absolute path, where the action should be happen.
         |  @param  array   The requested data array.
         |                      'newname'   The new folder or file name.
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        protected function _rename(string $path, array $data) {
            global $media_manager;
            global $media_history;

            // Check Arguments
            if(!isset($data["newname"])) {
                return $this->response(false, bt_a("The action :action was called incorrectly.", [":action" => "/rename"]));
            }
            $slug = MediaManager::slug($path);

            // Rename Item
            if(($status = $media_manager->move($path, dirname($path), $data["newname"])) !== true) {
                return $this->response(false, $status);
            }

            // Handle History
            if(file_exists(dirname($path) . DS . $data["newname"])) {
                $media_history->log("rename", $slug, MediaManager::slug(dirname($path) . DS . $data["newname"]));
            }

            // [PLUS] Handle Favourites
            if(method_exists($this, "updateFavorites")) {
                $this->updateFavorites($slug, MediaManager::slug(dirname($path) . DS . $data["newname"]));
            }

            // Success
            $this->rename = basename($path); // @todo find a better fix
            return $this->response(true, bt_("The item could be renamed successfully."), [
                "path"  => dirname($path) . DS . $data["newname"]
            ]);
        }

        /*
         |  METHOD :: DELETE FILEs
         |  @type   POST
         |  @since  0.1.0
         |
         |  @param  string  The absolute path, where the action should be happen.
         |  @param  array   The requested data array.
         |                      'recursive' Remove a folder recursive (if it is not empty).
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        protected function _delete(string $path, array $data) {
            global $media_manager;
            global $media_history;

            // Check Root
            $root = str_replace(PATH_UPLOADS, "", $path);
            if(in_array($root, ["media", "pages", "profiles", "thumbnails"])) {
                return $this->response(false, bt_("You cannot remove a system folder."));
            }
            $slug = MediaManager::slug($path);

            // Delete Path
            if(($status = $media_manager->delete($path, ($data["recursive"] ?? "0") === "1")) !== true) {
                return $this->response(false, $status);
            }

            // [PLUS] Handle Favourites
            if(method_exists($this, "updateFavorites")) {
                $this->updateFavorites($slug, null);
            }

            // Handle History
            $media_history->delete($slug);

            // Success
            return $this->response(true, bt_("The item could be deleted successfully."), [
                "path" => dirname($path)
            ]);
        }

        /*
         |  METHOD :: LIST CONTENT
         |  @type   GET | POST
         |  @since  0.1.0
         |
         |  @param  string  The absolute path, where the action should be happen.
         |  @param  array   The requested data array.
         |                      'mode'      Pass 'show' to show or 'edit' to edit supported
         |                                  text / content files on the details view.
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        protected function _list(string $path, array $data) {
            global $media_manager;

            // Render Content
            if(is_file($path)) {
                $content = $this->renderFile($path, $data["mode"] ?? null);
            } else {
                $content = $this->renderList($media_manager->list($path), $path);
            }

            // Success
            return $this->response(true, bt_("The passed Path is valid"), [
                "path"      => $path,
                "temp"      => !file_exists($path),
                "content"   => $content
            ]);
        }

        /*
         |  RENDER :: LIST
         |  @since  0.1.0
         |
         |  @param  multi   The rendered files list or NULL if no file is available.
         |  @param  string  The current rendered list path.
         |
         |  @return string  The rendered list content.
         */
        public function renderList(?array $files = null, string $path = ""): string {
            global $security;
            global $media_plugin;

            // Layout Path
            $layouts = PAW_MEDIA_PATH . DS . "system" . DS . "layouts" . DS;

            // Prepare Pathdata
            if(($this->query["temp"] ?? "false") === "true") {
                if(($pathinfo = MediaManager::pathinfo(dirname($path))) === null) {
                    return "";
                }
                $pathinfo["absolute"] .= DS . basename($path);
                $pathinfo["relative"] .= DS . basename($path);
                $pathinfo["slug"] .= "/" . basename($path);
                $pathinfo["url"] .= "/" . basename($path);
                $pathinfo["basename"] = basename($path);
                $pathinfo["dirname"] = basename($path);
            } else {
                if(($pathinfo = MediaManager::pathinfo($path)) === null) {
                    return "";
                }
            }

            // Render Item
            ob_start();
            if($media_plugin->getValue("layout") === "table") {
                require $layouts . "table.php";
            } else if($media_plugin->getValue("layout") === "grid") {
                require $layouts . "grid.php";
            } else {
                echo bt_e("Layout HTML File could not be found!");
            }

            // Return
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }

        /*
         |  RENDER :: LIST ITEM
         |  @since  0.1.0
         |
         |  @param  string  The real path and filename to render.
         |  @param  string  The basename of the folder or filename.
         |  @param  bool    TRUE to use the file template to render, FALSE to do it not.
         |
         |  @return string  The rendered list item content.
         */
        public function renderItem(string $real, ?string $basename = null, bool $render_file = false): string {
            global $security;
            global $media_plugin;
            global $media_manager;
            global $media_history;

            // Prepare Pathdata
            if(($pathinfo = MediaManager::pathinfo($real)) === null) {
                return "";
            }

            // Prepare Values
            $file_mime = $pathinfo["type"] === "folder"? "folder": mime_content_type($real);
            $file_type = $pathinfo["type"] === "folder"? "folder": explode("/", $file_mime)[0];

            // Prepare Links
            $open = $this->buildURL("media", [
                "path" => $pathinfo["slug"]
            ]);
            $delete = $this->buildURL("media/delete", [
                "token"     => $security->getTokenCSRF(),
                "action"    => "delete",
                "path"      => $pathinfo["slug"]
            ]);
            if(PAW_MEDIA_PLUS) {
                $edit = $this->buildURL("media", [
                    "path"          => $pathinfo["slug"],
                    "mode"          => "edit"
                ]);
                $favorite = $this->buildURL("media/favorite", [
                    "token"     => $security->getTokenCSRF(),
                    "action"    => "favorite",
                    "path"      => $pathinfo["slug"]
                ]);
            }
            $mode = $_GET["mode"] ?? $_POST["mode"] ?? "list";

            // Set Data
            switch($file_type) {
                case "folder":
                    $icon = "fa fa-folder-o";
                    $text = bt_("Folder");
                    $color = "bg-secondary";
                    break;
                case "video":
                    $icon = "fa fa-file-video-o";
                    $text = bt_("Video");
                    $color = "bg-success";
                    break;
                case "audio":
                    $icon = "fa fa-file-audio-o";
                    $text = bt_("Audio");
                    $color = "bg-warning";
                    break;
                case "image":
                    $icon = "fa fa-file-image-o";
                    $text = bt_("Image");
                    $color = "bg-danger";
                    break;
                case "text":
                    $mimes = $media_manager::MIME_TYPES;
                    $codes = array_merge(
                        $mimes["text/css"], $mimes["text/html"], $mimes["text/xml"], $mimes["application/xhtml+xml"],
                        $mimes["text/javascript"], $mimes["text/typescript"], $mimes["application/json"], $mimes["text/x-php"]
                    );
                    $icon = (in_array($pathinfo["extension"], $codes))? "fa fa-file-code-o": "fa fa-file-text-o";
                    $text = (in_array($pathinfo["extension"], $codes))? bt_("Code"): bt_("Text");
                    $color = (in_array($pathinfo["extension"], $codes))? "bg-info": "bg-primary";

                    break;
                default:
                    $archives = [".bz", ".bz2", ".gz", ".rar", ".tar", ".zip", ".7z"];
                    $icon = (in_array($pathinfo["extension"], $archives))? "fa fa-file-archive-o": "fa fa-file-o";
                    $text = (in_array($pathinfo["extension"], $archives))? bt_("Archive"): bt_("File");
                    $color = (in_array($pathinfo["extension"], $archives))? "bg-secondary": "bg-primary";
                    break;
            }

            // Layout Path
            $layouts = PAW_MEDIA_PATH . DS . "system" . DS . "layouts" . DS;

            // Render Item
            ob_start();
            if($render_file === true) {
                require $layouts . "details.php";
            } else {
                if($media_plugin->getValue("layout") === "table") {
                    require $layouts . "table-item.php";
                } else if($media_plugin->getValue("layout") === "grid") {
                    require $layouts . "grid-item.php";
                } else {
                    echo bt_e("Layout HTML File could not be found!");
                }
            }

            // Return
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }

        /*
         |  RENDER :: DETAILS VIEW
         |  @since  0.1.0
         |
         |  @param  string  The path to the file.
         |
         |  @return string  The rendered details-view content.
         */
        public function renderFile($file) {
            return $this->renderItem($file, $file, true);
        }
    }
