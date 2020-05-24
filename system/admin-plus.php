<?php
declare(strict_types=1);
/*
 |  Media       The advanced Media & File Manager for Bludit
 |  @file       ./system/admin-plus.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.2.0 [0.1.0] - Beta
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
         |  METHOD :: SEARCH
         |  @since  0.1.0
         |
         |  @param  array   The requested data array.
         |                      'path'      The current path.
         |                      'search'    The search string.
         |
         |  @return void    Prints the JSON output on AJAX requests, True otherwise.
         */
        protected function _search(array $data = []) {
            global $media_manager;

            // Validate Path
            if(($path = MediaManager::absolute($data["path"])) === null) {
                return $this->bye(false, bt_("The passed path is invalid."));
            }
            $base = MediaManager::slug($path);

            // AJAX Search
            if($this->ajax) {
                $content = $this->renderList($media_manager->search($data["search"], $base), $base);
                return $this->bye(true, bt_("The path is valid."), ["content" => $content, "path" => $base]);
            }

            // Non-AJAX Search
            $this->method = "index";
            $this->search = Sanitize::html(strip_tags($data["search"]));
            return true;
        }

        /*
         |  METHOD :: EDIT FILE
         |  @since  0.2.0
         |
         |  @param  array   The requested data array.
         |                      'file'      The path to the file (with file name).
         |                      'content'   The new content for the file.
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        protected function _edit(array $data) {
            global $login;
            global $media_manager;
            global $media_history;

            // Validate Path
            if(($file = MediaManager::absolute($data["file"])) === null) {
                return $this->bye(false, bt_("The passed file is invalid."));
            }

            // Edit Content
            if(isset($data["content"])) {
                if(@file_put_contents($file, $data["content"]) === false) {
                    return $this->bye(false, bt_("The file could not be updated."), ["path" => $file]);
                }
                $media_history->log("edit", MediaManager::slug($file), $login->username());
                return $this->bye(true, bt_("The file could be updated."), ["path" => $file]);
            }

            // Error
            return $this->bye(false, bt_("The action was called incorrectly."), ["path" => $file]);
        }

        /*
         |  METHOD :: FAVORITE
         |  @since  0.1.0
         |
         |  @param  array   The requested data array.
         |                      'path'      The current path (with file or directory name).
         |
         |  @return void    Prints the JSON output on AJAX requests, Redirects otherwise.
         */
        protected function _favorite(array $data = []) {
            global $login;
            global $users;

            // Check Path
            if(($path = MediaManager::absolute($data["path"])) === false) {
                return $this->bye(false, bt_("The passed path is invalid."));
            }

            // Prepare Query
            $query = [
                "path"      => dirname($path),
                "favorite"  => [$data["path"], !$this->isFavorite($data["path"])]
            ];
            if(strpos($_SERVER["HTTP_REFERER"] ?? "", basename($path)) !== false) {
                $query["path"] .= DS . basename($path);
            }

            // Handle & Return
            if($this->setFavorite($path) === false) {
                return $this->bye(false, bt_("You favorites could not be updated."), $query);
            }
            return $this->bye(true, bt_("You favorites have been updated successfully."), $query);
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
            global $login;
            global $users;

            // Get Login
            if(empty($login)) {
                $login = new Login();
            }

            // Get User
            $user = new User($login->username());
            $favs = $user->getValue("media_favorites");
            $favs = is_array($favs)? $favs: [];

            // Update Favorites
            foreach($favs AS &$fav) {
                if($fav === $slug_old || strpos($fav, trim($slug_old, "/") . "/") === 0) {
                    if($slug_new === null) {
                        $fav = null;
                    } else {
                        $fav = substr_replace($fav, trim($slug_new, "/"), 0, strlen(trim($slug_old, "/")));
                    }
                }
            }

            // Store Data
            $users->db[$login->username()]["media_favorites"] = array_filter($favs);
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
                <div class="btn-group tools">
                    <div class="btn-group">
                        <button class="btn btn-secondary dropdown-toggle" data-toggle="dropdown"><span class="fa fa-heart"></span></button>
                        <div class="media-favorites-dropdown dropdown-menu dropdown-menu-right shadow-sm">
                            <?php if(empty($favs)){ ?>
                                <span class="dropdown-item disabled text-center"><?php bt_e("No Favorites available"); ?></span>
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
                    </div>
                    <a href="#media-search" class="btn btn-secondary" data-toggle="modal"><span class="fa fa-search"></span></a>
                </div>
            <?php
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }
    }
