<?php
declare(strict_types=1);
/*
 |  Media       An advanced Media & File Manager for Bludit
 |  @file       ./system/manager-plus.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.1 [0.1.0] - Alpha
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */

    class MediaManagerPlus extends MediaManager {
        /*
         |  HANDLE :: SEARCH CONTENT
         |  @since  0.1.0
         |
         |  @param  string  The search, which should be applied.
         |  @param  string  The path to the directory, which should be listed.
         |
         |  @return multi   The files and folders within the directory, null on failure.
         */
        public function search(string $search, ?string $path = null): ?array {
            global $media_plugin;

            // Validate Path
            if(($path = self::absolute(empty($path)? DS: $path)) === null) {
                return null;
            }
            $search = Sanitize::html(strip_tags($search));

            // List Data
            $files = [];
            $folders = [];

            // Recursive
            $walk = function($path, $search, $func) use (&$files, &$folders){
                if($handle = opendir($path)) {
                    while(($file = readdir($handle)) !== false) {
                        if($file === "." || $file === "..") {
                            continue;
                        }
                        $real = realpath($path . DS . $file);

                        // Check Directory
                        if(is_dir($path . DS . $file)) {
                            if(stripos($file, $search) !== false) {
                                $folders[$real] = $path . DS . $file;
                            }
                            $func($path . DS . $file, $search, $func);
                        }

                        // Check File
                        if(is_file($path . DS . $file)) {
                            if(stripos($file, $search) !== false) {
                                $files[$real] = $path . DS . $file;
                            }
                        }
                    }
                }
                closedir($handle);
            };
            $walk($path, $search, $walk);

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

        /*
         |  HANDLE :: LIST CONTENT [ATTACH SEARCH]
         |  @since  0.1.0
         |
         |  @param  string  The path to the directory, which should be listed.
         |
         |  @return multi   The files and folders within the directory, null on failure.
         */
        public function list(?string $folder = null): ?array {
            $search = $_POST["search"] ?? $_GET["search"] ?? null;
            if(empty($search)) {
                return parent::list($folder);
            }
            return $this->search($search, $folder);
        }
    }
