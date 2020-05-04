<?php
declare(strict_types=1);
/*
 |  Media       An advanced Media & File Manager for Bludit
 |  @file       ./plugin.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.1 [0.1.0] - Alpha
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
    if(!defined("BLUDIT")) { die("Go directly to Jail. Do not pass Go. Do not collect 200 Cookies!"); }

    // Load Helper Functions
    require_once "system" . DS . "functions.php";


    // Load Plus Package
    if(file_exists(dirname(__FILE__) . DS . "plugin-plus.php")) {
        require_once "plugin-plus.php";
    } else if(!defined("PAW_MEDIA_PLUS")) {
        define("PAW_MEDIA_PLUS", false);
    }


    // Main Plugin Class
    class MediaPlugin extends Plugin {
        const VERSION = "0.1.1";
        const STATUS = "Alpha";

        /*
         |  CONSTRUCTOR
         |  @since  0.1.0
         */
        public function __construct() {
            global $media_plugin;           // The Media Plugin instance
            global $media_admin;            // The Media Administration instance
            global $media_manager;          // The Media Manager instance

            // Attach the Plugin instance
            $media_plugin = $this;

            // Call the Parent Constructor
            parent::__construct();
        }

        /*
         |  PLUGIN :: INIT
         |  @since  0.1.0
         */
        public function init(): bool {
            $this->dbFields = [
                "layout"            => "table",     // Media Manager Layout ("table" or "grid")
                "items_order"       => "ASC",       // *Items Order (ASC || DESC)
                "items_per_page"    => 0,           // *Items per Page (0 = all)
                "allow_html_upload" => true,        // Allow HTML upload
                "allow_php_upload"  => false,       // Allow PHP upload
                "allow_js_upload"   => false,       // Allow JavaScript upload
                "allow_root_upload" => false,       // Allow File-Upload on Root
                "enable_ajax_page"  => false,       // Enable AJAX on Media Admin Page
                "custom_mime_types" => [],          // Custom MIME Types with file extensions
            ];
            return true;
        }

        /*
         |  PLUGIN :: INIT IF INSTALLED
         |  @since  0.1.0
         */
        public function installed(): bool {
            global $media_admin;
            global $media_manager;

            // Handle if installed
            if(file_exists($this->filenameDb)) {
                if(!defined("PAW_MEDIA")) {
                    define("PAW_MEDIA", basename(__DIR__));
                    define("PAW_MEDIA_PATH", PATH_PLUGINS . PAW_MEDIA . DS);
                    define("PAW_MEDIA_DOMAIN", DOMAIN_PLUGINS . PAW_MEDIA . "/");
                    define("PAW_MEDIA_VERSION", self::VERSION . "-" . strtolower(self::STATUS));
                }

                // Init MediaAdmin
                if(!class_exists("MediaAdmin")) {
                    require_once "system" . DS . "admin.php";

                    if(PAW_MEDIA_PLUS) {
                        require_once "system" . DS . "admin-plus.php";
                        $media_admin = new MediaAdminPlus();
                    } else {
                        $media_admin = new MediaAdmin();
                    }
                }

                // Init MediaManager
                if(!class_exists("MediaManager")) {
                    require_once "system" . DS . "manager.php";

                    if(PAW_MEDIA_PLUS) {
                        require_once "system" . DS . "manager-plus.php";
                        $media_manager = new MediaManagerPlus();
                    } else {
                        $media_manager = new MediaManager();
                    }
                }
            }
            return file_exists($this->filenameDb);
        }

        /*
         |  PLUGIN :: REMOVE PLUGIN
         |  @since  0.1.0
         */
        public function uninstall() {
            global $users;

            // Remove User Data
            foreach($users->db AS $username => $data) {
                unset($users->db[$username]["media_order"]);
                unset($users->db[$username]["media_items_per_page"]);
                unset($users->db[$username]["media_layout"]);
                unset($users->db[$username]["media_favorites"]);
            }
            $users->save();

            // Uninstall Plugin
            return parent::uninstall();
        }

        /*
         |  HOOK :: PLUGIN CONFIG FORM
         |  @since  0.1.0
         */
        public function form(): void {
            include "admin" . DS . "form.php";
        }

        /*
         |  HOOK :: PLUGIN CONFIG FORM SUBMIT
         |  @since  0.1.0
         */
        public function post(): bool {
            $data = $_POST;
            foreach($this->dbFields AS $field => $value) {
                if(!isset($data[$field])) {
                    continue;
                }

                // Validate Settings
                if($field === "layout") {
                    $this->db[$field] = Sanitize::html($data[$field]);
                } else if($field === "items_order" && in_array($data[$field], ["asc", "desc"])) {
                    $this->db[$field] = $data[$field];
                } else if($field === "items_per_page" && is_numeric($data[$field])) {
                    $this->db[$field] = (int) $data[$field];
                } else if(strpos($field, "allow_") === 0 || strpos($field, "enable_") === 0) {
                    $this->db[$field] = $data[$field] === "true"? true: false;
                } else if($field === "custom_mime_types") {
                    if(empty($data["custom_mime_types"])) {
                        $this->db[$field] = [];
                        continue;
                    }

                    // Walk through MIME types
                    $lines = explode("\n", $data["custom_mime_types"]);
                    $output = [];
                    foreach($lines AS $line) {
                        [$mime, $ext] = array_pad(explode("#", $line), 2, null);
                        if(strpos($mime, "/") === false || strpos($ext, ".") === false) {
                            continue;
                        }
                        $output[$mime] = array_map("trim", explode(",", $ext));
                    }
                    $this->db[$field] = $output;
                }
            }
            return $this->save();
        }

        /*
         |  HOOK :: BEFORE ADMIN LOAD
         |  @since  0.1.0
         */
        public function beforeAdminLoad(): void {
            global $url;
            global $media_admin;

            // Trigger on 'Media' View
            if(strpos($url->slug(), "media") !== 0) {
                return;
            }
            checkRole(array("admin"));

            // Change Layout
            if(isset($_GET["layout"]) && in_array($_GET["layout"], ["table", "grid"])) {
                $this->setField("layout", $_GET["layout"]);
            }

            // Init Administration
            $media_admin->path = trim($url->slug(), "/");
            $media_admin->view = "media";
            $media_admin->method = explode("/", $media_admin->path)[1] ?? "index";
            $media_admin->query = $_GET;

            // Handle Request
            if($media_admin->method !== "index") {
                $media_admin->submit();
            }
        }

        /*
         |  HOOK :: ADMIN HEADER
         |  @since  0.1.0
         */
        public function adminHead(): string {
            global $url;

            // Set Data
            $admin = strpos($url->slug(), "media") === 0;
            $enable = (!$admin || ($admin && $this->getValue("enable_ajax_page")));

            // Return Scripts and Stylesheets
            ob_start();
            ?>
                <script type="text/javascript" src="<?php echo PAW_MEDIA_DOMAIN; ?>admin/js/custom-file.min.js"></script>
                <script type="text/javascript" src="<?php echo PAW_MEDIA_DOMAIN; ?>admin/js/dropzone.min.js"></script>
                <script type="text/javascript">
                    ;(function() {
                        window.media = {
                            admin: <?php echo ($admin)? "true": "false"; ?>,
                            enable: <?php echo ($enable)? "true": "false"; ?>,
                            strings: {
                                "js-error-title":   '<?php paw_e("JavaScript Error"); ?>',
                                "js-error-text":    '<?php paw_e("An JavaScript error occured, please reload the page and try again."); ?>',
                                "js-form-create":   '<?php paw_e("Create Form"); ?>',
                                "js-form-search":   '<?php paw_e("Search Form"); ?>',
                                "js-form-move":     '<?php paw_e("Move Form"); ?>',
                                "js-form-upload":   '<?php paw_e("Upload Form"); ?>',
                                "js-link-delete":   '<?php paw_e("Delete"); ?>',
                                "js-link-favorite": '<?php paw_e("Favorite"); ?>',
                                "js-media-title":   '<?php paw_e("Media"); ?>',
                                "js-unknown":       '<?php paw_e("An unknown error is occured."); ?>'
                            }
                        };
                    }(window));
                </script>
                <script type="text/javascript" src="<?php echo PAW_MEDIA_DOMAIN; ?>admin/js/media.js"></script>

                <link type="text/css" rel="stylesheet" href="<?php echo PAW_MEDIA_DOMAIN; ?>admin/css/media.css"></link>
            <?php
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }

        /*
         |  HOOK :: ADMIN SIDEBAR
         |  @since  0.1.0
         */
        public function adminSidebar(): string {
            global $media_admin;

            if(PAW_MEDIA_PLUS) {
                return '<a href="' . $media_admin->buildURL("media") . '" class="nav-link">Media+ Manager</a>';
            }
            return '<a href="' . $media_admin->buildURL("media") . '" class="nav-link">Media Manager</a>';
        }

        /*
         |  HOOK :: BEFORE ADMIN CONTENT
         |  @info   Fetch the HTML content, to inject the media admin page.
         |  @since  0.1.0
         */
        public function adminBodyBegin(): void {
            global $media_admin;

            // Check if Administration Page is shown
            if($media_admin->view !== "media" || $media_admin->method !== "index") {
                return;
            }

            // Catch Page not Found Message
            ob_start();
        }

        /*
         |  HOOK :: AFTER ADMIN CONTENT
         |  @info   Handle the HTML content, to inject the media admin page.
         |  @since  0.1.0
         */
        public function adminBodyEnd(): void {
            global $url;
            global $security;
            global $media_admin;
            global $media_manager;

            // Check if `new-content` or `edit-content` page is shown
            $page = explode("/", $url->slug())[0];
            if($page === "new-content" || $page === "edit-content") {
                $this->adminContentArea(); return;
            } else if($media_admin->view !== "media" || $media_admin->method !== "index") {
                return;
            }

            // Get Content
            $content = ob_get_contents();
            ob_end_clean();

            // Prepare Query
            if(($absolute = MediaManager::absolute($_GET["path"] ?? DS)) === null) {
                $absolute = PATH_UPLOADS_PAGES;
                $relative = DS;
            } else {
                $relative = MediaManager::relative($absolute);
            }
            if(isset($_GET["file"]) && ($file = MediaManager::absolute($absolute . DS . $_GET["file"])) === null) {
                unset($file);
            }

            // Load Admin Page
            ob_start();
            if(isset($file)) {
                include "admin" . DS . "file.php";
            } else {
                include "admin" . DS . "list.php";
            }

            // Load Modals
        	require "admin" . DS . "modal-folder.php";
            if(PAW_MEDIA_PLUS) {
                require "admin" . DS . "modal-search.php";
            }

            // Get Content
            $add = ob_get_contents();
            ob_end_clean();

            // Inject Admin Page
            $regexp = "/(\<div class=\"col-lg-10 pt-3 pb-1 h-100\"\>)(.*?)(\<\/div\>)/s";
            $content = preg_replace($regexp, "$1{$add}$3", $content);
            print($content);
        }

        /*
         |  HOOK :: ADMIN CONTENT AREA
         |  @since  0.1.0
         */
        private function adminContentArea(): void {
            global $security;
            global $media_admin;
            global $media_manager;

            // Prepare Query
            if(($absolute = MediaManager::absolute($_GET["path"] ?? DS)) === null) {
                $absolute = PATH_UPLOADS_PAGES;
                $relative = DS;
            } else {
                $relative = MediaManager::relative($absolute);
            }

            // Load Main Content Modal
            include "admin" . DS . "modal.php";

            // Load Modals
        	require "admin" . DS . "modal-folder.php";
            if(PAW_MEDIA_PLUS) {
                require "admin" . DS . "modal-search.php";
            }
        }
    }
