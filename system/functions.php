<?php
declare(strict_types=1);
/*
 |  Media       An advanced Media & File Manager for Bludit
 |  @file       ./system/functions.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.0 [0.1.0] - Alpha
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */

    /*
     |  S18N :: FORMAT AND GET STRING
     |  @since  0.1.0
     |
     |  @param  string  The respective string to translate.
     |  @param  array   Some additional array for `printf()`.
     |
     |  @return string  The translated and formated string.
     */
    if(!function_exists("paw__")) {
        function paw__(string $string, array $args = array()): string {
            global $L;
            $hash = "s18n-" . md5(strtolower($string));
            $value = $L->g($hash);
            if($hash === $value){
                $value = $string;
            }
            return (count($args) > 0)? vsprintf($value, $args): $value;
        }
    }

    /*
     |  S18N :: FORMAT AND PRINT STRING
     |  @since  0.1.0
     |
     |  @param  string  The respective string to translate.
     |  @param  array   Some additional array for `printf()`.
     |
     |  @return <print>
     */
    if(!function_exists("paw_e")) {
        function paw_e(string $string, array $args = array()): void {
            print(paw__($string, $args));
        }
    }

    /*
     |  FORM :: GET SELECTED STRING
     |  @since  0.1.0
     |
     |  @param  bool    The value of the <option> field or a boolean.
     |  @param  multi   The value to compare with.
     |  @param  bool    TRUE to print `selected="selected"`, FALSE to return it as string.
     |
     |  @return multi   The respective string or null.
     */
    if(!function_exists("paw_selected")) {
        function paw_selected(/* string | bool */ $field, /* string | bool */ $compare = true, bool $print = true): ?string {
            if($field === $compare) {
                $selected = 'selected="selected"';
            } else {
                $selected = '';
            }
            if(!$print){
                return $selected;
            }
            print($selected);
            return null;
        }
    }

    /*
     |  FORM :: GET CHECKED STRING
     |  @since  0.1.0
     |
     |  @param  bool    The value of the <input /> field or a boolean.
     |  @param  multi   The value to compare with.
     |  @param  bool    TRUE to print `checked="checked"`, FALSE to return it as string.
     |
     |  @return multi   The respective string or null.
     */
    if(!function_exists("paw_checked")) {
        function paw_checked(/* string | bool */ $field, /* string | bool */ $compare = true, bool $print = true): ?string {
            if($field === $compare) {
                $checked = 'checked="checked"';
            } else {
                $checked = '';
            }
            if(!$print){
                return $checked;
            }
            print($checked);
            return null;
        }
    }
