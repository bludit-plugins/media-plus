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
         |  CURRENT PATH
         */
        public $path = "";

        /*
         |  CURRENT VIEW
         */
        public $view = "";

        /*
         |  CURRENT METHOD
         */
        public $method = "";

        /*
         |  CURRENT QUERY
         */
        public $query = [ ];

        /*
         |  IS AJAX REQUEST?
         */
        public $ajax = false;

        /*
         |  LATEST STATUS
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
         |  HANDLER :: SUBMIT
         |  @since  0.1.0
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        public function submit(): bool {
            global $security;

            // Get GLOBAL of request
            if(isset($_GET["media_action"])) {
                $data = $_GET;
            } else if(isset($_POST["media_action"])) {
                $data = $_POST;
            } else {
                return $this->bye(false, bt_("The request is invalid or empty."));
            }

            // Check CSRF Token
            if($security->validateTokenCSRF($data["nonce"] ?? $data["tokenCSRF"] ?? "") === false) {
                return $this->bye(false, bt_("The CSRF token is invalid or missing."));
            }

            // Check Action
            if($data["media_action"] !== $this->method || !method_exists($this, "_{$this->method}")) {
                return $this->bye(false, bt_("The passed action is invalid or does not match."));
            }

            // Handle Request
            return $this->{"_{$this->method}"}($data);
        }

        /*
         |  HANDLER :: BYE
         |  @since  0.1.0
         |
         |  @param  bool    TRUE if the request was success, FALSE if not.
         |  @param  string  The status message.
         |  @param  array   The additional data array for AJAX requests.
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        public function bye(bool $status, string $message, array $data = []) {
            if($this->ajax) {
                header(($status)? "HTTP/1.1 200 OK": "HTTP/1.1 400 Bad Request");
                print(json_encode([
                    "status"    => $status? "success": "error",
                    "message"   => $message,
                    "data"      => $data
                ]));
                die();
            }

            // Add Message
            $_SESSION[self::SE_STATUS] = $status;
            $_SESSION[self::SE_MESSAGE] = $message;
            $_SESSION[self::SE_DATA] = $data;

            // Prepare
            $query = ["status" => $status? "success": "error"];
            if(!empty($data["path"] ?? "")) {
                $path = str_replace("\\", "/", str_replace(PAW_MEDIA_ROOT, "", $data["path"]));
                $query["path"] = $path;
            }

            // Redirect
            Redirect::url($this->buildURL("media", $query, true));
            die();
        }

        /*
         |  METHOD :: CREATE FOLDER OR FILE
         |  @since  0.1.0
         |
         |  @param  array   The requested data array (use 'folder' OR 'file').
         |                      'path'      The current base path
         |                      'folder'    The new directory name.
         |                      'file'      The new file name.
         |                      'content'   The new file content.
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        protected function _create(array $data) {
            global $media_manager;

            // Check Arguments
            if(!isset($data["path"]) || (!isset($data["folder"]) && !isset($data["file"]))) {
                return $this->bye(false, bt_("The action was called incorrectly."));
            }

            // Check Path
            if(($path = MediaManager::absolute($data["path"])) === null) {
                return $this->bye(false, bt_("The passed path is invalid."));
            }
            $slug = MediaManager::slug($path);
            $type = is_file($path)? bt_("file"): bt_("directory");
            $query = ["path" => $path];

            // Create
            $content = !empty($data["folder"])? null: $data["content"] ?? "";
            if(($status = $media_manager->create($path, $data["folder"] ?? $data["file"], $content)) !== true) {
                return $this->bye(false, $status, $query);
            }

            // Prepare Query
            $query["path"] = $path . DS . ($data["folder"] ?? "");

            // Success
            return $this->bye(true, bt_a("The new :type ':name' could be created.", [':type' => $type, ':name' => $data["folder"] ?? $data["file"]]), $query);
        }

        /*
         |  METHOD :: MOVE DIRECTORY OR FILE [UNUSED]
         |  @todo   Implement within Edit Modal
         |  @since  0.1.0
         |
         |  @param  array   The requested data array.
         |                      'path'      The current path (with file or directory name).
         |                      'move'      The new path (without the file or directory name).
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        protected function _move(array $data) {
            global $media_history;

            // Check Arguments
            if(!isset($data["path"]) || !isset($data["move"])) {
                return $this->bye(false, bt_("The action was called incorrectly."));
            }

            // Check Path
            if(($path = MediaManager::absolute($data["path"])) === null) {
                return $this->bye(false, bt_("The passed path is invalid."));
            }
            $slug = MediaManager::slug($path);
            $type = is_file($path)? bt_("file"): bt_("directory");

            // Check Arguments
            if(($move = MediaManager::absolute($data["move"])) === null) {
                return $this->bye(false, bt_("The passed path is invalid."));
            }
            if(file_exists($move . DS . basename($path))) {
                return $this->bye(false, bt_a("The new location already contains ':name'.", [':name' => basename($path)]));
            }
            $path_new = $move . DS . basename($path);

            // Move File or Directory
            if(!Filesystem::mv($path, $path_new)) {
                return $this->bye(false, bt_a("The :type could not be moved.", [":type" => $type]));
            }
            if(file_exists($path_new)) {
                $media_history->log("rename", $slug, MediaManager::slug($path_new));
            }

            // [PLUS] Handle Favourites
            if(method_exists($this, "updateFavorites")) {
                $this->updateFavorites($slug, MediaManager::slug($path_new));
            }

            // Prepare Query
            $query = ["path" => $move];
            if(is_file($path) && strpos($_SERVER["HTTP_REFERER"] ?? "", basename($path)) !== false) {
                $query["path"] .= DS . basename($path);
                $query["file"] = basename($path);

                if($this->ajax) {
                    $query["content"] = $this->renderFile($path_new);
                }
            }

            // Return on Success
            return $this->bye(true, bt_a("The :type could be successfully moved.", [":type" => $type]), $query);
        }

        /*
         |  METHOD :: RENAME DIRECTORY OR FILE
         |  @since  0.2.0
         |
         |  @param  array   The requested data array.
         |                      'path'      The current path (with file or directory name).
         |                      'rename'    The new directory or file name.
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        protected function _rename(array $data) {
            global $media_history;

            // Check Arguments
            if(!isset($data["path"]) || !isset($data["rename"])) {
                return $this->bye(false, bt_("The action was called incorrectly."));
            }

            // Check Path
            if(($path = MediaManager::absolute($data["path"])) === null) {
                return $this->bye(false, bt_("The passed path is invalid."));
            }
            $slug = MediaManager::slug($path);
            $type = is_file($path)? bt_("file"): bt_("directory");
            $query = ["path" => dirname($path)];

            // Check Arguments
            if(strpbrk($data["rename"], "\\/?%*:|\"<>") !== false) {
                return $this->bye(false, bt_("The passed new name is invalid."), $query);
            }
            if(file_exists(dirname($path) . DS . $data["rename"])) {

                // The Windows File System works completely case-insensitive, so we need to
                // check the realpath again (ex.: User wants to just change the letter cases).
                if(strcmp(realpath(dirname($path) . DS . $data["rename"]), dirname($path) . DS . $data["rename"]) === 0) {
                    return $this->bye(false, bt_a("The new name ':name' does already exist.", [':name' => basename($path)]), $query);
                }
            }
            $path_new = dirname($path) . DS . $data["rename"];

            // Rename File or Directory
            if(!Filesystem::mv($path, $path_new)) {
                return $this->bye(false, bt_a("The :type could not be renamed.", [":type" => $type]), $query);
            }
            if(file_exists($path_new)) {
                $media_history->log("rename", $slug, MediaManager::slug($path_new));
            }

            // [PLUS] Handle Favourites
            if(method_exists($this, "updateFavorites")) {
                $this->updateFavorites($slug, MediaManager::slug($path_new));
            }

            // Prepare Query
            $query = ["path" => dirname($path_new)];
            if(is_file($path_new) && strpos($_SERVER["HTTP_REFERER"] ?? "", basename($path)) !== false) {
                $query["path"] .= DS . basename($path_new);
                $query["file"] = basename($path_new);

                if($this->ajax) {
                    $query["content"] = $this->renderFile($path_new);
                }
            }

            // Return on Success
            return $this->bye(true, bt_a("The :type could be successfully renamed.", [":type" => $type]), $query);
        }

        /*
         |  METHOD :: DELETE FILEs
         |  @since  0.1.0
         |
         |  @param  array   The requested data array.
         |                      'path'      The current path (with file or directory name).
         |                      'recursive' Remove a folder recursive (if it is not empty).
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        protected function _delete(array $data) {
            global $media_manager;

            // Check Arguments
            if(!isset($data["path"])) {
                return $this->bye(false, bt_("The action was called incorrectly."));
            }

            // Check Path
            if(($path = MediaManager::absolute($data["path"])) === null) {
                return $this->bye(false, bt_("The passed path is invalid."));
            }
            $slug = MediaManager::slug($path);
            $type = is_file($path)? bt_("file"): bt_("directory");

            // Check Root
            $root = str_replace(PATH_UPLOADS, "", $path);
            if(in_array($root, ["media", "pages", "profiles", "thumbnails"])) {
                return $this->bye(false, bt_("You cannot remove a system folder."));
            }

            // Prepare Query
            $query = ["path" => dirname($path)];
            if(is_file($path)) {
                $query["file"] = basename($path);
            }

            // Delete Path
            if(($status = $media_manager->delete($path, ($data["recursive"] ?? "0") === "1")) !== true) {
                return $this->bye(false, $status, $query);
            }

            // [PLUS] Handle Favourites
            if(method_exists($this, "updateFavorites")) {
                $this->updateFavorites($slug, null);
            }

            // Success
            return $this->bye(true, bt_a("The :type ':name' could be successfully deleted.", [':type' => $type, ':name' => basename($path)]), $query);
        }

        /*
         |  METHOD :: UPLOAD FILEs
         |  @since  0.1.0
         |
         |  @param  array   The requested data array.
         |                      'path'      The base path, where the files should be uploaded.
         |                      'revision'  True if the upload is a revision.
         |                      'overwrite' True to overwrite the existing files.
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        protected function _upload(array $data) {
            global $media_plugin;
            global $media_manager;
            global $media_history;

            // Check Files
            if(empty($_FILES["media"]) || !isset($data["path"])) {
                return $this->bye(false, bt_("The upload request is invalid or empty."));
            }

            // Create / Check Path
            if(strpos($data["path"], "?create") !== false) {
                $data["path"] = explode("?", $path)[0];
                if(($path = MediaManager::absolute(dirname($data["path"]))) === null) {
                    return $this->bye(false, bt_("The passed path is invalid."));
                }
                if(!Filesystem::directoryExists($path . DS . basename($data["path"]))) {
                    Filesystem::mkdir($path . DS . basename($data["path"]));
                    $path = $path . DS . basename($data["path"]);
                }
            } else {
                if(($path = MediaManager::absolute($data["path"])) === null) {
                    return $this->bye(false, bt_("The passed path is invalid."));
                }
            }

            // Check Root
            if($path === PAW_MEDIA_ROOT && !$media_plugin->getValue("allow_root_upload")) {
                return $this->bye(false, bt_("You cannot upload files to the root directory."));
            }
            $slug = MediaManager::slug($path);

            // Set Data
            $files = $_FILES["media"];
            $errors = [];
            $content = [];
            $revision = ($data["revision"] ?? "0") === "1";
            $overwrite = ($data["overwrite"] ?? "0") === "1";

            // Loop Files
            $count = is_array($files["name"])? count($files["name"]): 1;
            for($i = 0; $i < $count; $i++) {
                if(is_array($files["name"])) {
                    [$name, $type, $tmp, $error, $size] = [
                        $files["name"][$i], $files["type"][$i], $files["tmp_name"][$i], $files["error"][$i], $files["size"][$i]
                    ];
                } else {
                    [$name, $type, $tmp, $error, $size] = array_values($files);

                    if($overwrite && !empty($data["name"] ?? "")) {
                        $name = $data["name"];
                    }
                }

                // Upload File
                $status = $media_manager->upload($path, [$name, $type, $tmp, $error, $size], $overwrite, $revision);
                if($status !== true) {
                    $errors[] = $status;
                    continue;
                }

                // Success
                if(file_exists($path . DS . $name) && $overwrite) {
                    if($revision) {
                        $media_history->log("revise", MediaManager::slug($path . DS . $name), MediaManager::slug($path . DS . $media_manager->lastRevise));
                        $media_history->log("revised", MediaManager::slug($path . DS . $media_manager->lastRevise), MediaManager::slug($path . DS . $name));
                    } else {
                        $media_history->log("revise", MediaManager::slug($path . DS . $name), null);
                    }
                }
            }

            // Return Success
            if(empty($errors)) {
                $query = [
                    "path"  => $path
                ];
                if(strpos($_SERVER["HTTP_REFERER"], $data["name"] ?? "") !== false) {
                    $query["path"] .= DS . $data["name"];
                }


                if($count === 1) {
                    return $this->bye(true, bt_("The upload was successfully."), $query);
                }
                return $this->bye(true, bt_a("The upload of all :num files was successfully.", [':num' => $count]), $query);
            }

            // Return Error
            if($count === 1) {
                return $this->bye(false, bt_("The file could not be uploaded successfully."), ["errors" => $errors]);
            } else if($count === count($errors)) {
                return $this->bye(false, bt_("No single file could be uploaded successfully."), ["errors" => $errors]);
            }
            return $this->bye(false, bt_("Not all files could be uploaded successfully."), ["errors" => $errors]);
        }

        /*
         |  METHOD :: LIST CONTENT
         |  @since  0.1.0
         |
         |  @param  array   The requested data array.
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        protected function _list(array $data) {
            global $media_manager;

            // Check if Ajax
            if(!$this->ajax) {
                return $this->bye(false, bt_("The action was called incorrectly."));
            }

            // Validate Path
            if(($path = MediaManager::absolute($data["path"] ?? "")) === null) {
                if(($data["create"] ?? "false") === "true") {
                    $base = dirname($data["path"] ?? "");
                    if(strlen($base) === 0 || $base === ".") {
                        $base = "/";
                    }
                    $base = rtrim(MediaManager::slug($base), "/") . "/" . basename($data["path"]);
                    $base .= "?create=true";
                    $content = $this->renderList([], $base);
                    return $this->bye(true, bt_("The path is valid."), ["content" => $content, "path" => $base]);
                }
                return $this->bye(false, bt_("The passed path is invalid."));
            }
            $base = MediaManager::slug($path);

            // Render File
            if(is_file($path)) {
                $content = $this->renderFile($path);
                return $this->bye(true, bt_("The file is valid."), ["content" => $content, "path" => $base, "file" => basename($path)]);
            }

            // Render Directory
            $content = $this->renderList($media_manager->list($base), $base);
            return $this->bye(true, bt_("The path is valid."), ["content" => $content, "path" => $base]);
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
            if(($pathinfo = MediaManager::pathinfo($path)) === null) {
                return "";
            }
            extract($pathinfo);

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
            extract($pathinfo);

            // Prepare Values
            $file_mime = $type === "folder"? "folder": mime_content_type($real);
            $file_type = $type === "folder"? "folder": explode("/", $file_mime)[0];

            // Prepare Links
            $open = $this->buildURL("media", [
                "path" => $slug
            ]);
            $delete = $this->buildURL("media/delete", [
                "nonce"         => $security->getTokenCSRF(),
                "media_action"  => "delete",
                "path"          => $slug
            ]);
            if(PAW_MEDIA_PLUS) {
                $edit = $this->buildURL("media", [
                    "path"          => $slug,
                    "edit"          => "true"
                ]);
                $favorite = $this->buildURL("media/favorite", [
                    "nonce"         => $security->getTokenCSRF(),
                    "media_action"  => "favorite",
                    "path"          => $slug
                ]);
            }

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
                    $icon = (in_array($extension, $codes))? "fa fa-file-code-o": "fa fa-file-text-o";
                    $text = (in_array($extension, $codes))? bt_("Code"): bt_("Text");
                    $color = (in_array($extension, $codes))? "bg-info": "bg-primary";

                    break;
                default:
                    $archives = [".bz", ".bz2", ".gz", ".rar", ".tar", ".zip", ".7z"];
                    $icon = (in_array($extension, $archives))? "fa fa-file-archive-o": "fa fa-file-o";
                    $text = (in_array($extension, $archives))? bt_("Archive"): bt_("File");
                    $color = (in_array($extension, $archives))? "bg-secondary": "bg-primary";
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
