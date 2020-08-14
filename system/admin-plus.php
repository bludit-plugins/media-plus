<?php
declare(strict_types=1);
/*
 |  Media       The advanced Media & File Manager for Bludit
 |  @file       ./system/admin-plus.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.2.1 - Beta
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
    defined("BLUDIT") or die("Go directly to Jail. Do not pass Go. Do not collect 200 Cookies!");

    // [Plus] Administration
    class MediaAdminPlus extends MediaAdmin {
        /*
         |  CURRENT SEARCH QUERY
         */
        public $search = "";

        /*
         |  CONSTRUCTOR
         |  @since  0.2.0
         */
        public function __construct() {
            parent::__construct();

            $this->methods["search"] = ["GET", "POST"];
            $this->methods["edit"] = ["POST"];
            $this->methods["favorite"] = ["GET", "POST"];
        }

        /*
         |  METHOD :: SEARCH
         |  @type   GET | POST
         |  @since  0.1.0
         |
         |  @param  string  The absolute path, where the action should be happen.
         |  @param  array   The requested data array.
         |                      'search'    The search term.
         |
         |  @return void    Prints the JSON output on AJAX requests, True otherwise.
         */
        protected function _search(string $path, array $data = []) {
            global $media_manager;

            // Check Arguments
            if(!isset($data["search"])) {
                return $this->response(false, bt_a("The action :action was called incorrectly.", [":action" => "/search"]));
            }

            // Success
            return $this->response(true, bt_("The passed search is valid"), [
                "path"      => $path,
                "search"    => Sanitize::html(strip_tags($data["search"])),
                "content"   => $this->renderList($media_manager->search($data["search"], $path), $path)
            ]);
        }

        /*
         |  METHOD :: EDIT FILE
         |  @type   POST
         |  @since  0.2.0
         |
         |  @param  string  The absolute path, where the action should be happen.
         |  @param  array   The requested data array.
         |                      'content'   The new content for the file.
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        protected function _edit(string $path, array $data) {
            global $login;
            global $media_manager;
            global $media_history;

            // Check Arguments
            if(!isset($data["content"]) || !is_file($path)) {
                return $this->response(false, bt_a("The action :action was called incorrectly.", [":action" => "/edit"]));
            }

            // Edit Content
            if(@file_put_contents($path, $data["content"]) === false) {
                return $this->response(false, bt_("The file ':path' could not be updated."));
            }

            // Handle History
            $media_history->log("edit", null, MediaManager::slug($path));

            // Success
            return $this->response(true, bt_("The file could be updated successfully."), [
                "path"  => $path
            ]);
        }

        /*
         |  METHOD :: FAVORITE
         |  @type   GET | POST
         |  @since  0.1.0
         |
         |  @param  string  The absolute path, where the action should be happen.
         |  @param  array   The requested data array.
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        protected function _favorite(string $path, array $data = []) {
            global $login;
            global $users;

            // Set Favourite
            if($this->setFavorite($path) === false) {
                return $this->response(false, bt_("Your favorites could not be updated."));
            }

            // Success
            return $this->response(true, bt_("Your favorites have been updated successfully."), [
                "path"      => $path,
                "favorite"  => $this->isFavorite($path)
            ]);
        }

        /*
         |  FAVORITES :: SET STATUS
         |  @since  0.1.0
         |
         |  @param  string  The path to the file or folder.
         |  @param  multi   NULL to toggle the favourite status, TRUE to set, FALSE to remove.
         |
         |  @return bool    TRUE if everything is fluffy, FALSE if not.
         */
        protected function setFavorite(string $path, ?bool $status = null): bool {
            global $login;
            global $users;

            // Validate Path
            $path = trim(MediaManager::slug($path), "/");

            // Get Login
            if(empty($login)) {
                $login = new Login();
            }

            // Get User
            $user = new User($login->username());
            $favs = $user->getValue("media_favorites");
            $favs = is_array($favs)? $favs: [];

            // Handle Favorite
            $status = $status === null? !in_array($path, $favs): $status;
            if($status && !in_array($path, $favs)) {
                $favs[] = $path;
            } else if(!$status && in_array($path, $favs)) {
                unset($favs[array_search($path, $favs)]);
            }

            // Store Data
            $users->db[$login->username()]["media_favorites"] = $favs;
            return $users->save();
        }

        /*
         |  FAVORITES :: UPDATE FAVORITES PATH
         |  @since  0.2.0
         |
         |  @param  string  The old slug path to the file or folder.
         |  @param  multi   The new slug path to the file or folder, or NULL to remove it.
         |
         |  @return bool    TRUE if everything is fluffy, FALSE if not.
         */
        protected function updateFavorites(string $slug_old, ?string $slug_new): bool {
            global $users;

            // Get User
            foreach($users->db AS $user => &$data) {
                if(!isset($data["media_favorites"])) {
                    continue;
                }

                $favs = $data["media_favorites"];
                if(empty($favs) || !is_array($favs)) {
                    continue;
                }

                foreach($favs AS &$fav) {
                    if($fav === $slug_old || strpos($fav, trim($slug_old, "/") . "/") === 0) {
                        if($slug_new === null) {
                            $fav = null;
                        } else {
                            $fav = substr_replace($fav, trim($slug_new, "/"), 0, strlen(trim($slug_old, "/")));
                        }
                    }
                }
                $data["media_favorites"] = array_filter($favs);
            }
            return $users->save();
        }

        /*
         |  FAVORITES :: GET ALL STATUSSEs
         |  @since  0.1.0
         */
        public function getFavorites(): array {
            global $login;

            // Get Login
            if(empty($login)) {
                $login = new Login();
            }

            // Get User
            $user = new User($login->username());

            // Get Favorites
            $favs = $user->getValue("media_favorites");
            return is_array($favs)? $favs: [];
        }

        /*
         |  FAVORITES :: CHECK
         |  @since  0.1.0
         |
         |  @param  string  The full path to the file or folder.
         |
         |  @return bool    TRUE if the file or folder is set as favorite, FALSE if not.
         */
        public function isFavorite(string $path): bool {
            global $login;

            // Get User
            $user = new User($login->username());

            // Get Favorites
            $favs = $user->getValue("media_favorites");
            $favs = is_array($favs)? $favs: [];

            // Check Path
            $path = MediaManager::slug($path);
            return ($path)? in_array(trim($path, "/"), $favs): false;
        }

        /*
         |  RENDER :: TOOLBAR ACTIONs
         |  @since  0.1.0
         |
         |  @return string  The toolbar HTML content.
         */
        public function renderToolbar(): string {
            $favs = $this->getFavorites();

            // Render
            ob_start();
            ?>
                <div class="tools btn-group">
                    <button class="btn btn-secondary dropdown-toggle" data-toggle="dropdown"><span class="fa fa-heart"></span></button>
                    <div class="media-favorites-dropdown dropdown-menu dropdown-menu-right shadow-sm">
                        <?php if(empty($favs)){ ?>
                            <span class="dropdown-item disabled text-center py-3"><?php bt_e("No Favorites available"); ?></span>
                        <?php } else { ?>
                            <?php
                                $files = [];
                                $folders = [];
                                foreach($favs AS $fav) {
                                    if(($temp = MediaManager::absolute($fav)) === null) {
                                        continue;
                                    }

                                    if(is_file($temp)) {
                                        $url = $this->buildURL("media", ["path" => $fav]);
                                        $files[] = '<a href="'.$url.'" class="dropdown-item" data-media-action="list"><span class="fa fa-file"></span>'. basename($fav) .'</a>';
                                    } else {
                                        $url = $this->buildURL("media", ["path" => $fav]);
                                        $folders[] = '<a href="'.$url.'" class="dropdown-item" data-media-action="list"><span class="fa fa-folder"></span>'. basename($fav) .'</a>';
                                    }
                                }

                                if(empty($files) XOR empty($folders)) {
                                    ?>
                                        <div class="tab-content tab-content-single">
                                            <?php print(implode("\n", empty($files)? $folders: $files)); ?>
                                        </div>
                                    <?php
                                } else {
                                    ?>
                                        <ul class="nav nav-tabs">
                                            <li class="nav-item">
                                                <a href="#media-favorites-tab-folders" class="nav-link active" data-toggle="tab"><?php bt_e("Folders"); ?></a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#media-favorites-tab-files" class="nav-link" data-toggle="tab"><?php bt_e("Files"); ?></a>
                                            </li>
                                        </ul>
                                        <div class="tab-content" id="myTabContent">
                                            <div id="media-favorites-tab-folders" class="tab-pane show active">
                                                <?php print(implode("\n", $folders)); ?>
                                            </div>
                                            <div id="media-favorites-tab-files" class="tab-pane">
                                                <?php print(implode("\n", $files)); ?>
                                            </div>
                                        </div>
                                    <?php
                                }
                            ?>
                        <?php } ?>
                    </div>
                    <a href="#media-search" class="btn btn-secondary" data-toggle="modal"><span class="fa fa-search"></span></a>
                </div>
            <?php
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }
    }
