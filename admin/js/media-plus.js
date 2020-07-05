/*
 |  Media       The advanced Media & File Manager for Bludit
 |  @file       ./admin/js/media-plus.js
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.2.0 [0.1.0] - Beta
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */

;(function($, Media) {
    "use strict";

    /*
     |  API :: LIST NEW SEARCH VIEW
     |  @since  0.2.0
     |
     |  @param  object  The FormData object of the respective modal.
     |
     |  @return multi   Returns False directly or a Promise object instance.
     */
    Media.prototype.search = function(form) {
        if(!Media.admin || this.loading) {
            return false;
        }

        // Check Arguments
        if(!(form instanceof FormData)) {
            return false;
        }
        if(!form.has("search")) {
            return false;
        }

        // Prepare Query
        form.set("action", "search");
        if(!form.has("path")) {
            form.set("path", this.path);
        }

        // Call
        return this.handle(form, (data) => {
            this.container.innerHTML = data.content;
            this.setPath(form.get("path"));
            return true;
        });
    },

    /*
     |  API :: EDIT AN ITEM
     |  @since  0.2.0
     |
     |  @param  multi   The path as STRING which should list should be requested,
     |                  An element containing the path within "href" or as "data-media-path" attribute.
     |
     |  @return multi   Returns False directly or a Promise object instance.
     */
    Media.prototype.edit = function(form) {
        if(!Media.admin || this.loading) {
            return false;
        }

        // Check Arguments
        if(!(form instanceof FormData)) {
            return false;
        }
        if(!form.has("content") || form.get("content").trim().length <= 0) {
            return false;
        }

        // Prepare Query
        form.set("action", "edit");
        if(!form.has("path")) {
            form.set("path", this.path);
        }

        // Call
        return this.handle(form, (data) => {
            this.force = true;
            return this.list(data.path);
        });
    };

    /*
     |  API :: [UN] FAVORITE AN ITEM
     |  @since  0.2.0
     |
     |  @param  multi   The path as STRING which should list should be requested,
     |                  An element containing the path within "href" or as "data-media-path" attribute.
     |
     |  @return multi   Returns False directly or a Promise object instance.
     */
    Media.prototype.favorite = function(path) {
        if(!Media.admin || this.loading) {
            return false;
        }
        var self = path;

        // Check Argument
        if(path instanceof Element) {
            path = path.getAttribute("data-media-path") || this.getParam(path.href || window.location.href, "path", this.path);
        }
        if(typeof path !== "string") {
            return false;
        }

        // Prepare Query
        let form = new FormData();
        form.append("action", "favorite");
        form.append("path", path);

        // Call
        return this.handle(form, (data) => {
            if(self instanceof Element && self.querySelector(".fa")) {
                self.classList.toggle("active");
                self.querySelector(".fa").classList.toggle("fa-heart");
                self.querySelector(".fa").classList.toggle("fa-heart-o");
                self.blur();
            }
            return true;
        });
    };
}).call(window, jQuery, window.Media);
