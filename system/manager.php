<?php
declare(strict_types=1);
/*
 |  Media       The advanced Media & File Manager for Bludit
 |  @file       ./system/manager.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.2.0 [0.1.0] - Beta
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
    defined("BLUDIT") or die("Go directly to Jail. Do not pass Go. Do not collect 200 Cookies!");

    // Main File Handler
    class MediaManager {
        /*
         |  MIME/TYPE => [".allowed", ".extensions"]
         */
        const MIME_TYPES = [
            "audio/aac" => [".aac"],
            "audio/midi" => [".mid", ".midi"],
            "audio/x-midi" => [".mid", ".midi"],
            "audio/mpeg" => [".mp3"],
            "audio/ogg" => [".oga"],
            "audio/wav" => [".wav"],
            "audio/webm" => [".webm"],
            "image/bmp" => [".bmp"],
            "image/gif" => [".gif"],
            "image/jpeg" => [".jpg", ".jpeg"],
            "image/png" => [".png"],
            "image/svg+xml" => [".svg"],
            "image/tiff" => [".tif", ".tiff"],
            "image/vnd.microsoft.icon" => [".ico"],
            "image/webp" => [".webp"],
            "video/x-msvideo" => [".avi"],
            "video/mpeg" => [".mpeg"],
            "video/ogg" => [".ogv"],
            "video/mp2t" => [".ts"],
            "video/mp4" => [".mp4"],
            "video/webm" => [".webp"],
            "application/x-bzip" => [".bz"],
            "application/x-bzip2" => [".bz2"],
            "application/gzip" => [".gz"],
            "application/vnd.rar" => [".rar"],
            "application/x-tar" => [".tar"],
            "application/zip" => [".zip"],
            "application/x-7z-compressed" => [".7z"],
            "application/pdf" => [".pdf"],
            "application/rtf" => [".rtf"],
            "application/msword" => [".doc"],
            "application/vnd.ms-powerpoint" => [".ppt"],
            "application/vnd.ms-excel" => [".xls"],
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document" => [".docx"],
            "application/vnd.openxmlformats-officedocument.presentationml.presentation" => [".pptx"],
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" => [".xlsx"],
            "application/vnd.oasis.opendocument.presentation" => [".odp"],
            "application/vnd.oasis.opendocument.spreadsheet" => [".ods"],
            "application/vnd.oasis.opendocument.text" => [".odt"],
            "text/restructured" => [".rst"],
            "text/markdown" => [".md", ".markdown"],
            "text/textile" => [".textile"],
            "text/csv" => [".csv"],
            "text/css" => [".css", ".less", ".scss", ".sass"],
            "text/html" => [".html", ".htm"],
            "text/xml" => [".xml"],
            "text/x-php" => [".php", ".phtml", ".php3", ".php4", ".php5", ".php7", ".phps", ".php-s",".pht", ".phar"],
            "application/xhtml+xml" => [".xhtml"],
            "text/javascript" => [".js", ".mjs", ".ts"],
            "text/typescript" => [".ts", ".tsc"],
            "application/json" => [".json"],

            // Special :: Link all other MIME/TYPEs which MAY get recognized as 'text/plain'
            "text/plain" => [
                "text/restructured", "text/markdown", "text/textile", "text/csv", "text/css",
                "text/html", "text/xml", "text/x-php", "application/xhtml+xml", "text/javascript",
                "text/typescript", "application/json",
            ]
        ];

        /*
         |  HELPER :: GET ABSOLUTE PATH
         |  @since  0.1.0
         |
         |  @param  string  The path, which should be formed "absolute".
         |
         |  @return string  The absolute path, or NULL on failure.
         */
        static public function absolute(string $path): ?string {
            $path = str_replace("\\/", DS, urldecode($path));

            // Sanitize Path
            if(strpos($path, PAW_MEDIA_ROOT) !== 0) {
                $path = realpath(PAW_MEDIA_ROOT . DS . trim($path, DS));
            } else {
                $path = realpath($path);
            }

            // Check Path
            if(!$path || strpos($path, PAW_MEDIA_ROOT) !== 0) {
                return null;
            }
            return $path;
        }

        /*
         |  HELPER :: GET RELATIVE PATH
         |  @since  0.1.0
         |
         |  @param  string  The path, which should be formed "relative".
         |
         |  @return string  The relative path, or NULL on failure.
         */
        static public function relative(string $path): ?string {
            if(($path = self::absolute($path)) === null) {
                return null;
            }
            return ltrim(str_replace(rtrim(PAW_MEDIA_ROOT, DS), "", $path) . DS);
        }

        /*
         |  HELPER :: GET RELATIVE PATH IN UNIX / SLUG STYLE
         |  @since  0.1.0
         |
         |  @param  string  The path, which should be formed as "slug".
         |
         |  @return string  The slug-styled relative path, or NULL on failure.
         */
        static public function slug(string $path): ?string {
            if(($path = self::relative($path)) === null) {
                return null;
            }
            return trim(str_replace("\\", "/", $path), "/");
        }

        /*
         |  HELPER :: GET FULL URL
         |  @since  0.1.0
         |
         |  @param  string  The path, which should be formed as "url".
         |
         |  @return string  The full URL to the passed path, or NULL on failure.
         */
        static public function url(string $path): ?string {
            if(($path = self::slug($path)) === null) {
                return null;
            }
            return rtrim(DOMAIN_UPLOADS_PAGES, "/") . "/" . $slug;
        }

        /*
         |  HELPER :: GET PATHINFO
         |  @since  0.2.0
         |
         |  @param  string  The full path to the directory or file.
         |
         |  @return multi   An array with all important path informations, NULL on error.
         */
        static public function pathinfo(string $path): ?array {
            $path = str_replace("\\/", DS, $path);

            // Sanitize Path
            if(strpos($path, PAW_MEDIA_ROOT) !== 0) {
                $path = realpath(PAW_MEDIA_ROOT . DS . trim($path, DS));
            } else {
                $path = realpath($path);
            }

            // Check Path
            if(!$path || strpos($path, PAW_MEDIA_ROOT) !== 0) {
                return null;
            }

            // Prepare
            $relative = ltrim(str_replace(PAW_MEDIA_ROOT, "", $path), DS);
            $slugpath = trim(str_replace("\\", "/", $relative), "/");
            $urlpath = DOMAIN_UPLOADS . str_replace(rtrim(PATH_UPLOADS, DS), "", PAW_MEDIA_ROOT) . $slugpath;

            // Return Path
            return [
                "absolute"  => $path,
                "relative"  => $relative,
                "slug"      => $slugpath,
                "url"       => $urlpath,
                "type"      => is_file($path)? "file": "folder",
                "basename"  => basename($path),
                "dirname"   => is_file($path)? dirname($path): basename($path),
                "extension" => is_file($path)? pathinfo($path, PATHINFO_EXTENSION): null
            ];
        }


        /*
         |  ROOT DIRECTORY
         */
        public $root = null;

        /*
         |  LAST UPLOADED FILE
         */
        public $lastFile = [];

        /*
         |  LAST REVISION FILE NAME
         */
        public $lastRevise = null;


        /*
         |  CONSTRUCTOR
         |  @since  0.1.0
         */
        public function __construct() {
            global $media_plugin;

            $root = str_replace("root", PATH_UPLOADS, $media_plugin->getValue("root_directory"));
            $this->root = rtrim($root, "/\\");
        }

        /*
         |  HELPER :: CHECK FILE TYPE
         |  @since  0.1.0
         |
         |  @param  string  The full path to the file.
         |  @param  string  The file name, if $file is tmp.
         |
         |  @return bool    TRUE if the file is valid and allowed, FALSE if not.
         */
        public function checkFileType(string $file, ?string $name = null): bool {
            global $media_plugin;

            // Get File Data
            $ext = substr($name ?? $file, strrpos($name ?? $file, "."));
            $type = mime_content_type($file);

            // Convert PHP File Type, because it's compilcated
            $phps = ["text/php", "application/php", "application/x-php", "application/x-httpd-php", "application/x-httpd-php-source"];
            if(in_array($type, $phps)) {
                $type = "text/x-php";
            }

            // Explicit Disallowed File Extensions
            // The file types below are mostly recognized with the mime type "text/plain"
            // so it doesn't help to check the mime type itself.
            $disallowed = [];
            if(!$media_plugin->getValue("allow_js_upload")) {
                $disallowed = array_merge($disallowed, self::MIME_TYPES["text/javascript"]);
                $disallowed = array_merge($disallowed, self::MIME_TYPES["text/typescript"]);
            }
            if(!$media_plugin->getValue("allow_php_upload")) {
                $disallowed = array_merge($disallowed, self::MIME_TYPES["text/x-php"]);
            }
            if(!$media_plugin->getValue("allow_html_upload")) {
                $disallowed = array_merge($disallowed, self::MIME_TYPES["application/xhtml+xml"]);
                $disallowed = array_merge($disallowed, self::MIME_TYPES["text/html"]);
                $disallowed = array_merge($disallowed, self::MIME_TYPES["text/xml"]);
            }
            if(in_array($ext, $disallowed)) {
                return false;
            }

            // Check Mime Type
            if($type === "text/plain") {
                $allowed = [".txt"];
                foreach(self::MIME_TYPES["text/plain"] AS $mime) {
                    $allowed = array_merge($allowed, self::MIME_TYPES[$mime]);
                }
            } else if(isset(self::MIME_TYPES[$type])) {
                $allowed = self::MIME_TYPES[$type];
            }

            // Custom Types
            $custom = $media_plugin->getValue("custom_mime_types");
            if(!isset($allowed) && !isset($custom[$type])) {
                return false;
            } else if(isset($custom[$type])) {
                $allowed = array_merge($allowed ?? [], $custom[$type]);
            }

            // Check File Extension
            return in_array(strtolower($ext), $allowed);
        }

        /*
         |  HELPER :: GET REAL MIME TYPE
         |  @since  0.1.0
         |
         |  @param  string  The path to the file.
         |
         |  @return string  The "real" file mime type, or NULL if the $path is invalid.
         */
        public function getMIME(string $file): ?string {
            if(($file = self::absolute($file)) === null) {
                return null;
            }

            $mime = mime_content_type($file);
            if($mime !== "text/plain" && !in_array($mime, self::MIME_TYPES["text/plain"])) {
                return $mime;
            }

            // Get By Extension
            $ext = substr($name ?? $file, strrpos($name ?? $file, "."));
            foreach(self::MIME_TYPES["text/plain"] AS $type) {
                if(in_array($ext, self::MIME_TYPES[$type])) {
                    return $type;
                }
            }
            return $mime;
        }

        /*
         |  HANDLE :: CALCULATE FILE SIZE
         |  @since  0.1.0
         |
         |  @param  int     The respective filesize you want to round up.
         |
         |  @return string  A readable string of the filesize.
         */
        public function calcFileSize(int $size): string {
            $string = "0 B";
            switch(true) {
                case $size >= 1073741824:
                    $string = number_format($size / 1073741824, 2) . " GB"; break;
                case $size >= 1048576:
                    $string = number_format($size / 1048576, 2) . " MB"; break;
                case $size >= 1024:
                    $string = number_format($size / 1024, 2) . " KB"; break;
                case $size >= 1:
                    $string = $size . " B"; break;
            }
            return $string;
        }

        /*
         |  HANDLE :: UPLOAD FILE
         |  @since  0.1.0
         |
         |  @param  string  The path, where the file should be uploaded.
         |  @param  array   The custom file object [name, type, tmp_name, error, size].
         |  @param  bool    TRUE to overwrite existing files, FALSE to do it not.
         |  @param  bool    TRUE keep the old file / make a revision, FALSE to do it not.
         |
         |  @return multi   TRUE if everything went fluffy or the error message as STRING.
         */
        public function upload(string $path, array $file, bool $overwrite = false, bool $revision = false)/*: bool | string*/ {
            if(($path = self::absolute($path)) === null) {
                return bt_("The passed path for the file is invalid.");
            }
            [$name, $type, $tmp, $error, $size] = array_values($file);

            // Reset File Storage
            $this->lastFile = [];
            $this->lastRevise = null;

            // Replace Name
            if(is_file($path)) {
                $file = true;
                $name = basename($path);
                $path = dirname($path);
            } else {
                $file = false;
            }

            // Check File Error
            if($error !== UPLOAD_ERR_OK) {
                switch($error) {
                    case UPLOAD_ERR_INI_SIZE:   ///@pass
                    case UPLOAD_ERR_FORM_SIZE:
                        return bt_a("The requested file ':name' exceeds the maximum size.", [':name' => $name]);
                    case UPLOAD_ERR_PARTIAL:    ///@pass
                    case UPLOAD_ERR_NO_FILE:
                        return bt_a("The requested file ':name' has not (fully) uploaded.", [':name' => $name]);
                    case UPLOAD_ERR_CANT_WRITE: ///@pass
                    case UPLOAD_ERR_EXTENSION:
                        return bt_a("The requested file ':name' could not be uploaded.", [':name' => $name]);
                    case UPLOAD_ERR_NO_TMP_DIR:
                        return bt_a("The requested file ':name' could not be uploaded in the temporary directory.", [':name' => $name]);
                    case UPLOAD_ERR_INI_SIZE:
                        return bt_a("An unknown error occured on the requested file ':name'.", [':name' => $name]);
                }
            }

            // File Revision
            if($file && $revision) {
                [$old, $ext] = [substr($name, 0, strrpos($name, ".")), substr($name, strrpos($name, "."))];

                // Change old File Name
                $temp = $old . "_rev" . $ext;
                $tempn = 1;
                while(file_exists($path . DS . $temp) === true) {
                    $temp = $old . "_rev_" . $tempn++ . $ext;
                }

                // Try to Rename
                if(!@rename($path . DS . $name, $path . DS . $temp)) {
                    return bt_a("The old version of the file ':name' could not be renamed.", [':name' => $name]);
                }
                $this->lastRevise = $path . DS . $temp;
            }

            // File Overwrite
            if(!$file && file_exists($path . DS . $name) && !$overwrite) {
                $temp = $name;
                $tempn = 1;
                while(file_exists($path . DS . $temp) === true) {
                    $temp = substr($name, 0, strrpos($name, ".")) . "_" . $tempn++ . substr($name, strrpos($name, "."));
                }
                $name = $temp;
            }

            // Check File Extension
            if(!$this->checkFileType($tmp, $name)) {
                return bt_a("The requested file ':name' has an unsupported or illegal mime type or file extension.", [':name' => $name]);
            }

            // Move Uploaded File
            if(!@move_uploaded_file($tmp, $path . DS . $name)) {
                return bt_a("The file upload for file ':name' failed.", [':name' => $name]);
            }
            $this->lastFile = [$name, $type, $size, $path . DS . $name];
            return true;
        }

        /*
         |  HANDLE :: CREATE ITEM
         |  @since  0.2.0
         |
         |  @param  string  The absolute path, where the new folder or file should be created.
         |  @param  string  The new name of the item.
         |  @param  string  The item type: 'file' or 'folder'.
         |
         |  @return multi   TRUE if everything went fluffy or the error message as STRING.
         */
        public function create(string $path, string $name, string $type)/*: bool | string */ {
            if(($path = self::absolute($path)) === null) {
                return bt_("The passed path for the file is invalid.");
            }

            // Check Name
            if(strpbrk($name, "\\/?%*:|\"<>") !== false) {
                return bt_a("The passed :type name is invalid.", [':type' => $type]);
            }
            if(file_exists($path . DS . $name)) {
                return bt_a("The passed :type ':name' does already exists.", [':type' => $type, ':name' => $name]);
            }

            // Create
            if($type === "folder") {
                if(@Filesystem::mkdir($path . DS . $name) !== false) {
                    return true;
                }
            } else {
                if(@file_put_contents($path . DS . $name, "") !== false) {
                    return true;
                }
            }
            return bt_a("The passed :type ':name' could not be created.", [':type' => $type, ':name' => $name]);
        }

        /*
         |  HANDLE :: MOVE ITEM
         |  @since  0.2.0
         |
         |  @param  string  The absolute path, of the folder or file which should be moved.
         |  @param  string  The absolute new path, where the folder or file should be moved to.
         |  @param  string  The (optional) new name of the file or folder,
         |                  use null to use dir basename() of $oldpath.
         |
         |  @return multi   TRUE if everything went fluffy or the error message as STRING.
         */
        public function move(string $oldpath, string $newpath, ?string $newname = null) {
            $type = is_dir($oldpath)? bt_("folder"): bt_("file");
            $newname = empty($newname)? basename($oldpath): $newname;

            // Check New Name
            if(strpbrk($newname, "\\/?%*:|\"<>") !== false) {
                return bt_a("The passed :type name is invalid.", [':type' => $type]);
            }
            if(file_exists($newpath . DS . $newname)) {
                // The WINDOWS File System works completely case-insensitive so if the user just
                // wants to change the letter cases, we need to check the realpath again!
                if(dirname($oldpath) === $newpath) {
                    if(strcmp(realpath($newpath . DS . $newname), $newpath . DS . $newname) === 0) {
                        return bt_a("The passed :type ':name' does already exists.", [':type' => $type, ':name' => $newname]);
                    }
                } else {
                    return bt_a("The passed :type ':name' does already exists.", [':type' => $type, ':name' => $newname]);
                }
            }

            // Move
            if(@Filesystem::mv($oldpath, $newpath . DS . $newname) !== false) {
                return true;
            }
            return bt_a("The :type could not be moved.", [":type" => $type]);
        }

        /*
         |  HANDLE :: DELETE ITEM
         |  @since  0.2.0
         |
         |  @param  string  The path INCLUDING the directory, which should be deleted.
         |  @param  bool    Delete the directory recursive.
         |
         |  @return multi   TRUE if everything went fluffy or the error message as STRING.
         */
        public function delete(string $path, bool $recursive = false)/*: bool | string */ {
            if(($path = self::absolute($path)) === null) {
                return bt_("The passed path for the folder or file is invalid.");
            }
            $base = trim(self::slug($path), "/");

            // Remove Directory
            if(is_dir($path)) {
                if(count(scandir($path)) > 2) {
                    if(!$recursive) {
                        return bt_a("The passed folder ':name' is not empty.", [':name' => $base]);
                    }
                    if(!@Filesystem::deleteRecursive($path, true)) {
                        return bt_a("The passed folder ':name' could not be emptied.", [':name' => $base]);
                    }
                } else if(!@Filesystem::rmdir($path)) {
                    return bt_a("The passed folder ':name' could not be deleted.", [':name' => $base]);
                }
                return true;
            }

            // Remove File
            if(!@Filesystem::rmfile($path)) {
                return bt_a("The passed file ':name' could not be deleted.", [':name' => $base]);
            }
            return true;
        }

        /*
         |  HANDLE :: LIST CONTENT
         |  @since  0.1.0
         |
         |  @param  string  The path to the directory to show, null to use the root dir.
         |  @param  int     The limit of returning items, use 0 to return everything.
         |  @param  int     The current page number, starting with 0.
         |
         |  @return multi   The files and folders within the directory, null on failure.
         */
        public function list(?string $path = null, int $limit = 0, int $page = 0): ?array {
            global $pages;
            global $media_plugin;

            // Validate Path
            if(($path = self::absolute(empty($path)? DS: $path)) === null) {
                return null;
            }

            // List Data
            $files = [];
            $folders = [];
            if($handle = opendir($path)) {
                while(($file = readdir($handle)) !== false) {
                    if($file === "." || $file === "..") {
                        continue;
                    }
                    if(($real = realpath($path . DS . $file)) === false) {
                        if(is_link($path . DS . $file)) {
                            @unlink($path . DS . $file);
                        }
                        continue;
                    }

                    // Is File
                    if(is_file($real)) {
                        if(!is_link($path . DS . $file)) {
                            $files[$real] = basename($real);
                        }
                        continue;
                    }

                    // Is Directory
                    if($media_plugin->getValue("resolve_folders") !== "symlink") {
                        if($media_plugin->getValue("resolve_folders") === "page_title") {
                            if(($page = $pages->getByUUID(basename($real))) !== false) {
                                $page = $pages->getPageDB($page)["title"];
                            }
                        } else {
                            $page = $pages->getByUUID(basename($real));
                        }

                        if(!empty($page)) {
                            $folders[$real] = $page;
                        } else {
                            $folders[$real] = basename($real);
                        }
                    } else {
                        if(is_link($path . DS . $file)) {
                            $folders[$real] = $file;
                        } else if(!array_key_exists($real, $folders)) {
                            $folders[$real] = basename($real);
                        }
                    }
                }
            }
            closedir($handle);

            // Sort
            if($media_plugin->getValue("items_order") == "desc") {
                krsort($folders);
                krsort($files);
            } else {
                ksort($folders);
                ksort($files);
            }
            $list = array_merge($folders, $files);

            // Split Result to Pages
            //if($limit > 0 && $page >= 0) {
            //    $chunks = array_chunk($list, $limit, true);
            //    $list = count($chunks) > $page? $chunks[$page]: [ ];
            //}
            return $list;
        }
    }
