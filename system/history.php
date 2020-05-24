<?php
declare(strict_types=1);
/*
 |  Media       The advanced Media & File Manager for Bludit
 |  @file       ./system/history.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.2.0 [0.2.0] - Beta
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
    defined("BLUDIT") or die("Go directly to Jail. Do not pass Go. Do not collect 200 Cookies!");

    class MediaHistory extends dbJSON {
        /*
         |  CONSTRUCTOR
         |  @since  0.2.0
         */
        public function __construct() {
            parent::__construct(PAW_MEDIA_WORKSPACE . "history.php");
            if(!file_exists(PAW_MEDIA_WORKSPACE . "history.php")){
                $this->db = [ ];
                $this->save();
            }
        }

        /*
         |  DB :: LOG ACTION
         |  @since  0.2.0
         |
         |  @param  string  The applied action.
         |                      move        The file has been moved.
         |                      rename      The file has been renamed.
         |                      revise      The file has been revised.
         |                      edit        The file has been edited.
         |  @param  string  The slug to the file or folder.
         |  @param  string  The made change.
         |
         |  @return bool    TRUE if everything is fluffy, FALSE if not.
         */
        public function log(string $action, string $slug, /* any */ $after): bool {
            if(!array_key_exists($slug, $this->db)) {
                $this->db[$slug] = [ ];
            }
            $data = &$this->db[$slug];

            // Slug has changed
            if($action === "rename" || $action === "move") {
                $this->db[$after] = $this->db[$slug];
                $data = &$this->db[$after];
                unset($this->db[$slug]);
            }

            // Log Actions
            $changes = ["action" => $action];
            switch($action) {
                case "move":
                    $changes["before"] = $slug;
                    $changes["after"] = $after;
                    break;
                case "rename":
                    $changes["before"] = basename($slug);
                    $changes["after"] = basename($after);
                    break;
                case "edit":
                    $changes["before"] = null;
                    $changes["after"] = $after;
                    break;
                case "revise":
                    $changes["before"] = $slug;
                    $changes["after"] = $after;
                    break;
                case "revised":
                    $changes["before"] = $slug;
                    $changes["after"] = $after;
                    break;
            }

            // Add Action
            if(array_key_exists("before", $changes) && array_key_exists("after", $changes)) {
                $data[time()] = $changes;
                return $this->save();
            }
            return false;
        }

        /*
         |  DB :: GET LOGS
         |  @since  0.2.0
         |
         |  @param  string  The slug to the file or directory.
         |
         |  @return multi   The array on success, NULL on failure.
         */
        public function get(string $slug): ?array {
            if(array_key_exists($slug, $this->db)) {
                krsort($this->db[$slug]);
                return $this->db[$slug];
            }
            return null;
        }

        /*
         |  DB :: REMOVE LOGs
         |  @since  0.2.0
         |
         |  @param  string  The slug to the file or directory.
         |
         |  @return bool    TRUE if everything is fluffy, FALSE if not.
         */
        public function delete(string $slug): bool {
            if(array_key_exists($slug, $this->db)) {
                unset($this->db[$slug]);
                return $this->save();
            }
            return false;
        }
    }
