/*
 |  Media       The advanced Media & File Manager for Bludit
 |  @file       ./admin/js/media.js
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.2.0 [0.1.0] - Beta
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
;(function() {
    "use strict";

    /*
     |  MEDIA CONSTRUCTOR
     |  @since  0.2.0
     |
     |  @param  object  The main media-list Element instance.
     */
    let Media = function(container) {
        if(!(this instanceof Media)) {
            return new Media(container);
        }

        // Main List Container
        if(!(container.hasAttribute("data-path") && container.hasAttribute("data-token"))) {
            throw "The passed container element is invalid.";
        }
        this.container = container;
        this.path = container.getAttribute("data-path");
        this.token = container.getAttribute("data-token");

        // Prepare View
        let list = container.querySelector(".media-list");
        if(list.classList.contains("media-details-page")) {
            this.view = "details";
        } else if(list.classList.contains("media-list-search")) {
            this.view = "search";
        } else {
            this.view = "list";
        }

        // Return Instance
        Media.instance = this;
        return this;
    };

    /*
     |  STATIC :: AJAX IS ENABLED
     */
    Media.admin = false;

    /*
     |  STATIC :: THE MEDIA INSTANCE
     */
    Media.instance = null;

    /*
     |  STATIC :: THE MEDIA DROPZONE INSTANCE
     */
    Media.dropzone = null;

    /*
     |  MEDIA METHODs
     */
    Media.prototype = {
        /*
         |  THE CURRENT PATH
         */
        path: "",

        /*
         |  THIS IS A TEMPORARY PATH VIEW
         */
        temp: false,

        /*
         |  THE CURRENT VIEW (list, search or details)
         */
        view: "list",

        /*
         |  LOADING DUE TO AN APPLIED ACTION
         */
        loading: false,

        /*
         |  FORCE REQUEST EVEN IF LOADING IS TRUE
         */
        force: false,

        /*
         |  KEEP LOADING ANIMATION ON CHAINED REQUESTs
         */
        keep: false,

        /*
         |  CALLBACKs
         */
        callbacks: [],

        /*
         |  HELPER :: AJAX
         |  @since  0.2.0
         |
         |  @param  object  The FormData instance for this request.
         |
         |  @return object  The Promise instance.
         */
        ajax: function(formData) {
            return new Promise((resolve, reject) => {
                if(!(formData instanceof FormData)) {
                    reject({ status: error, message: Media.strings["js-error-text"] });
                }
                if(formData.get("action") === null) {
                    reject({ status: error, message: Media.strings["js-error-text"] });
                }
                formData.append("token", this.token);           // Custom Token Check
                formData.append("tokenCSRF", this.token);       // Bludit Admin Check

                // Create XHRE
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if(this.readyState === 4) {
                        if(status < 400) {
                            resolve(this);
                        } else {
                            reject(this);
                        }
                    }
                }

                // Send XHR
                xhr.open("POST", Media.ajax + formData.get("action"), true);
                xhr.setRequestHeader("Cache-Control", "no-cache");
                xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                xhr.send(formData);
            });
        },

        /*
         |  HELPER :: REQUEST HANDLER
         |  @since  0.2.0
         |
         |  @param  object  The FormData instance to pass to the .ajax function.
         |  @param  callb.  The callback function for success requests.
         |
         |  @return object  The Promise instance.
         */
        handle: function(formData, callback) {
            this.loader(true);
            return new Promise((resolve, reject) => {
                this.ajax(formData).then(
                    // Success
                    (xhr) => {
                        try {
                            var data = JSON.parse(xhr.responseText);
                        } catch (e) {
                            var data = { status: "error", message: Media.strings["js-error-text"] };
                        }
                        if(data.status === "error") {
                            reject(data.message);
                        }

                        // Handle
                        try {
                            var status = callback.call(xhr, data);
                        } catch(e) {
                            var status = false;
                        }
                        if(status instanceof Promise) {
                            this.keep = true;
                            status.then(
                                () => { resolve(data.message) },
                                () => { resolve(Media.strings["js-error-text"]) }
                            );
                        } else {
                            if(!status) {
                                reject(Media.strings["js-error-text"]);
                            }
                            for(let i = 0; i < this.callbacks.length; i++) {
                                this.callbacks[i].call(this, data)
                            }
                            resolve(data.message);
                        }
                    },

                    // Error
                    (xhr) => {
                        try {
                            var data = JSON.parse(xhr.responseText);
                        } catch (e) {
                            var data = { status: "error", message: Media.strings["js-error-text"] };
                        }
                        reject(data.message);
                    }
                ).finally(() => {
                    if(!this.keep) {
                        this.loader(false);
                    }
                    this.keep = false;
                });
            });
        },

        /*
         |  HELPER :: LOADER
         |  @since  0.2.0
         |
         |  @param  bool    TRUE to set the loader, FALSE to unset it.
         |  @param  bool    TRUE to start loader on modal, FALSE to do it not.
         |
         |  @return void
         */
        loader: function(status, modal) {
            let loader = this.container.querySelector(".media-loader");

            // Add Loader
            if(status) {
                this.loading = (typeof modal !== "undefined" && modal === true)? this.loading: true;

                // Append Loader
                if(loader === null) {
                    loader = document.createElement("DIV");
                    loader.className = 'media-loader d-flex justify-content-center align-items-center';
                    loader.innerHTML = '<div class="media-logo animated"><span></span><span></span><span></span><span></span></div>';

                    this.container.appendChild(loader);
                    setTimeout(() => { loader.classList.add("active"); }, 10);
                }

                // Start Logo Animation
                if(document.querySelector('.media-logo-container .media-logo:not(.animated)')) {
                    document.querySelector('.media-logo-container .media-logo').classList.add("animated");
                }
            }

            // Delete Loader
            if(!status) {
                this.loading = (typeof modal !== "undefined" && modal === true)? this.loading: false;

                // Remove Loader
                if(loader !== null) {
                    loader.parentElement.removeChild(loader);
                }

                // Stop Logo Animation
                if(document.querySelector('.media-logo-container .media-logo.animated')) {
                    document.querySelector('.media-logo-container .media-logo.animated').classList.remove("animated");
                }
            }
        },

        /*
         |  HELPER :: SET PATH
         |  @since  0.2.0
         |
         |  @param  string  The new path.
         |
         |  @return void
         */
        setPath: function(path) {
            this.path = path;
            this.container.setAttribute("data-path", path);

            // Prepare View
            let list = this.container.querySelector(".media-list");
            if(list.classList.contains("media-list-details")) {
                this.view = "details";
            } else if(list.classList.contains("media-list-search")) {
                this.view = "search";
            } else {
                this.view = "list";
            }
        },

        /*
         |  HELPER :: GET URL PARAMETER
         |  @since  0.2.0
         |
         |  @param  string  The URL to fetch the parameters.
         |  @param  string  The parameter name to fetch the value.
         |  @param  any     The default value if the parameter does not exist.
         |
         |  @return multi  The default value or the parameter value.
         */
        getParam: function(url, name, defvalue) {
            let results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(url);
            if(results === null) {
                return defvalue;
            }
            return decodeURI(results[1]) || 0;
        },

        /*
         |  API :: CREATE A NEW ITEM
         |  @since  0.2.0
         |
         |  @param  object  The FormData object of the respective modal.
         |
         |  @return multi   Returns False directly or a Promise object instance.
         */
        create: function(form) {
            if(!Media.admin || this.loading) {
                return false;
            }

            // Check Arguments
            if(!(form instanceof FormData)) {
                return false;
            }
            if(!form.has("type") || ["folder", "file"].indexOf(form.get("type")) < 0) {
                return false;
            }
            if(!form.has("item") || form.get("item").trim().length <= 0) {
                return false;
            }

            // Prepare Query
            form.set("action", "create");
            if(!form.has("path")) {
                form.set("path", this.path);
            }

            // Call
            return this.handle(form, (data) => {
                this.force = true;
                return this.reload();
            });
        },

        /*
         |  API :: MOVE AN ITEM
         |  @since  0.2.0
         |
         |  @param  object  The FormData object of the respective modal.
         |
         |  @return multi   Returns False directly or a Promise object instance.
         */
        move: function(form) {
            if(!Media.admin || this.loading) {
                return false;
            }

            // Check Arguments
            if(!(form instanceof FormData)) {
                return false;
            }
            if(!form.has("newpath") || form.get("newpath").trim().length <= 0) {
                return false;
            }

            // Prepare Query
            form.set("action", "move");
            if(!form.has("path")) {
                form.set("path", this.path);
            }

            // Call
            return this.handle(form, (data) => {
                if(data.status === "success") {
                    this.force = true;
                    return this.list(form.get("newpath"));
                }
                return this.reload();
            });
        },

        /*
         |  API :: RENAME AN ITEM
         |  @since  0.2.0
         |
         |  @param  object  The FormData object of the respective modal.
         |
         |  @return multi   Returns False directly or a Promise object instance.
         */
        rename: function(form) {
            if(!Media.admin || this.loading) {
                return false;
            }

            // Check Arguments
            if(!(form instanceof FormData)) {
                return false;
            }
            if(!form.has("newname") || form.get("newname").trim().length <= 0) {
                return false;
            }

            // Prepare Query
            form.set("action", "rename");
            if(!form.has("path")) {
                form.set("path", this.path);
            }

            // Call
            return this.handle(form, (data) => {
                this.force = true;
                console.log(this.view, data);
                if(this.view === "details") {
                    return this.list(data.path);
                }
                return this.reload();
            });
        },

        /*
         |  API :: DELETE AN ITEM
         |  @since  0.2.0
         |
         |  @param  object  The FormData object of the respective modal.
         |
         |  @return multi   Returns False directly or a Promise object instance.
         */
        delete: function(form) {
            if(!Media.admin || this.loading) {
                return false;
            }

            // Check Arguments
            if(!(form instanceof FormData)) {
                return false;
            }
            form.set("recursive", (form.get("recursive") !== "1")? "0": "1");

            // Prepare Query
            form.set("action", "delete");
            if(!form.has("path")) {
                form.set("path", this.path);
            }

            // Call
            return this.handle(form, (data) => {
                this.force = true;
                return this.reload();
            });
        },

        /*
         |  API :: UPLOAD AN ITEM
         |  @since  0.2.0
         |
         |  @param  object  The FormData object of the respective modal.
         |
         |  @return multi   Returns False directly or a Promise object instance.
         */
        upload: function(form) {
            if(!Media.admin || this.loading) {
                return false;
            }

            // Check Arguments
            if(!(form instanceof FormData)) {
                return false;
            }
            form.set("revision", (form.get("revision") !== "1")? "0": "1");
            form.set("overwrite", (form.get("overwrite") !== "1")? "0": "1");

            // Prepare Query
            form.set("action", "upload");
            if(!form.has("path")) {
                form.set("path", this.path);
            }

            // Call
            return this.handle(form, (data) => {
                this.force = true;
                return this.reload();
            });
        },

        /*
         |  API :: LIST NEW VIEW
         |  @since  0.2.0
         |
         |  @param  multi   The path as STRING which should list should be requested,
         |                  An element containing the path within "href" or as "data-media-path" attribute.
         |  @param  string  The list mode 'list', 'edit' or 'read'.
         |                  This can also be passed as 'data-media-mode' attribute on the path Element.
         |  @param  bool    TRUE to list a temporary page folder, FALSE to do it not.
         |  @param  string  Change the list layout: 'table' or 'grid'.
         |
         |  @return multi   Returns False directly or a Promise object instance.
         */
        list: function(path, mode, temp, layout) {
            if(!Media.admin || (this.loading && !this.force)) {
                return false;
            }
            this.force = false;

            // Check Argument
            if(path instanceof Element) {
                mode = path.getAttribute("data-media-mode") || this.getParam(path.href || window.location.href, "mode", "list");
                temp = path.getAttribute("data-media-temp") || this.getParam(path.href || window.location.href, "temp", this.temp);
                layout = path.getAttribute("data-media-layout") || this.getParam(path.href || window.location.href, "layout", null);
                path = path.getAttribute("data-media-path") || this.getParam(path.href || window.location.href, "path", this.path);
            }
            if(typeof path !== "string") {
                return false;
            }

            // Prepare Query
            let form = new FormData();
            form.append("action", "list");
            form.append("path", path);
            form.append("mode", typeof mode === "undefined"? "list": mode);
            form.append("temp", typeof temp === "undefined"? this.temp: temp);
            if(typeof layout === "undefined" && layout) {
                form.append("layout", layout);
            }

            // Call
            return this.handle(form, (data) => {
                this.container.innerHTML = data.content;
                this.setPath(form.get("path"));
                this.temp = data.temp;
                return true;
            });
        },

        /*
         |  API :: RELOAD CURRENT VIEW
         |  @since  0.2.0
         |
         |  @return multi   Returns False directly or a Promise object instance.
         */
        reload: function(element, layout) {
            if(this.loading && !this.force) {
                return false;
            }
            this.force = false;

            // Check Argument
            if(element instanceof Element) {
                layout = element.getAttribute("data-media-layout") || null;
            }

            // Prepare Query
            let form = new FormData();
            form.append("action", "list");
            form.append("path", this.path);
            form.append("layout", layout);
            if(this.temp) {
                form.append("temp", this.temp);
            }

            // Call
            return this.handle(form, (data) => {
                this.container.innerHTML = data.content;
                this.setPath(form.get("path"));
                this.temp = data.temp;
                return true;
            });
        }
    };

    // Append Media
    window.Media = Media;
}).call(window, jQuery);
