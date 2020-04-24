<?php
declare(strict_types=1);
/*
 |  Media       An advanced Media & File Manager for Bludit
 |  @file       ./system/admin.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.0 [0.1.0] - Alpha
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
    if(!defined("BLUDIT")) { die("Go directly to Jail. Do not pass Go. Do not collect 200 Cookies!"); }

    class MediaAdmin {
        const SE_STATUS = "media-status";
        const SE_MESSAGE = "media-message";
        const SE_DATA = "media-data";

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
            $this->ajax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? "") === "xmlhttprequest";
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
        public function buildURL(string $path, array $query = [ ], $replace = true): string {
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
         |  METHOD :: MOVE FILEs
         |  @since  0.1.0
         |
         |  @param  array   The requested data array.
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        protected function _move(array $data) {
            global $media_plugin;
            global $media_manager;

            // Rename a Folder or File
            if(isset($data["path"], $data["name"], $data["rename"])) {
                if(($path = MediaManager::absolute($data["path"])) === null) {
                    return $this->bye(false, "The passed path is invalid.");
                }

                // Prepare Query
                $query = ["path" => MediaManager::slug($path)];
                if(strpos($_SERVER["HTTP_REFERER"] ?? "", "&file=") !== false) {
                    $query["file"] = $data["name"];
                }

                //@todo move this function to the MediaManager - media.php

                // Check if File / Folder exists
                if(!file_exists($path . DIRECTORY_SEPARATOR . $data["name"])) {
                    return $this->bye(false, paw__("The requested folder or file '%s' doesn't exist.", [$data["name"]]), $query);
                }

                // Check if renamed version exists
                if(file_exists($path . DIRECTORY_SEPARATOR . $data["rename"])) {
                    return $this->bye(false, paw__("The new folder or file name '%s' does already exist.", [$data["rename"]]), $query);
                }

                // Check File Extension
                [$old, $new] = [substr($data["name"], strrpos($data["name"], ".")), substr($data["rename"], strrpos($data["rename"], "."))];
                if(($data["force"] ?? "0") !== "1" && strtolower($old) !== strtolower($new)) {
                    return $this->bye(false, paw__("The file extension '%s' must not be changed.", [$old]), $query);
                }

                // Change Favorite
                if(method_exists($this, "isFavorite") && $this->isFavorite($data["name"])) {
                    $this->setFavorite($data["name"]);
                    $this->setFavorite($data["rename"]);
                }

                // Try to rename
                if(!@rename($path . DIRECTORY_SEPARATOR . $data["name"], $path . DIRECTORY_SEPARATOR . $data["rename"])) {
                    return $this->bye(false, paw__("The folder for file '%s' could not be renamed.", [$data["name"]]), $query);
                }

                // Success
                if(is_file($path . DIRECTORY_SEPARATOR . $data["rename"])) {
                    $query["file"] = $data["rename"];
                    $query["content"] = $this->renderFile($path . DIRECTORY_SEPARATOR . $data["rename"]);
                }
                return $this->bye(true, paw__("The folder for file could be successfully renamed."), $query);
            }

            // Error
            return $this->bye(false, paw__("The action was called incorrectly."));
        }

        /*
         |  METHOD :: UPLOAD FILEs
         |  @since  0.1.0
         |
         |  @param  array   The requested data array.
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        protected function _upload(array $data) {
            global $media_plugin;
            global $media_manager;

            // Check Files
            if(empty($_FILES["media"])) {
                return $this->bye(false, paw__("The upload request is invalid or empty."));
            }

            // Check Path
            if(empty($data["path"] ?? "") && !$media_plugin->getValue("allow_root_upload")) {
                return $this->bye(false, paw__("You cannot upload files to the root directory."));
            }

            // Create Path
            $path = $data["path"] ?? "";
            if(strpos($path, "?create") !== false) {
                $path = explode("?", $path)[0];
                $base = MediaManager::absolute(dirname($path)) . DS . basename($path);

                if(!Filesystem::directoryExists($base)) {
                    Filesystem::mkdir($base, true);
                }
            }

            // Validate Path
            $path = realpath(PATH_UPLOADS_PAGES . $path);
            if(!$path || strpos($path, realpath(PATH_UPLOADS_PAGES)) === false) {
                return $this->bye(false, paw__("The passed path is invalid."));
            }

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
                } else {
                    $content[$media_manager->lastFile[0]] = $this->renderItem($media_manager->lastFile[3]);
                }
            }

            // Return Success
            if(empty($errors)) {
                $query = [
                    "items" => $content,
                    "path"  => MediaManager::slug($path),
                    "file"  => $name
                ];

                if($count === 1) {
                    return $this->bye(true, paw__("The upload was successfully."), $query);
                }
                return $this->bye(true, paw("The upload of all %d files was successfully.", [$count]), $query);
            }

            // Return Error
            if($count === 1) {
                return $this->bye(false, paw__("The file could not be uploaded successfully."), ["errors" => $errors]);
            } else if($count === count($errors)) {
                return $this->bye(false, paw__("No single file could be uploaded successfully."), ["errors" => $errors]);
            }
            return $this->bye(false, paw__("Not all files could be uploaded successfully."), ["errors" => $errors]);
        }

        /*
         |  METHOD :: CREATE FILEs
         |  @since  0.1.0
         |
         |  @param  array   The requested data array.
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        protected function _create(array $data) {
            global $media_manager;

            // Validate Path
            $path = realpath(PATH_UPLOADS_PAGES . ($data["path"] ?? "<!>)"));
            if(!$path || strpos($path, realpath(PATH_UPLOADS_PAGES)) === false) {
                return $this->bye(false, paw__("The passed path is invalid."));
            }

            // Create Directory
            if(!empty($data["folder"] ?? "")) {
                if(($status = $media_manager->createDir($path, $data["folder"])) !== true) {
                    return $this->bye(false, $status);
                }

                $base = str_replace(PATH_UPLOADS_PAGES, "", $path . DIRECTORY_SEPARATOR . $data["folder"]);
                $query = [
                    "path" => str_replace("\\", "/", $base),
                    "items" => [
                        "$base" => $this->renderItem($path . DIRECTORY_SEPARATOR . $data["folder"])
                    ]
                ];
                return $this->bye(true, paw__("The new folder '%s' could be created.", [$_POST['folder']]), $query);
            }

            // Create File
            if(!empty($data["file"] ?? "")) {
                if(($status = $media_manager->createFile($path, $data["file"], $data["file-content"] ?? "")) !== true) {
                    return $this->bye(false, $status);
                }
                //@todo Path to new file
                return $this->bye(true, paw__("The new file '%s' could be created.", [$data["file"]]));
            }

            // Error
            return $this->bye(false, paw__("The action was called incorrectly."));
        }

        /*
         |  METHOD :: UPDATE FILEs
         |  @since  0.1.0
         |
         |  @param  array   The requested data array.
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        protected function _update(array $data) {

        }

        /*
         |  METHOD :: DELETE FILEs
         |  @since  0.1.0
         |
         |  @param  array   The requested data array.
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        protected function _delete(array $data) {
            global $media_manager;

            // Set Data
            $rebase = "";
            $errors = [];
            $recursive = ($data["recursive"] ?? "0") === "1";

            // Delete Directories
            $folders = $data["folders"] ?? $data["folder"] ?? "";
            if(!empty($folders)) {
                $folders = !is_array($folders)? [$folders]: $folders;
                foreach($folders AS &$dir) {
                    if(method_exists($this, "isFavorite") && $this->isFavorite($dir)) {
                        $this->setFavorite($dir);
                    }
                    $dir2 = MediaManager::slug($dir);

                    // Handle
                    if(($status = $media_manager->deleteDir($dir, $recursive)) !== true) {
                        $errors[] = $status;
                    } else if(empty($rebase)) {
                        $rebase = dirname($dir);
                    }
                    $dir = $dir2;
                }
                $type = ["folder", "folders"];
            }

            // Delete Files
            $files = $data["files"] ?? $data["file"] ?? "";
            if(!empty($files)) {
                $files = !is_array($files)? [$files]: $files;
                foreach($files AS &$file) {
                    if(method_exists($this, "isFavorite") && $this->isFavorite($file)) {
                        $this->setFavorite($file);
                    }
                    $file2 = MediaManager::slug($file);

                    // Handle
                    if(($status = $media_manager->deleteFile($file)) !== true) {
                        $errors[] = $status;
                    } else if(empty($rebase)) {
                        $rebase = dirname($file);
                    }
                    $file = $file2;
                }
                $type = ["file", "files"];
            }

            // No Action applied
            if(!isset($type)) {
                return $this->bye(false, paw__("The action was called incorrectly."));
            }

            // Return Single
            if(empty($errors)) {
                if(count(${$type[1]}) > 1) {
                    return $this->bye(true, paw__("The %s could be successfullfy deleted.", [$type[1]]), [
                        "path"  => $rebase,
                        "type"  => $type[0],
                        "items" => ${$type[1]}
                    ]);
                }
                return $this->bye(true, paw__("The %s could be successfullfy deleted.", [$type[0]]), [
                    "path"  => $rebase,
                    "type"  => $type[0],
                    "items" => ${$type[1]}
                ]);
            }

            // Return Multiple
            if(count($errors) === count(${$type[1]})) {
                return $this->bye(false, paw__("No single %s could be deleted", [$type[0]]), ["errors" => $errors]);
            }
            return $this->bye(false, paw__("One or more %s could not be deleted", [$type[1]]), ["errors" => $errors]);
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
                return $this->bye(false, paw__("The action was called incorrectly."));
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
                    return $this->bye(true, paw__("The path is valid."), ["content" => $content, "path" => $base]);
                }
                return $this->bye(false, paw__("The passed path is invalid."));
            }
            $base = MediaManager::slug($path);

            // Render File
            if(isset($data["file"]) && file_exists($path . DIRECTORY_SEPARATOR . $data["file"])) {
                $content = $this->renderFile($path . DIRECTORY_SEPARATOR . $data["file"]);
                return $this->bye(true, paw__("The file is valid."), ["content" => $content, "path" => $base, "file" => $data["file"]]);
            }

            // Render
            $content = $this->renderList($media_manager->list($base), $base);
            return $this->bye(true, paw__("The path is valid."), ["content" => $content, "path" => $base]);
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
            } else if($_POST["media_action"]) {
                $data = $_POST;
            } else {
                return $this->bye(false, paw__("The request is invalid or empty."));
            }

            // Check CSRF Token
            if($security->validateTokenCSRF($data["nonce"] ?? $data["tokenCSRF"] ?? "") === false) {
                return $this->bye(false, paw__("The CSRF token is invalid or missing."));
            }

            // Check Action
            if($data["media_action"] !== $this->method || !method_exists($this, "_" . $this->method)) {
                return $this->bye(false, paw__("The passed action is invalid or does not match."));
            }

            // Handle Request
            return $this->{"_" . $this->method}($data);
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
            $_SESSION["media-status"] = $status;
            $_SESSION["media-message"] = $message;
            $_SESSION["media-data"] = $data;

            // Prepare
            $query = ["status" => $status? "success": "error"];
            if(!empty($data["path"] ?? "")) {
                $path = str_replace("\\", "/", str_replace(PATH_UPLOADS_PAGES, "", $data["path"]));
                $query["path"] = $path;
            }
            if(!empty($data["file"] ?? "")) {
                $query["file"] = basename($data["file"]);
            }

            // Redirect
            Redirect::url($this->buildURL("media", $query, true));
            die();
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

            // Render Item
            ob_start();
            if($media_plugin->getValue("layout") === "table") {
                require $layouts . "table.php";
            } else if($media_plugin->getValue("layout") === "grid") {
                require $layouts . "grid.php";
            } else {
                echo paw_e("Layout HTML File could not be found!");
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
         |  @param  string  The path and filename to render.
         |  @param  string  The real path and filename, if file is a link.
         |  @param  bool    TRUE to use the file template to render, FALSE to do it not.
         |
         |  @return string  The rendered list item content.
         */
        public function renderItem(string $file, ?string $real = null, bool $render_file = false): string {
            global $url;
            global $security;
            global $media_plugin;
            global $media_manager;

            $real = $real ?? $file;

            // Layout Path
            $layouts = PAW_MEDIA_PATH . DS . "system" . DS . "layouts" . DS;

            // Prepare Base Values
            $path = str_replace(PATH_UPLOADS_PAGES, "", $real);
            $type = is_file($file)? "file": "folder";
            $dirname = ($type === "file")? dirname($path): $path;
            $basename = basename($real);
            $slug = str_replace("\\", "/", $path);
            $source = DOMAIN_UPLOADS_PAGES . $slug;

            // Prepare File Values
            if(is_file($file)) {
                $file_mime = mime_content_type($file);
                $file_type = explode("/", $file_mime)[0];
                $file_ext = substr($file, strrpos($file, "."));
            } else {
                $file_mime = "folder";
                $file_type = "folder";
                $file_ext = "";
            }

            // Prepare Links
            $folder = $this->buildURL("media",[
                "path" => str_replace("\\", "/", $dirname)
            ]);
            $details = $this->buildURL("media", [
                "path" => str_replace("\\", "/", $dirname),
                "file" => $basename
            ]);
            $delete = $this->buildURL("media/delete", [
                "nonce"         => $security->getTokenCSRF(),
                "media_action"  => "delete",
                "$type"         => $path
            ]);

            // Set Data
            switch($file_type) {
                case "folder":
                    $icon = "fa fa-folder-o";
                    $text = paw__("Folder");
                    $color = "bg-secondary";
                    break;
                case "video":
                    $icon = "fa fa-file-video-o";
                    $text = paw__("Video");
                    $color = "bg-success";
                    break;
                case "audio":
                    $icon = "fa fa-file-audio-o";
                    $text = paw__("Audio");
                    $color = "bg-warning";
                    break;
                case "image":
                    $icon = "fa fa-file-image-o";
                    $text = paw__("Image");
                    $color = "bg-danger";
                    break;
                case "text":
                    $mimes = $media_manager::MIME_TYPES;
                    $codes = array_merge(
                        $mimes["text/css"], $mimes["text/html"], $mimes["text/xml"], $mimes["application/xhtml+xml"],
                        $mimes["text/javascript"], $mimes["text/typescript"], $mimes["application/json"], $mimes["text/x-php"]
                    );
                    $icon = (in_array($file_ext, $codes))? "fa fa-file-code-o": "fa fa-file-text-o";
                    $text = (in_array($file_ext, $codes))? paw__("Code"): paw__("Text");
                    $color = (in_array($file_ext, $codes))? "bg-info": "bg-primary";

                    break;
                default:
                    $archives = [".bz", ".bz2", ".gz", ".rar", ".tar", ".zip", ".7z"];
                    $icon = (in_array($file_ext, $archives))? "fa fa-file-archive-o": "fa fa-file-o";
                    $text = (in_array($file_ext, $archives))? paw__("Archive"): paw__("File");
                    $color = (in_array($file_ext, $archives))? "bg-secondary": "bg-primary";
                    break;
            }

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
                    echo paw_e("Layout HTML File could not be found!");
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
