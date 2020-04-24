<?php
declare(strict_types=1);
/*
 |  Media       An advanced Media & File Manager for Bludit
 |  @file       ./system/manager.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.0 [0.1.0] - Alpha
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
    if(!defined("BLUDIT")) { die("Go directly to Jail. Do not pass Go. Do not collect 200 Cookies!"); }

    class MediaManager {
        const MIME_TYPES = [
            //
            //  "MIME/TYPE" => [".allowed", ".extensions"]
            //
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

            // Special :: Link all other MIME/TYPEs which MAY get recognized as 'text/plain'
            "text/plain" => [
                "text/restructured", "text/markdown", "text/textile", "text/csv", "text/css",
                "text/html", "text/xml", "text/x-php", "application/xhtml+xml", "text/javascript",
                "text/typescript", "application/json",
            ],
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
            "application/json" => [".json"]
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
            $path = str_replace("\\/", DS, $path);

            // Sanitize Path
            if(strpos($path, rtrim(PATH_UPLOADS_PAGES, DS)) !== 0) {
                $path = realpath(rtrim(PATH_UPLOADS_PAGES, DS) . DS . trim($path, DS));
            } else {
                $path = realpath($path);
            }

            // Check Path
            if(!$path || strpos($path, rtrim(PATH_UPLOADS_PAGES, DS)) !== 0) {
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
            return str_replace(rtrim(PATH_UPLOADS_PAGES, DS), "", $path);
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
            return str_replace("\\", "/", $path);
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
         |  ROOT DIRECTORY
         */
        public $root = PATH_UPLOADS_PAGES;

        /*
         |  LAST UPLOADED FILE
         */
        public $lastFile = [];


        /*
         |  CONSTRUCTOR
         |  @since  0.1.0
         */
        public function __construct() {
            $this->root = rtrim(PATH_UPLOADS_PAGES, "/\\");
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
                return paw__("The passed path for the file is invalid.");
            }
            [$name, $type, $tmp, $error, $size] = array_values($file);

            // Check File Error
            if($error !== UPLOAD_ERR_OK) {
                switch($error) {
                    case UPLOAD_ERR_INI_SIZE: ///@pass
                    case UPLOAD_ERR_FORM_SIZE:
                        return paw__("The requested file '%s' exceeds the maximum size.", [$name]);
                    case UPLOAD_ERR_PARTIAL: ///@pass
                    case UPLOAD_ERR_NO_FILE:
                        return paw__("The requested file '%s' has not (fully) uploaded.", [$name]);
                    case UPLOAD_ERR_CANT_WRITE: ///@pass
                    case UPLOAD_ERR_EXTENSION:
                        return paw__("The requested file '%s' could not be uploaded.", [$name]);
                    case UPLOAD_ERR_NO_TMP_DIR:
                        return paw__("The requested file '%s' could not be uploaded in the temporary directory.", [$name]);
                    case UPLOAD_ERR_INI_SIZE:
                        return paw__("An unknown error occured on the requested file '%s'.", [$name]);
                }
            }

            // Don't Overwrite File
            if(!$overwrite) {
                $temp = $name;
                $tempn = 1;
                while(file_exists($path . DS . $temp) === true) {
                    $temp = substr($name, 0, strrpos($name, ".")) . "_" . $tempn++ . substr($name, strrpos($name, "."));
                }
                $name = $temp;
            } else if($revision && file_exists($path . DS . $name) === true) {
                [$old, $ext] = [substr($name, 0, strrpos($name, ".")), substr($name, strrpos($name, "."))];

                // Revision Name
                $temp = $old . "_rev" . $ext;
                $tempn = 1;
                while(file_exists($path . DS . $temp) === true) {
                    $temp = $old . "_rev_" . $tempn++ . $ext;
                }

                // Try to Rename
                if(!@rename($path . DS . $name, $path . DS . $temp)) {
                    return paw__("The old version of the file '%s' could not be renamed.", [$name]);
                }
            }

            // Check File Extension
            if(!$this->checkFileType($tmp, $name)) {
                return paw__("The requested file '%s' has an unsupported or illegal mime type or file extension.", [$name]);
            }

            // Move Uploaded File
            if(!@move_uploaded_file($tmp, $path . DS . $name)) {
                return paw__("The file upload for file '%s' failed.", [$file]);
            }
            $this->lastFile = [$name, $type, $size, $path . DS . $name];
            return true;
        }

        /*
         |  HANDLE :: CREATE DIRECTORY
         |  @since  0.1.0
         |
         |  @param  string  The path, where the new directory should be created.
         |  @param  string  The new directory folder name.
         |
         |  @return multi   TRUE if everything went fluffy or the error message as STRING.
         */
        public function createDir(string $path, string $directory)/*: bool | string */ {
            if(($path = self::absolute($path)) === null) {
                return paw__("The passed path for the new directory is invalid.");
            }

            // Check Directory
            if(strpbrk($directory, "\\/?%*:|\"<>") !== false) {
                return paw__("The passed directory name is invalid.");
            }
            if(file_exists($path . DS . $directory)) {
                return paw__("The passed directory '%s' does already exists.", [$directory]);
            }
            if(@mkdir($path . DS . $directory) === false) {
                return paw__("The passed directory '%s' could not be created.", [$directory]);
            }
            return true;
        }

        /*
         |  HANDLE :: CREATE FILE
         |  @since  0.1.0
         |
         |  @param  string  The path, where the new file should be created.
         |  @param  string  The new file name.
         |  @param  string  The content of the new file.
         |
         |  @return multi   TRUE if everything went fluffy or the error message as STRING.
         */
        public function createFile(string $path, string $filename, string $content = "")/*: bool | string */ {
            if(($path = self::absolute($path)) === null) {
                return paw__("The passed path for the new file is invalid.");
            }

            // Check File
            if(strpbrk($filename, "\\/?%*:|\"<>") !== false) {
                return paw("The passed filename is invalid.");
            }
            if(file_exists($path . DS . $filename)) {
                return paw("The passed filename '%s' does already exists.", [$filename]);
            }
            if(@file_put_contents($path . DS . $filename, $content) === false) {
                return paw("The passed filename '%s' could not be created.", [$filename]);
            }
            return true;
        }

        /*
         |  HANDLE :: MOVE OR RENAME DIRECTORY
         |  @since  0.1.0
         |
         |  @param  string  The old path INCLUDING the directory you want to move.
         |  @param  string  The new path INCLDUING the directory (new or old) name.
         |
         |  @return multi   TRUE if everything went fluffy or the error message as STRING.
         */
        public function moveDir(string $old, string $new)/*: bool | string */ {
            ///@todo Move Function from admin.php
        }

        /*
         |  HANDLE :: MOVE OR RENAME FILE
         |  @since  0.1.0
         |
         |  @param  string  The old path INCLUDING the file you want to move.
         |  @param  string  The new path INCLDUING the file (new or old) name.
         |
         |  @return multi   TRUE if everything went fluffy or the error message as STRING.
         */
        public function moveFile(string $old, string $new)/*: bool | string */ {
            ///@todo Move Function from admin.php
        }

        /*
         |  HANDLE :: DELETE DIRECTORY
         |  @since  0.1.0
         |
         |  @param  string  The path INCLUDING the directory, which should be deleted.
         |  @param  bool    Delete the directory recursive.
         |
         |  @return multi   TRUE if everything went fluffy or the error message as STRING.
         */
        public function deleteDir(string $path, bool $recursive = false)/*: bool | string */ {
            if(($path = self::absolute($path)) === null) {
                return paw__("The passed path for the new file is invalid.");
            }
            $base = trim(self::slug($path), "/");

            // Recursive Delete
            $delete = function($path, $func){
                $handle = opendir($path);
                while(($item = readdir($handle)) !== false) {
                    if($item === "." || $item === "..") {
                        continue;
                    }
                    if(is_dir($path . DS . $item)) {
                        if($func($path . DS . $item, $func) !== true || !@rmdir($path . DS . $item)) {
                            return false;
                        }
                    } else if(@unlink($path . DS . $item)) {
                        return false;
                    }
                }
                closedir($handle);
                return true;
            };

            // Check if Empty
            if(count(scandir($path)) > 2) {
                if(!$recursive) {
                    return paw__("The passed folder '%s' is not empty.", [$base]);
                }
                if($delete($path, $delete)) {
                    return paw__("The passed folder '%s' could not be emptied.", [$base]);
                }
            }

            // Delete
            if(@rmdir($path) !== true) {
                return paw__("The passed folder '%s' could not be deleted.", [$base]);
            }
            return true;
        }

        /*
         |  HANDLE :: DELETE FILE
         |  @since  0.1.0
         |
         |  @param  string  The path INCLUDING the file, which should be deleted.
         |
         |  @return multi   TRUE if everything went fluffy or the error message as STRING.
         */
        public function deleteFile(string $path)/*: bool | string */ {
            if(($path = self::absolute($path)) === null) {
                return paw__("The passed file path is invalid.");
            }
            $base = self::relative($path);

            // Delete
            if(@unlink($path) !== true) {
                return paw__("The passed file '%s' could not be deleted.", [$base]);
            }
            return true;
        }

        /*
         |  HANDLE :: LIST CONTENT
         |  @since  0.1.0
         |
         |  @param  string  The path to the directory, which should be listed.
         |
         |  @return multi   The files and folders within the directory, null on failure.
         */
        public function list(?string $path = null): ?array {
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
                    $real = realpath($path . DS . $file);

                    // Append
                    if(is_dir($real)) {
                        $append = &$folders;
                    } else {
                        $append = &$files;
                    }

                    // Check for Links
                    if(is_link($path . DS . $file)) {
                        $append[$real] = $path . DS . $file;
                    } else if(!array_key_exists($real, $append)) {
                        $append[$real] = $real;
                    }
                }
            }
            closedir($handle);

            // Sort & Return
            if($media_plugin->getValue("items_order") == "desc") {
                krsort($folders);
                krsort($files);
            } else {
                ksort($folders);
                ksort($files);
            }
            return array_merge($folders, $files);
        }
    }
