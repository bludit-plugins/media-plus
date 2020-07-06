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
         |  @param  string  The data before the action has been applied or NULL.
         |                      move        The path to the old location w\ folder / filename
         |                      rename      The path to the location w\ old folder / filename
         |                      revise      The path to the old source file or NULL if overwritten
         |                      edit        NULL
         |  @param  string  The data after the action has been applied.
         |                      move        The path to the new location w\ folder / filename
         |                      rename      The path to the location w\ new folder / filename
         |                      revise      The path to the new source file
         |                      edit        The path to the source file
         |
         |  @return bool    TRUE if everything is fluffy, FALSE if not.
         */
        public function log(string $action, ?string $before, string $after): bool {
            global $login;

            // Check Arguments
            if(($after = MediaManager::slug($after)) === null) {
                return false;
            }

            // Check DB Item
            if($action === "move" || $action === "rename") {
                if(array_key_exists($before, $this->db)) {
                    $this->db[$after] = $this->db[$before];
                    unset($this->db[$before]);
                }
            }
            if(!array_key_exists($after, $this->db)) {
                $this->db[$after] = [ ];
            }

            // Log Actions
            $changes = ["action" => $action, "username" => $login->username()];
            switch($action) {
                case "move":
                    $this->db[$after][time()] = array_merge($changes, [
                        "before"    => $before,
                        "after"     => $after
                    ]);
                    break;
                case "rename":
                    $this->db[$after][time()] = array_merge($changes, [
                        "before"    => basename($before),
                        "after"     => basename($after)
                    ]);
                    break;
                case "edit":
                    $this->db[$after][time()] = array_merge($changes, [
                        "before"    => null,
                        "after"     => null
                    ]);
                    break;
                case "revise":
                    $this->db[$after][time()] = array_merge($changes, [
                        "before"    => $before,
                        "after"     => $after
                    ]);

                    if(!empty($before)) {
                        if(!array_key_exists($before, $this->db)) {
                            $this->db[$before] = [ ];
                        }
                        $this->db[$before][time()] = array_merge($changes, [
                            "before"    => $before,
                            "after"     => $after
                        ]);
                    }
                    break;
                default:
                    return false;
            }

            // Add Action
            return $this->save();
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
