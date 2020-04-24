<?php
declare(strict_types=1);
/*
 |  Media       An advanced Media & File Manager for Bludit
 |  @file       ./system/admin-plus.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.0 [0.1.0] - Alpha
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */

    class MediaAdminPlus extends MediaAdmin {
        /*
         |  CURRENT SEARCH QUERY
         */
        public $search = "";

        /*
         |  METHOD :: SEARCH
         |  @since  0.1.0
         */
        protected function _search(array $data = []) {
            global $media_manager;

            // Validate Path
            if(($path = MediaManager::absolute($data["path"])) === null) {
                return $this->bye(false, paw__("The passed path is invalid."));
            }
            $base = MediaManager::slug($path);

            // AJAX Search
            if($this->ajax) {
                $content = $this->renderList($media_manager->search($data["search"], $base), $base);
                return $this->bye(true, paw__("The path is valid."), ["content" => $content, "path" => $base]);
            }

            // Non-AJAX Search
            $this->method = "index";
            $this->search = Sanitize::html(strip_tags($data["search"]));
            return true;
        }

        /*
         |  METHOD :: FAVORITE
         |  @since  0.1.0
         */
        protected function _favorite(array $data = []) {
            global $login;
            global $users;

            // Check Path
            if(($path = MediaManager::absolute($data["path"])) === false) {
                return $this->bye(false, paw__("The passed path is invalid."));
            }

            // Prepare Query
            $query = [
                "path"      => dirname($path),
                "favorite"  => [$data["path"], !$this->isFavorite($data["path"])]
            ];
            if(strpos($_SERVER["HTTP_REFERER"] ?? "", "&file=") !== false) {
                $query["file"] = basename($path);
            }

            // Handle & Return
            if($this->setFavorite($data["path"]) === false) {
                return $this->bye(false, paw__("You favorites could not be updated."), $query);
            }
            return $this->bye(true, paw__("You favorites have been updated successfully."), $query);
        }

        /*
         |  FAVORITES :: GET
         |  @since  0.1.0
         */
        protected function setFavorite($path): bool {
            global $login;
            global $users;

            // Validate Path
            $path = trim(MediaManager::slug($path), "/");

            // Get User
            $user = new User($login->username());
            $favs = $user->getValue("media_favorites");
            $favs = is_array($favs)? $favs: [];

            // Handle Favorite
            if(in_array($path, $favs)) {
                unset($favs[array_search($path, $favs)]);
            } else {
                $favs[] = $path;
            }

            // Get Favorites
            $users->db[$login->username()]["media_favorites"] = $favs;
            return $users->save();
        }

        /*
         |  FAVORITES :: GET
         |  @since  0.1.0
         */
        public function getFavorites(): array {
            global $login;

            // Get User
            $user = new User($login->username());

            // Get Favorites
            $favs = $user->getValue("media_favorites");
            return is_array($favs)? $favs: [];
        }

        /*
         |  FAVORITES :: CHECK
         |  @since  0.1.0
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
                                <span class="dropdown-item disabled text-center"><?php paw_e("No Favorites available"); ?></span>
                            <?php } else { ?>
                                <?php
                                    $files = [];
                                    $folders = [];
                                    foreach($favs AS $fav) {
                                        if(($absolute = MediaManager::absolute($fav)) === null) {
                                            continue;
                                        }

                                        if(is_file($absolute)) {
                                            $url = $this->buildURL("media", [
                                                "path" => str_replace("\\", "/", MediaManager::relative(dirname($absolute))),
                                                "file" => basename($fav)
                                            ]);
                                            $files[] = '<a href="'.$url.'" class="dropdown-item" data-media-action="list"><span class="fa fa-file"></span>'. basename($fav) .'</a>';
                                        } else {
                                            $url = $this->buildURL("media", [
                                                "path" => str_replace("\\", "/", MediaManager::relative($absolute))
                                            ]);
                                            $folders[] = '<a href="'.$url.'" class="dropdown-item" data-media-action="list"><span class="fa fa-folder"></span>'. basename($fav) .'</a>';
                                        }
                                    }

                                    if(empty($files) XOR empty($folders)) {
                                        print(implode("\n", empty($files)? $folders: $files));
                                    } else {
                                        ?>
                                            <ul class="media-favorites-tabs nav nav-tabs">
                                                <li class="nav-item">
                                                    <a href="#media-favorites-tab-folders" class="nav-link active" data-toggle="tab"><?php paw_e("Folders"); ?></a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="#media-favorites-tab-files" class="nav-link" data-toggle="tab"><?php paw_e("Files"); ?></a>
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
