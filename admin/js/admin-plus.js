/*
 |  Media       The advanced Media & File Manager for Bludit
 |  @file       ./admin/js/admin-plus.js
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.2.1 - Beta
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
;(function($) {
    /*
     |  ACTION :: SUBMIT EDIT FORM [DETAILS]
     |  @since  0.2.0
     */
    Media.prototype.edited = function(element) {
        if(document.querySelector(`[data-media-form="edit"]`)) {
            let ev = new CustomEvent("submit", { "bubbles":true, "cancelable": true });
            document.querySelector(`[data-media-form="edit"]`).dispatchEvent(ev);
            return true;
        }
        return false;
    };

    /*
     |  DETAILS :: INIT CODEMIRROR EDITOR
     |  @since  0.2.0
     */
    function mediaInitCodeMirror() {
        if(!document.querySelector("#media-plus-file-editor")) {
            return false;
        }
        let textarea = document.querySelector("#media-plus-file-editor");
        var myCodeMirror = CodeMirror.fromTextArea(textarea, {
            mode: textarea.getAttribute("data-type"),
            theme: "neo",
            lineNumbers: true,
        });
    }

    /*
     |  GENERAL :: PLUS FEATURE :: FAVORITE DROPDOWN
     |  @since  0.1.0
     */
    $(document).on("click", ".media-favorites-dropdown", function(event) {
        if(event.target.hasAttribute("data-toggle")) {
            event.stopPropagation();
        }
    });

    /*
     |  READY
     */
    document.addEventListener("DOMContentLoaded", function() {
        if(Media.instance === null) {
            return;
        }
        Media.instance.callbacks.push(function() {
            setTimeout(function() {
                mediaInitCodeMirror();
            }, 500);
        });
        mediaInitCodeMirror();
    });
}).call(this, jQuery);
