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
         |
         |  @return bool    TRUE if the upgrade was successfullfy, FALSE if not.
         */
        public function init(): bool {
            $this->dbFields = [
                "version"           => self::VERSION,   // Installed version

                // Core Settings
                "allowed_admin_roles"   => "",          // Allowed Roles for the custom admin page
                "allowed_modal_roles"   => "",          // Allowed Roles for the content modal
                "allow_html_upload"     => true,        // Allow HTML upload
                "allow_php_upload"      => false,       // Allow PHP upload
                "allow_js_upload"       => false,       // Allow JavaScript upload
                "allow_root_upload"     => false,       // Allow File-Upload on Root
                "custom_mime_types"     => [ ],         // Custom MIME Types with file extensions

                // Advanced Settings
                "resolve_folders"       => "symlink",   // Resolve temporary page folders
                "root_directory"        => "root",      // Default root directory
                "enable_ajax_page"      => false,       // Enable AJAX on Media Admin Page

                // Default Settings
                "layout"                => "table",     // Media Manager Layout ("table" or "grid")
                "items_order"           => "ASC",       // *Items Order (ASC || DESC)
                "items_per_page"        => 0,           // *Items per Page (0 = all)
            ];
            return true;
        }

        /*
         |  PLUGIN :: INSTALL
         |  @since  0.2.0
         |
         |  @return bool    TRUE if the upgrade was successfullfy, FALSE if not.
         */
        public function install($position = 1): bool {
            parent::install($position);
            $this->dbFields["allowed_admin_roles"] = $this->db["allowed_admin_roles"] = ["admin", "editor"];
            $this->dbFields["allowed_modal_roles"] = $this->db["allowed_modal_roles"] = ["admin", "editor", "author"];
            return $this->save();
        }

        /*
         |  PLUGIN :: UPGRADE
         |  @since  0.2.0
         |
         |  @param  string  The current installed version.
         |
         |  @return bool    TRUE if the upgrade was successfullfy, FALSE if not.
         */
        public function upgrade(string $version): bool {

            // Upgrade 0.1.x to 0.2.x
            if(version_compare($version, "0.2.0", "<")) {
                @unlink(PAW_MEDIA_PATH . "admin" . DS . "file.php");
                @unlink(PAW_MEDIA_PATH . "admin" . DS . "form.php");
                @unlink(PAW_MEDIA_PATH . "admin" . DS . "list.php");
                @unlink(PAW_MEDIA_PATH . "admin" . DS . "modal.php");
                @unlink(PAW_MEDIA_PATH . "admin" . DS . "modal-folder.php");
                @unlink(PAW_MEDIA_PATH . "admin" . DS . "css" . DS . "media.css");

                $this->db = array_merge($this->dbFields, $this->db);
                return $this->save();
            }
            return true;
        }

        /*
         |  PLUGIN :: REMOVE PLUGIN
         |  @since  0.1.0
         |
         |  @return bool    TRUE if the upgrade was successfullfy, FALSE if not.
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
         |  PLUGIN :: GET VALUE
         |  @since  0.2.0
         |
         |  @param  string  The settings name.
         |  @param  bool    TRUE to return a sanitized value, FALSE to do it not.
         |
         |  @return any     The respective (may sanitized) settings value.
         */
        public function getValue($field, $html = true) {
            if(isset($this->db[$field])) {
                return $this->db[$field];
            }
            return $this->dbFields[$field] ?? null;
        }

        /*
         |  PLUGIN :: INIT IF INSTALLED
         |  @since  0.1.0
         |  @info   The installed method is called twice during the bludit boot. The first call
         |          initializes the plugin, and the second one prepares the additional classes.
         |
         |  @return bool    TRUE if the upgrade was successfullfy, FALSE if not.
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

            // Configure Default Values
            $this->dbFields["allowed_admin_roles"] = ["admin", "editor"];
            $this->dbFields["allowed_modal_roles"] = ["admin", "editor", "author"];

            // Do Upgrade & Return
            if(version_compare($this->db["version"] ?? "0.1.1", self::VERSION, "<")) {
                $this->upgrade($this->db["version"] ?? "0.1.1");
            }
            return true;
        }

        /*
         |  HOOK :: PLUGIN CONFIG FORM
         |  @since  0.1.0
         |
         |  @return void
         */
        public function form(): void {
            if(DEBUG_MODE && ($_GET["translate"] ?? "") === "true") {
                bt_fetch(PAW_MEDIA_PATH);
            }
            include "admin" . DS . "admin-form.php";
        }

        /*
         |  HOOK :: PLUGIN CONFIG FORM SUBMIT
         |  @since  0.1.0
         |
         |  @return bool    TRUE if the upgrade was successfullfy, FALSE if not.
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

                // Validate Roles
                if(strpos($field, "allowed") === 0) {
                    $this->db[$field] = ["admin"];

                    if(in_array("editor", ($data[$field] ?? []))) {
                        $this->db[$field][] = "editor";
                    }
                    if(in_array("author", ($data[$field] ?? []))) {
                        $this->db[$field][] = "author";
                    }
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
         |
         |  @return void
         */
        public function beforeAdminLoad(): void {
            global $url;
            global $media_admin;

            // Trigger on 'Media' View
            if(strpos($url->slug(), "media") !== 0) {
                return;
            }

            // Change Layout
            $layout = $_GET["layout"] ?? $_POST["layout"] ?? null;
            if(in_array($layout, ["table", "grid"])) {
                $this->setField("layout", $layout);
            }

            // Check Administration
            if($media_admin->ajax && (strpos($_SERVER["HTTP_REFERER"], "new-content") !== false || strpos($_SERVER["HTTP_REFERER"], "edit-content") !== false)) {
                checkRole($this->getValue("allowed_modal_roles"));
                $view = "modal";
            } else {
                checkRole($this->getValue("allowed_admin_roles"));
                $view = "media";
            }

            // Init Administration
            $handle = explode("/", trim($url->slug(), "/"));
            $media_admin->custom = ($view === "media");
            $media_admin->method = $handle[1] ?? null;

            // Handle Request
            if(isset($handle[1])) {
                $media_admin->request();
            }
        }

        /*
         |  HOOK :: ADMIN HEADER
         |  @since  0.1.0
         |
         |  @return string  The media stylesheets and scripts.
         */
        public function adminHead(): string {
            global $url;
            global $login;

            // Set Data
            if(strpos($url->slug(), "media") === 0) {
                $admin = $this->getValue("enable_ajax_page");
            } else {
                $admin = true;
            }

            // Configure PLUS Metadata
            if(PAW_MEDIA_PLUS && array_key_exists("name", $this->metadata)) {
                $this->metadata["name"] .= '<span class="paw-plus">PLUS</span>';
                $this->metadata["description"] .= ' <b style="font-weight:600;">' . bt_("Thanks for your Support!") . '</b>';
            }

            // Return Scripts and Stylesheets
            ob_start();
            ?>
                <script type="text/javascript" src="<?php echo PAW_MEDIA_DOMAIN; ?>admin/js/custom-file.min.js?ver=1.3.4"></script>
                <script type="text/javascript" src="<?php echo PAW_MEDIA_DOMAIN; ?>admin/js/dropzone.min.js?ver=5.7.0"></script>
                <script type="text/javascript" src="<?php echo PAW_MEDIA_DOMAIN; ?>admin/js/media.js?ver=<?php echo self::VERSION; ?>"></script>
                <?php if(PAW_MEDIA_PLUS) { ?><script type="text/javascript" src="<?php echo PAW_MEDIA_DOMAIN; ?>admin/js/media-plus.js?ver=<?php echo self::VERSION; ?>"></script><?php } ?><?php echo PHP_EOL; ?>
                <script type="text/javascript" src="<?php echo PAW_MEDIA_DOMAIN; ?>admin/js/admin.js?ver=<?php echo self::VERSION; ?>"></script>
                <?php if(PAW_MEDIA_PLUS) { ?><script type="text/javascript" src="<?php echo PAW_MEDIA_DOMAIN; ?>admin/js/admin-plus.js?ver=<?php echo self::VERSION; ?>"></script><?php } ?><?php echo PHP_EOL; ?>
                <script type="text/javascript">
                    ;(function(Media) {
                        Media.admin = <?php echo ($admin)? "true": "false"; ?>;
                        Media.ajax = "<?php echo DOMAIN_ADMIN; ?>media/";
                        Media.strings = {
                            "js-error-title":   '<?php bt_e("JavaScript Error"); ?>',
                            "js-error-text":    '<?php bt_e("An JavaScript error occured, please reload the page and try again."); ?>',
                            "js-form-create":   '<?php bt_e("Create Form"); ?>',
                            "js-form-search":   '<?php bt_e("Search Form"); ?>',
                            "js-form-move":     '<?php bt_e("Move Form"); ?>',
                            "js-form-upload":   '<?php bt_e("Upload Form"); ?>',
                            "js-link-delete":   '<?php bt_e("Delete"); ?>',
                            "js-link-favorite": '<?php bt_e("Favorite"); ?>',
                            "js-button-goback": '<?php bt_e("Go Back"); ?>',
                            "js-media-title":   '<?php echo "Media" . (PAW_MEDIA_PLUS? "+": ""); ?>',
                            "js-unknown":       '<?php bt_e("An unknown error is occured."); ?>',
                            "js-pdf-unsupport": '<?php bt_e("PDF is not supported on your browser."); ?>',
                            "js-click-here":    '<?php bt_e("Click here to view the PDF file."); ?>'
                        };
                    }).call(window, window.Media);

                    <?php if($login->role() === "author" && in_array("author", $this->getValue("allowed_admin_roles"))) { ?>
                    document.addEventListener("DOMContentLoaded", function() {
                        let navi = document.querySelector(".sidebar ul.nav");
                        let empty = document.createElement("LI");
                        empty.className = "nav-item";
                        empty.innerHTML = "<hr>";
                        let media = document.createElement("LI");
                        media.className = "nav-item";
                        media.innerHTML = '<?php echo $this->adminSidebar(); ?>';

                        navi.insertBefore(empty, navi.children[navi.children.length-1]);
                        navi.insertBefore(media, navi.children[navi.children.length-1]);
                    });
                    <?php } ?>
                </script>
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
         |
         |  @return string  The sidebar navigation item.
         */
        public function adminSidebar(): string {
            global $login;
            global $media_admin;

            // Check Roles
            if(!in_array($login->role(), $this->getValue("allowed_admin_roles"))) {
                return '';
            }

            // Return Link
            if(PAW_MEDIA_PLUS) {
                return '<a href="' . $media_admin->buildURL("media") . '" class="nav-link">Media+</a>';
            }
            return '<a href="' . $media_admin->buildURL("media") . '" class="nav-link">Media</a>';
        }

        /*
         |  HOOK :: BEFORE ADMIN CONTENT
         |  @info   Fetch the HTML content, to inject the media admin page.
         |  @since  0.1.0
         |
         |  @return void
         */
        public function adminBodyBegin(): void {
            global $login;
            global $media_admin;

            // Check if Administration Page is shown
            if(!$media_admin->custom && empty($media_admin->method)) {
                return;
            }

            // Check Roles
            if(!in_array($login->role(), $this->getValue("allowed_admin_roles"))) {
                return;
            }

            // Catch Page not Found Message
            ob_start();
        }

        /*
         |  HOOK :: AFTER ADMIN CONTENT
         |  @info   Handle the HTML content, to inject the media admin page.
         |  @since  0.1.0
         |
         |  @return void
         */
        public function adminBodyEnd(): void {
            global $url;
            global $login;
            global $security;
            global $media_admin;
            global $media_manager;

            // Check if `new-content` or `edit-content` page is shown
            $page = explode("/", $url->slug())[0];
            if($page === "new-content" || $page === "edit-content") {
                $this->adminContentArea();
                return;
            }
            if(!$media_admin->custom && empty($media_admin->method)) {
                return;
            }

            // Check Roles
            if(!in_array($login->role(), $this->getValue("allowed_admin_roles"))) {
                return;
            }

            // Get Content
            $content = ob_get_contents();
            ob_end_clean();

            // Set Pathdata
            $pathinfo = MediaManager::pathinfo($_GET["path"] ?? "");
            if($pathinfo === null) {
                Redirect::url($media_admin->buildURL("media"));
                return;
            }

            // Load Admin Page
            ob_start();

            // Load Admin Page
            require "admin" . DS . "admin-page.php";
        	require "admin" . DS . "modal-create.php";
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
         |
         |  @return void
         */
        private function adminContentArea(): void {
            global $url;
            global $login;
            global $security;
            global $media_admin;
            global $media_manager;

            // Check Roles
            if(!in_array($login->role(), $this->getValue("allowed_modal_roles"))) {
                return;
            }

            // Prepare Query
            $pathinfo = MediaManager::pathinfo($_GET["path"] ?? "");

            // Load Admin Modal
            include "admin" . DS . "admin-modal.php";
        	require "admin" . DS . "modal-embed.php";
        	require "admin" . DS . "modal-create.php";
        	require "admin" . DS . "modal-edit.php";
        	require "admin" . DS . "modal-delete.php";
            if(PAW_MEDIA_PLUS) {
                require "admin" . DS . "modal-search.php";
            }

            // Load Icons
            require "admin" . DS . "_icons.php";
        }
    }
