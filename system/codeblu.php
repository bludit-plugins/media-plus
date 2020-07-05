<?php
/*
 |  CODEBLU     This additional file packs all codeBLU plugins.
 |  @file       ./codeblu.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    1.0.0 [1.0.0] - Stable
 |
 |  @website    https://github.com/pytesNET/blu-tools
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */

    if(version_compare(($GLOBALS["_CODEBLU_VERSION"] ?? "0.0.1"), "1.0.0", ">")) {
        return;
    }
    $GLOBALS["_CODEBLU_VERSION"] = "1.0.0";

    // Define codeBLU Plugins
    if(empty($GLOBALS["_CODEBLU_PLUGINS"])) {
        $GLOBALS["_CODEBLU_PLUGINS"] = [];
    }

    /*
     |  INIT CODEBLU ENVIRONMENT
     |  @since  1.0.0
     */
    if(!function_exists("codeblu_init")) {
        function codeblu_init() {

        }
    }

    /*
     |  RENDER CODEBLU SIDEBAR
     |  @since  1.0.0
     */
    if(!function_exists("codeblu_sidebar")) {
        function codeblu_sidebar() {

        }
    }
