<?php
/*
 |  BLU-TOOLS   A bunch of useful Bludit Tools 4 the Plugin Development.
 |  @file       ./functions.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    1.0.0 [1.0.0] - Stable
 |
 |  @website    https://github.com/pytesNET/blu-tools
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
##
#   S18N TRANSLATION SYSTEM
#       1.  bt_(<string>)
#       2.  bt_e(<string>)
#       3.  bt_a(<string>, <array>)
#       4.  bt_ae(<string>, <array>)
#       5.  bt_n(<string, <string>, <int>)
#       6.  bt_ne(<string>, <string>, <int>)
#       7.  bt_fetch(<string>, <string>)
#
#   GENERAL HELPER FUNCTIONs
#       1.  bt_selected(<string|bool>, <string|bool>, <bool>)
#       2.  bt_checked(<string|bool>, <string|bool>, <bool>)
##

    /*
     |  S18N :: TRANSLATE A STRING
     |  @since  1.0.0 [1.0.0]
     |
     |  @param  string  The desired string to translate.
     |
     |  @return string  The parsed, may translated, string.
     */
    if(!function_exists("bt_")) {
        function bt_(string $string): string {
            global $L;
            $hash = "s18n-" . md5(strtolower($string));
            $value = $L->g($hash);
            return ($hash === $value)? $string: $value;
        }
    }

    /*
     |  S18N :: PRINT A TRANSLATED STRING
     |  @since  1.0.0 [1.0.0]
     |
     |  @param  string  The desired string to translate.
     |
     |  @return void    <print>
     */
    if(!function_exists("bt_e")) {
        function bt_e(string $string): void {
            print(bt_($string));
        }
    }

    /*
     |  S18N :: TRANSLATE AND PARSE A STRING
     |  @since  1.0.0 [1.0.0]
     |
     |  @param  string  The desired string to translate.
     |  @param  array   Some additional key => value paired arguments to render.
     |
     |  @return string  The parsed, may translated, string.
     */
    if(!function_exists("bt_a")) {
        function bt_a(string $string, array $array = [ ]): string {
            return strtr(bt_($string), $array);
        }
    }

    /*
     |  S18N :: PRINT A TRANSLATED AND PARSED STRING
     |  @since  1.0.0 [1.0.0]
     |
     |  @param  string  The desired string to translate.
     |  @param  array   Some additional key => value paired arguments to render.
     |
     |  @return void    <print>
     */
    if(!function_exists("bt_ae")) {
        function bt_ae(string $string, array $array = [ ]): void {
            print(strtr(bt_($string), $array));
        }
    }

    /*
     |  S18N :: TRANSLATE A SINGULAR OR PLURAL STRING
     |  @since  1.0.0 [1.0.0]
     |
     |  @param  string  The desired singular string to translate.
     |  @param  string  The desired plural string to translate.
     |  @param  int     The respective number, which should be used for.
     |
     |  @return string  The parsed, may translated, string.
     */
    if(!function_exists("bt_n")) {
        function bt_n(string $singular, string $plural, int $number = 1): string {
            return bt_(($number === 1)? $singular: $plural);
        }
    }

    /*
     |  S18N :: PRINT A TRANSLATED OR PLURAL STRING
     |  @since  1.0.0 [1.0.0]
     |
     |  @param  string  The desired singular string to translate.
     |  @param  string  The desired plural string to translate.
     |  @param  int     The respective number, which should be used for.
     |
     |  @return void    <print>
     */
    if(!function_exists("bt_ne")) {
        function bt_ne(string $singular, string $plural, int $number = 1): void {
            print(bt_(($number === 1)? $singular: $plural));
        }
    }

    /*
     |  S18N :: FETCH TRANSLATIONs
     |  @since  1.0.0 [1.0.0]
     |
     |  @param  string  The path to the plugin folder to fetch.
     |
     |  @return bool    TRUE if everything is fluffy, FALSE if not.
     */
    if(!function_exists("bt_fetch")) {
        function bt_fetch(string $base, string $dir = ""): bool {
            static $translations = [];

            // Fett Full Path
            $fullpath = rtrim($base, DS) . $dir;

            // Fetch Strings
            if(is_file($fullpath)) {
                if(strpos($fullpath, ".php") !== strlen($fullpath) - 4 || $fullpath === __FILE__) {
                    return false;
                }

                $content = explode("\n", file_get_contents($fullpath));
                foreach($content AS $line) {
                    preg_match_all('/bt(?:_|_e|_a|_ae|_n|_ne)\s*\(\s*([\'"])(.+?)\1(?:\s*,\s*([\'"])(.+?)\3)?/', $line, $matches, PREG_SET_ORDER);
                    if(empty($matches)) {
                        continue;
                    }

                    foreach($matches AS $match) {
                        if(count($match) < 2) {
                            continue;
                        }

                        [$key, $string] = ["s18n-" . md5(strtolower(trim($match[2]))), trim($match[2])];
                        if(strlen($string) > 0 && !isset($translations[$key])) {
                            $translations[$key] = $string;
                        }

                        // Plural Strings (as used on bt_n and bt_ne)
                        if(count($match) === 5) {
                            [$key, $string] = ["s18n-" . md5(strtolower(trim($match[4]))), trim($match[4])];
                            if(strlen($string) > 0 && !isset($translations[$key])) {
                                $translations[$key] = $string;
                            }
                        }
                    }
                }
                return true;
            }

            // Recursive Walker
            if(is_dir($fullpath)) {
                if($handle = opendir($fullpath)) {
                    while(($file = readdir($handle)) !== false) {
                        if(in_array($file, [".", "..", "languages"])) {
                            continue;
                        }
                        bt_fetch($base, $dir . DS . $file);
                    }
                    closedir($handle);
                }
                if(!empty($dir)) {
                    return true;
                }
            }

            // Write Translation Strings
            $langpath = rtrim($base) . DS . "languages" . DS;
            if($handle = opendir($langpath)) {
                while(($file = readdir($handle)) !== false) {
                    if(in_array($file, [".", ".."])) {
                        continue;
                    }
                    if(is_dir($langpath . $file) || strpos($file, ".json") !== strlen($file) - 5) {
                        continue;
                    }

                    $content = file_get_contents($langpath . $file);
                    $content = json_decode($content, true);

                    foreach($translations AS $key => $value) {
                        if(array_key_exists($key, $content)) {
                            continue;
                        }
                        $content[$key] = $value;
                    }

                    $content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    file_put_contents($langpath . $file, $content);
                }
                closedir($handle);
            }
            return true;
        }
    }

    /*
     |  HELPER :: GET SELECTED STRING
     |  @since  1.0.0
     |
     |  @param  bool    The first set value.
     |  @param  multi   The second value to compare with.
     |  @param  bool    TRUE to print ' selected="selected"', FALSE to return it as string.
     |
     |  @return multi   The respective string or null.
     */
    if(!function_exists("bt_selected")) {
        function bt_selected(/* string | bool */ $field, /* string | bool */ $compare = true, bool $print = true): ?string {
            $selected = ($field === $compare)? ' selected="selected"': '';
            if(!$print){
                return $selected;
            }
            print($selected);
            return null;
        }
    }

    /*
     |  HELPER :: GET CHECKED STRING
     |  @since  1.0.0
     |
     |  @param  bool    The first set value.
     |  @param  multi   The second value to compare with.
     |  @param  bool    TRUE to print ' checked="checked"', FALSE to return it as string.
     |
     |  @return multi   The respective string or null.
     */
    if(!function_exists("bt_checked")) {
        function bt_checked(/* string | bool */ $field, /* string | bool */ $compare = true, bool $print = true): ?string {
            $checked = ($field === $compare)? ' checked="checked"': '';
            if(!$print){
                return $checked;
            }
            print($checked);
            return null;
        }
    }
