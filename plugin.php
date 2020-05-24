<?php
declare(strict_types=1);
/*
 |  Media       The advanced Media & File Manager for Bludit
 |  @file       ./plugin.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.2.0 [0.1.0] - Beta
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
    defined("BLUDIT") or die("Go directly to Jail. Do not pass Go. Do not collect 200 Cookies!");

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
        const VERSION = "0.2.0";
        const STATUS = "Beta";

        /*
         |  CORE ERRORs
         */
        protected $errors = [ ];

        /*
         |  CONSTRUCTOR
         |  @since  0.1.0
         */
        public function __construct() {
            global $media_plugin;           // The Media Plugin instance
            global $media_admin;            // The Media Administration instance
            global $media_manager;          // The Media Manager instance
            global $media_history;          // The Media History instance

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
                "version"           => self::VERSION,   // Installed version
                "layout"            => "table",         // Media Manager Layout ("table" or "grid")
                "items_order"       => "ASC",           // *Items Order (ASC || DESC)
                "items_per_page"    => 0,               // *Items per Page (0 = all)
                "allow_html_upload" => true,            // Allow HTML upload
                "allow_php_upload"  => false,           // Allow PHP upload
                "allow_js_upload"   => false,           // Allow JavaScript upload
                "allow_root_upload" => false,           // Allow File-Upload on Root
                "enable_ajax_page"  => false,           // Enable AJAX on Media Admin Page
                "custom_mime_types" => [ ],             // Custom MIME Types with file extensions
                "root_directory"    => "root",          // Default root directory
                "resolve_folders"   => "symlink",       // Resolve temporary page folders
            ];
            return true;
        }

        /*
         |  PLUGIN :: INIT IF INSTALLED
         |  @since  0.1.0
         |  @info   The installed method is called twice during the bludit boot. The first call
         |          initializes the plugin, and the second one prepares the additional classes.
         */
        public function installed(): bool {
            global $media_admin;
            global $media_manager;
            global $media_history;

            // Plugin is not installed
            if(!file_exists($this->filenameDb)) {
                return false;
            }

            // Init Plugin
            if(!defined("PAW_MEDIA")) {
                define("PAW_MEDIA", basename(__DIR__));
                define("PAW_MEDIA_PATH", PATH_PLUGINS . PAW_MEDIA . DS);
                define("PAW_MEDIA_DOMAIN", DOMAIN_PLUGINS . PAW_MEDIA . "/");
                define("PAW_MEDIA_VERSION", self::VERSION . "-" . strtolower(self::STATUS));
                define("PAW_MEDIA_UPLOADS", PATH_UPLOADS . "media" . DS);
                define("PAW_MEDIA_WORKSPACE", $this->workspace());

                // Check Uploads Directory
                if(!file_exists(PAW_MEDIA_UPLOADS)) {
                    if(@mkdir(PAW_MEDIA_UPLOADS) === false) {
                        $this->errors = bt_a("The Media Upload directory could not be created, please create the path :path manually.", [":path" => PAW_MEDIA_UPLOADS]);
                    }
                }
                return true;
            }

            // Prepare Plugin
            if(defined("PAW_MEDIA") && empty($media_admin)) {
                if(!class_exists("MediaAdmin")) {
                    require_once "system" . DS . "admin.php";

                    if(PAW_MEDIA_PLUS) {
                        require_once "system" . DS . "admin-plus.php";
                        $media_admin = new MediaAdminPlus();
                    } else {
                        $media_admin = new MediaAdmin();
                    }
                }

                if(!class_exists("MediaManager")) {
                    require_once "system" . DS . "manager.php";

                    if(PAW_MEDIA_PLUS) {
                        require_once "system" . DS . "manager-plus.php";
                        $media_manager = new MediaManagerPlus();
                    } else {
                        $media_manager = new MediaManager();
                    }
                }

                if(!class_exists("MediaHistory")) {
                    require_once "system" . DS . "history.php";
                    $media_history = new MediaHistory();
                }

                define("PAW_MEDIA_ROOT", $media_manager->root);
            }

            // Add Plugin+ Metadata
            if(PAW_MEDIA_PLUS && array_key_exists("name", $this->metadata)) {
                $this->metadata["name"] .= "+";
                $this->metadata["description"] .= ' <b style="font-weight:600;">' . bt_("Thanks for your Support!") . '</b>';
            }

            // Do Upgrade & Return
            if(version_compare($this->db["version"] ?? "0.1.1", self::VERSION, "<")) {
                $this->upgrade($this->db["version"] ?? "0.1.1");
            }
            return true;
        }

        /*
         |  PLUGIN :: UPGRADE VERSION
         |  @since  0.2.0
         |
         |  @param  string  The current installed version.
         |
         |  @return bool    TRUE if the upgrade was successfullfy, FALSE if not.
         */
        public function upgrade(string $version): bool {

            // Upgrade 0.1.x to 0.2.x
            if(version_compare($version, "0.2.0", "<")) {
                $this->db = array_merge($this->dbFields, $this->db);
                return $this->save();
            }
            return true;
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

            // Validation Array
            $validate = [
                "layout"            => ["table", "grid"],
                "items_order"       => ["asc", "desc"],
                "root_directory"    => ["root", "root/pages", "root/media"],
                "resolve_folders"   => ["symlink", "page_title", "page_slug"],
            ];

            // Loop Configuration
            foreach($this->dbFields AS $field => $value) {
                if(is_bool($value)) {
                    $this->db[$field] = ($data[$field] ?? ($value? "true": "false")) === "true";
                    continue;
                }
                if(is_int($value) && is_numeric($data[$field] ?? "!")) {
                    $this->db[$field] = intval($data[$field]);
                    continue;
                }
                if(array_key_exists($field, $validate) && in_array(strtolower($data[$field] ?? ""), $validate[$field])) {
                    $this->db[$field] = $data[$field];
                    continue;
                }

                // Validate Custom Mime Types
                if($field === "custom_mime_types" && isset($data[$field])) {
                    if(empty($data[$field])) {
                        $this->db[$field] = [];
                        continue;
                    }

                    // Walk through MIME types
                    $lines = explode("\n", $data[$field]);
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

            // Check Administration
            if($media_admin->ajax && (strpos($_SERVER["HTTP_REFERER"], "new-content") !== false || strpos($_SERVER["HTTP_REFERER"], "edit-content") !== false)) {
                $view = "modal";
            } else {
                $view = "media";
            }

            // Init Administration
            $media_admin->path = trim($url->slug(), "/");
            $media_admin->view = $view;
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
            if(strpos($url->slug(), "media") === 0) {
                $admin = $this->getValue("enable_ajax_page");
            } else {
                $admin = true;
            }

            // Return Scripts and Stylesheets
            ob_start();
            ?>
                <script type="text/javascript" src="<?php echo PAW_MEDIA_DOMAIN; ?>admin/js/custom-file.min.js?ver=1.3.4"></script>
                <script type="text/javascript" src="<?php echo PAW_MEDIA_DOMAIN; ?>admin/js/dropzone.min.js?ver=5.7.0"></script>
                <script type="text/javascript">
                    ;(function() {
                        window.media = {
                            admin: <?php echo ($admin)? "true": "false"; ?>,
                            strings: {
                                "js-error-title":   '<?php bt_e("JavaScript Error"); ?>',
                                "js-error-text":    '<?php bt_e("An JavaScript error occured, please reload the page and try again."); ?>',
                                "js-form-create":   '<?php bt_e("Create Form"); ?>',
                                "js-form-search":   '<?php bt_e("Search Form"); ?>',
                                "js-form-move":     '<?php bt_e("Move Form"); ?>',
                                "js-form-upload":   '<?php bt_e("Upload Form"); ?>',
                                "js-link-delete":   '<?php bt_e("Delete"); ?>',
                                "js-link-favorite": '<?php bt_e("Favorite"); ?>',
                                "js-media-title":   '<?php bt_e("Media"); ?>',
                                "js-unknown":       '<?php bt_e("An unknown error is occured."); ?>'
                            }
                        };
                    }(window));
                </script>
                <script type="text/javascript" src="<?php echo PAW_MEDIA_DOMAIN; ?>admin/js/media.js?ver=<?php echo self::VERSION; ?>"></script>
                <link type="text/css" rel="stylesheet" href="<?php echo PAW_MEDIA_DOMAIN; ?>admin/css/media.min.css?ver=<?php echo self::VERSION; ?>"></link>

                <?php if(PAW_MEDIA_PLUS) { ?>
                    <script type="text/javascript" src="<?php echo PAW_MEDIA_DOMAIN; ?>admin/codemirror/codemirror.min.js?ver=5.53.2"></script>
                    <script type="text/javascript" src="<?php echo PAW_MEDIA_DOMAIN; ?>admin/codemirror/codemirror-javascript.min.js?ver=5.53.2"></script>
                    <script type="text/javascript" src="<?php echo PAW_MEDIA_DOMAIN; ?>admin/codemirror/codemirror-yaml.min.js?ver=5.53.2"></script>
                    <script type="text/javascript" src="<?php echo PAW_MEDIA_DOMAIN; ?>admin/codemirror/codemirror-xml.min.js?ver=5.53.2"></script>
                    <script type="text/javascript" src="<?php echo PAW_MEDIA_DOMAIN; ?>admin/codemirror/codemirror-htmlmixed.min.js?ver=5.53.2"></script>
                    <script type="text/javascript" src="<?php echo PAW_MEDIA_DOMAIN; ?>admin/codemirror/codemirror-css.min.js?ver=5.53.2"></script>
                    <script type="text/javascript" src="<?php echo PAW_MEDIA_DOMAIN; ?>admin/codemirror/codemirror-sass.min.js?ver=5.53.2"></script>
                    <script type="text/javascript" src="<?php echo PAW_MEDIA_DOMAIN; ?>admin/codemirror/codemirror-php.min.js?ver=5.53.2"></script>
                    <script type="text/javascript" src="<?php echo PAW_MEDIA_DOMAIN; ?>admin/codemirror/codemirror-markdown.min.js?ver=5.53.2"></script>
                    <script type="text/javascript" src="<?php echo PAW_MEDIA_DOMAIN; ?>admin/codemirror/codemirror-textile.min.js?ver=5.53.2"></script>
                    <link type="text/css" rel="stylesheet" href="<?php echo PAW_MEDIA_DOMAIN; ?>admin/codemirror/codemirror.min.css?ver=5.53.2"></link>
                    <link type="text/css" rel="stylesheet" href="<?php echo PAW_MEDIA_DOMAIN; ?>admin/codemirror/codemirror-neo.min.css?ver=5.53.2"></link>
                <?php } ?>
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

            // Set Pathdata
            $pathdata = MediaManager::pathinfo($_GET["path"] ?? "");
            extract(empty($pathdata)? MediaManager::pathinfo(""): $pathdata);

            // Load Admin Page
            ob_start();
            if(is_file($absolute)) {
                require "admin" . DS . "file.php";
            } else {
                require "admin" . DS . "list.php";
            }

            // Load Modals
        	require "admin" . DS . "modal-folder.php";
        	require "admin" . DS . "modal-edit.php";
        	require "admin" . DS . "modal-delete.php";
            if(PAW_MEDIA_PLUS) {
                require "admin" . DS . "modal-search.php";
            }

            // Load Icons
            require "admin" . DS . "_icons.php";

            // Get Content
            $add = ob_get_contents();
            ob_end_clean();

            // Inject Admin Page
            $regexp = "/(\<div class=\"col\-lg\-10 pt\-3 pb\-1 h\-100\">)(.*?)(\<\/div\>)/s";
            $content = preg_replace($regexp, "$1{content}$3", $content);
            $content = str_replace("{content}", $add, $content);
            print($content);
        }

        /*
         |  HOOK :: ADMIN CONTENT AREA
         |  @since  0.1.0
         */
        private function adminContentArea(): void {
            global $url;
            global $security;
            global $media_admin;
            global $media_manager;

            // Init Admin
            $media_admin->path = explode("/", trim($url->slug(), "/"))[0];
            $media_admin->view = "modal";
            $media_admin->method = "index";
            $media_admin->query = [ ];

            // Prepare Query
            $absolute = PAW_MEDIA_ROOT;
            if(!empty($_GET["path"]) && ($temp =  MediaManager::absolute($_GET["path"])) !== null) {
                $absolute = $temp;
            }
            $relative = MediaManager::relative($absolute);

            // Load Main Content Modal
            include "admin" . DS . "modal.php";

            // Load Modals
        	require "admin" . DS . "modal-folder.php";
        	require "admin" . DS . "modal-edit.php";
        	require "admin" . DS . "modal-delete.php";
            if(PAW_MEDIA_PLUS) {
                require "admin" . DS . "modal-search.php";
            }

            // Load Icons
            require "admin" . DS . "_icons.php";
        }
    }
