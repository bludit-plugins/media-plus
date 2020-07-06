/*
 |  Media       The advanced Media & File Manager for Bludit
 |  @file       ./admin/js/admin.js
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.2.0 [0.1.0] - Beta
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
;(function($){
    "use strict";

    /*
     |  HELPER :: CREATE STATUS
     |  @since  0.1.0
     |
     |  @param  string  The type of this toast:
     |                      'status'    Show a status message.
     |                      'upload'    Show a upload message from dropzone.
     |  @param  string  The status for this post.
     |                      'success'   Something good has happened.
     |                      'danger'    Something bad has happened.
     |                      'info'      Something informative has happened.
     |  @param  string  The title for this toast.
     |  @param  multi   The content for this toast. (A HTML string or a respective element).
     |  @param  object  The optional toast configuration object.
     |
     |  @return object  The jQuery instance of the toast element.
     */
    function createStatus(type, status, title, content, config) {
        let toast = $("<div></div>", {
            'class': `media-toast media-toast-${type} toast mt-3 mr-3 mb-1 ml-auto`,
        });
        toast.append($("<div></div>", {
            'class': "toast-header bg-white border-bottom-0",
            html: `<span class="toast-status d-inline-block rounded mr-2 bg-${status}"></span>`
                + `<strong class="mr-auto">Media Manager / ${title}</strong>`
                + `<button type="button" class="ml-2 mb-1 close" data-dismiss="toast">`
                + `    <span aria-hidden="true">&times;</span>`
                + `</button>`
        }), $("<div></div>", {
            'class': "toast-body bg-white",
        })[(typeof content === "string")? "html": "append"](content));

        // Check if the main toasts container exists.
        let toasts = $(".media-toasts");
        if(toasts.length === 0) {
            toasts = $("<div></div>", {
                'class': "media-toasts toasts position-fixed d-flex flex-column",
            }).appendTo(document.body);
        }

        // Default Configuration
        if(typeof config !== "object") {
            config = (type === "status")? { "autohide": true, "delay": 2500 }: { "autohide": false };
        }

        // Append & Call
        toasts.append(toast);
        toast.toast(config).toast("show").on("hidden.bs.toast", function() {
            this.parentElement.removeChild(this);
        });
        return toast;
    }

    /*
     |  HELPER :: WRITE CONTENT TO EDITOR
     |  @since  0.1.0
     |
     |  @param  string  The mime type of the content.
     |  @param  object  The required attributes for the new element.
     |
     |  @return bool    TRUE if everything is fluffy, FALSE if not.
     */
    function insertContent(mime, args) {
        let isEmpty = function(key, data) {
            if(typeof data[key] === "undefined") {
                return true;
            }
            return data[key].trim().length <= 0;
        };
        let render = {
            link: function(markup, args) {
                let content = `<a href="${args.source}" title="${args.title}">${args.title}</a>`;
                switch(markup) {
                    case "markdown":
                        content = `[${args.title}](${args.source})`; break;
                    case "textile":
                        content = `"${args.title}":${args.source}`; break;
                    case "bbcode":
                        content = `[url=${args.source}]${args.title}[/url]`; break;
                }
                return content;
            },
            image: function(markup, args) {
                let alt = isEmpty("alt", args)? args.title: args.alt;
                let width = isEmpty("width", args)? null: args.width;
                let height = isEmpty("height", args)? null: args.height;
                let position = isEmpty("position", args)? null: args.position;

                if(markup === "markdown" && (width || height || position)) {
                    markup = "html";
                }
                switch(markup) {
                    case "markdown":
                        var content = `![${alt}](${args.source})`; break;
                    case "textile":
                        if(position === "left") {
                            position = "<";
                        } else if(position === "right") {
                            position = ">";
                        } else if(position === "center") {
                            position = "=";
                        }
                        var style = "";
                        if(width) {
                            style += `width:${width};`;
                        }
                        if(height) {
                            style += `height:${height};`;
                        }
                        var content = `!${position}{${style}}${args.source}(${alt})!`; break;
                    case "bbcode":
                        var attrs = [];
                        if(position) { attrs.push(`align="${position}"`); }
                        if(width) { attrs.push(`width="${width}"`); }
                        if(height) { attrs.push(`height="${height}"`); }
                        if(attrs.length > 0) {
                            attrs = " " + attrs.join(" ");
                        } else {
                            attrs = "";
                        }

                        var content = `[img${attrs}]${args.source}[/img]`; break;
                    default:
                        var style = "width:auto;height:auto;";
                        if(position === "left") {
                            style += "float:left;";
                        }
                        if(position === "right") {
                            style="float:right;";
                        }
                        if(position === "center") {
                            style="margin-left:auto;margin-right:auto;display:block;"
                        }
                        if(width) {
                            style += `width:${width};`;
                        }
                        if(height) {
                            style += `height:${height};`;
                        }
                        var content = `<img src="${args.source}" alt="${alt}" style="${style}" />`; break;
                }
                return content;
            },
            player: function(tag, markup, args) {
                let alt = isEmpty("alt", args)? args.title: args.alt;
                let width = isEmpty("width", args)? null: args.width;
                let height = isEmpty("height", args)? null: args.height;

                let attrs = ["controls"];
                if(!isEmpty("autoplay", args) && args.autoplay === "1") {
                    attrs.push("autoplay");
                }
                if(!isEmpty("loop", args) && args.loop === "1") {
                    attrs.push("loop");
                }
                if(!isEmpty("muted", args) && args.muted === "1") {
                    attrs.push("muted");
                }
                attrs = " " + attrs.join(" ");

                var style = "";
                if(width) {
                    style += `width:${width};`;
                }
                if(height) {
                    style += `height:${height};`;
                }

                let content = `<${tag}${attrs} style="${style}"><source src="${args.source}" type="${mime}" /></${tag}>`;
                switch(markup) {
                    case "textile":
                        content = "notextile.. " + content; break;
                }
                return content;
            },
            audio: function(markup, args) {
                return render.player("audio", markup, args);
            },
            video: function(markup, args) {
                return render.player("video", markup, args);
            },
            pdf: function(markup, args) {
                let width = isEmpty("width", args)? null: args.width;
                let height = isEmpty("height", args)? null: args.height;

                var style = `width:${width || "100%"};height:${height || "400px"};`;
                let content = `<object data="${args.source}" type="application/pdf" style="${style}">${Media.strings["js-pdf-unsupport"]} ${render.link("html", {source: args.source, title: Media.strings["js-click-here"]})}</object>`;
                switch(markup) {
                    case "textile":
                        content = "notextile.. " + content; break;
                }
                return content;
            }
        };

        // Prepare Type
        let type = mime.substr(0, mime.indexOf("/"));
        if(mime === "application/pdf") {
            type = "pdf";
        }
        if(typeof render[type] === "undefined") {
            type = "link";
        }

        // Prepare Args
        if(!isEmpty("width", args) && /^\d+$/.test(args.width)) {
            args.width += "px";
        }
        if(!isEmpty("height", args) && /^\d+$/.test(args.height)) {
            args.height += "px";
        }

        // TinyMCE Editor
        if(typeof tinymce !== "undefined") {
            tinymce.activeEditor.insertContent(render[type]("html", args) + "&nbsp;");
            return true;
        }

        // EasyMDE Editor
        if(typeof easymde !== "undefined") {
            let text = easymde.value();
            easymde.value(text + render[type]("markdown", args) + (type === "link"? " ": "\n"));
            easymde.codemirror.refresh();
            return true;
        }

        // tail.writer Editor
        if(typeof tail !== "undefined" && typeof WriterEditor !== "undefined") {
            WriterEditor.writeContent(render[type](WriterEditor.config("markup"), args) + (type === "link"? " ": "\n"));
            return true;
        }

        // CKEDITOR Editor
        if(typeof CKEDITOR !== "undefined" && CKEDITOR.version.substr(0, 1) === "4") {
            let content = render[type]("html", args).replace("object", "iframe").replace("data", "src");
            CKEDITOR.instances.jseditor.insertHtml(content + (type === "link"? "&nbsp;": "<br />"), "unfiltered_html");
            return true;
        }

        // Return
        $("#jseditor").val($('#jseditor').val() + render[type]("html", args) + (type === "link"? " ": "<br />"));
        return true;
    }

    /*
     |  READY
     */
    document.addEventListener("DOMContentLoaded", function() {
        if(document.querySelector(".media-container") === null) {
            return;
        }
        const MediaHandler = new Media(document.querySelector(".media-container"));

        /*
         |  ACTION :: EMBED CONTENT
         |  @since  0.2.0
         */
        Media.prototype.embed = function(element) {
            if(element instanceof Element) {
                let type = element.getAttribute("data-media-mime");
                let title = element.getAttribute("data-media-name");
                let source = element.href.substr(0, element.href.indexOf("?"));

                insertContent(type, {title: title, source: source});
                $("#media-manager-modal").modal("hide");
            } else if(element instanceof FormData) {
                let type = element.get("mime");
                let args = { };
                for(let data of element.entries()) {
                    args[data[0]] = data[1];
                }

                insertContent(type, args);
                $("#media-embed-file.media-modal").modal("hide");
            }
            return true;
        };

        /*
        |  ACTION :: SET AS COVER IMAGE
        |  @since  0.2.0
        */
       Media.prototype.cover = function(element) {
            $("#jscoverImage").val(element.href.substr(0, element.href.indexOf("?")));
            $("#jscoverImagePreview").attr("src", element.href.substr(0, element.href.indexOf("?")));
            $("#media-manager-modal").modal("hide");
            return true;
        };

        /*
         |  ACTION :: TOGGLE RENAME FORM [DETAILS]
         |  @since  0.2.0
         */
        Media.prototype.renamed = function(element) {
            let form = $('form[data-media-form="rename"]');
            let input = $('input[name="newname"]');

            if(input.prop("readonly")) {
                input.prop("readonly", false).focus();
                input.get(0).setSelectionRange(input.val().lastIndexOf("."), input.val().lastIndexOf("."));

                $(form).find('button[type="submit"],button[type="cancel"]').removeClass("d-none");
                $(form).find('button[type="button"]').addClass("d-none");
            } else {
                input.prop("readonly", true).blur();

                $(form).find('button[type="submit"],button[type="cancel"]').addClass("d-none");
                $(form).find('button[type="button"]').removeClass("d-none");
            }
            return true;
        };

        /*
         |  ACTION :: TOGGLE WIDGET SIZE [DETAILS]
         |  @since  0.2.0
         */
        Media.prototype.resize = function(element) {
            let cols = $(".media-list-details > .row > div");
            if(cols.length === 2) {
                if(cols.first().hasClass("col-8")) {
                    cols.first().removeClass("col-8").addClass("col-12");
                    cols.last().removeClass("col-4 pl-5").addClass("col-12 row px-5 py-5 justify-content-around align-items-start");
                    element.innerHTML = `<svg class="media-icon"><use href="#octicon-screen-normal" /></svg>`;
                } else {
                    cols.first().addClass("col-8").removeClass("col-12");
                    cols.last().addClass("col-4 pl-5").removeClass("col-12 row px-5 py-5 justify-content-around align-items-start");
                    element.innerHTML = `<svg class="media-icon"><use href="#octicon-screen-full" /></svg>`;
                }
                return true;
            }
            return false;
        };

        /*
         |  GENERAL :: REPLACE CORE MODAL WITH MEDIA MODAL
         |  @since  0.1.0
         */
        function mediaReplaceModal() {
            if($("#jsmediaManagerOpenModal").length === 0 || $("#media-manager-modal").length === 0) {
                return;
            }
            let modal = $("#media-manager-modal");
            let button = $("#jsmediaManagerOpenModal");

            // Good-Bye Core Modal
            $(window).off("dragover dragenter");
            $(window).off("drop");
            $('#jsmediaManagerModal').on('shown.bs.modal', function() {
                $('#jsmediaManagerModal').modal('dispose');
            });

            // Hellow Media Modal
            button.html(`<span class="fa fa-image"></span>` + Media.strings["js-media-title"]);
            button.attr("data-target", "#media-manager-modal");

            // Click on Modals
            $(".media-modal").on("show.bs.modal", function() {
                MediaHandler.loader(true, true);
                modal.css("opacity", 0.5);
            }).on("hidden.bs.modal", function() {
                modal.css("opacity", 1.0);
                MediaHandler.loader(false, true);
            });

            // Hide Modal & Prevent Loader on Error
            modal.on("hidden.bs.modal", function() {
                modal.css("opacity", 1.0);
                MediaHandler.loader(false, true);
            });
        }
        mediaReplaceModal();

        /*
         |  INIT :: INIT DROPZONE SCRIPT
         |  @since  0.1.0
         |  @author Matias Meno <m@tias.me>
         |  @source https://www.dropzonejs.com
         */
        function mediaInitDropzone() {
            if(Media.dropzone instanceof Dropzone) {
                Media.dropzone.destroy();
            }
            if(!MediaHandler.container.children[0] || !MediaHandler.container.children[0].classList.contains("media-list-upload")) {
                return;
            }

            // Add Dropzone Script
            Media.dropzone = new Dropzone(MediaHandler.container.children[0], {
                url: Media.ajax + "upload",
                paramName: "media",
                maxFilesize: 1050,
                previewsContainer: null,
                clickable: ".media-trigger-upload"
            });

            // Append some Data
            Media.dropzone.on("sending", function(file, xhr, formData) {
                MediaHandler.loader(true);
                formData.append("action", "upload");
                formData.append("path", MediaHandler.path);
                formData.append("temp", MediaHandler.temp? "true": "false");
                formData.append("token", MediaHandler.token);
                formData.append("tokenCSRF", MediaHandler.token);
            });

            // Handle File Toasts
            Media.dropzone.on("addedfile", function(file) {
                createStatus("upload", "warning", Media.strings["js-form-upload"], file.previewTemplate);
            });

            // Handle File Errors
            Media.dropzone.on("error", function(file, errorMessage, xhr) {
                if(errorMessage.trim().indexOf("{") >= 0) {
                    try {
                        var data = JSON.parse(errorMessage);
                    } catch (e) {
                        var data = { status: "error", message: Media.strings["js-error-text"] };
                    }
                } else {
                    var data = { message: errorMessage };
                }

                if("data" in data && typeof data.errors !== "undefined") {
                    data.message += "\n" + data.errors.join("\n");
                }
                file.previewTemplate.querySelector("[data-dz-errormessage]").innerText = data.message;
            });

            // Complete AJAX Request
            Media.dropzone.on("complete", function(file) {

                // Keep File Preview
                file.previewTemplate.parentElement.replaceChild(file.previewTemplate.cloneNode(true), file.previewTemplate);
                this.destroy();

                // Update List
                MediaHandler.force = true;
                MediaHandler.reload().then(
                    () => { mediaInit(); },
                    () => { mediaInit(); }
                );
            });
        }

        /*
         |  INIT :: INIT GOBACK
         |  @since  0.2.0
         */
        function mediaInitGoBack() {
            let goback = document.querySelector(".action-goback");
            if(!goback) {
                return;
            }

            // Show Go Back
            let path = decodeURIComponent(MediaHandler.path).replace(/(^\/|\/$)/g, "");
            if(path.length > 0) {
                let link = goback.querySelector("a");
                if(!link) {
                    link = document.createElement("a");
                    link.className = "btn btn-success";
                    link.setAttribute("data-media-action", "list");
                    link.innerHTML = `<span class="fa fa-arrow-left"></span> ${Media.strings["js-button-goback"]}`;
                }
                if(path.lastIndexOf("/") > 0) {
                    link.href = `${Media.ajax}?path=${path.substr(0, path.lastIndexOf("/"))}`;
                } else {
                    link.href = `${Media.ajax}?path=/`;
                }
                goback.appendChild(link);
            }

            // Hide Go Back
            if(path.length === 0 && goback.querySelector("a")) {
                goback.removeChild(goback.querySelector("a"));
            }
        }

        /*
         |  INIT :: INIT INTERACTIONs
         |  @since  0.2.0
         */
        function mediaInitInteractions() {
            let actions = document.querySelector(".action-handle");
            if(!actions) {
                return;
            }

            // Show Interactions
            if(MediaHandler.view !== "list") {
                for(let i = 0; i < actions.children.length; i++) {
                    actions.children[i].style.display = "none";
                }
            } else {
                for(let i = 0; i < actions.children.length; i++) {
                    actions.children[i].style.removeProperty("display");
                }
            }

            // Change Layout Buttons
            if(actions.querySelector('[data-media-layout]')) {
                if(MediaHandler.container.children[0].tagName.toUpperCase() === "TABLE") {
                    actions.querySelector('[data-media-layout="grid"]').classList.remove("active");
                    actions.querySelector('[data-media-layout="table"]').classList.add("active");
                } else {
                    actions.querySelector('[data-media-layout="grid"]').classList.add("active");
                    actions.querySelector('[data-media-layout="table"]').classList.remove("active");
                }
            }
        }

        /*
         |  INIT :: INIT BREADCRUMBs
         |  @since  0.2.0
         */
        function mediaInitBreadcrumbs() {
            let breadcrumb = document.querySelector(".breadcrumb");
            if(!breadcrumb) {
                return;
            }

            // Get Root
            let root = breadcrumb.querySelector("li:first-child").cloneNode(true);
            root.classList.remove("active");
            breadcrumb.innerHTML = "";
            breadcrumb.appendChild(root);

            // Loop Items
            let parent = "";
            let crumbs = decodeURIComponent(MediaHandler.path).replace(/(^\/|\/$)/g, "").split("/").map((slug) => {
                if(slug.length === 0) {
                    return null;
                }

                let item = document.createElement("LI");
                item.className = "breadcrumb-item";
                item.innerHTML = `<a href="${Media.ajax}?path=${parent}${slug}" data-media-action="list">${slug}</a>`;

                parent += slug + "/";
                breadcrumb.appendChild(item);
                return item;
            });
            if(crumbs[0] === null) {
                crumbs[0] = root;
            }
            crumbs[crumbs.length - 1].classList.add("active");
        }

        /*
         |  INIT :: MEDIA DETAILS INIT
         |  @since  0.2.0
         */
        function mediaInitDetails() {
            var items = $(".media-preview-video video,.media-preview-audio audio");
            if(items.length === 0) {
                return;
            }

            let meta = function(event) {
                let tag = this.tagName.toLowerCase();

                // Duration
                let min = ("0" + Math.floor(this.duration / 60)).toString().slice(-2);
                let sec = ("0" + Math.round(this.duration % 60)).toString().slice(-2);
                $(`[data-media-${tag}="duration"]`).text(`${min}:${sec}`);

                // Dimenstion
                if(this.videoWidth && this.videoHeight) {
                    $(`[data-media-${tag}="dimension"]`).text(`${this.videoWidth}x${this.videoHeight}`);
                }
            };
            items.on("loadedmetadata", meta);
            items.each(function(){ meta.call(this); });
        }

        /*
         |  GENERAL :: INIT MEDIA PAGE
         |  @since  0.2.0
         */
        function mediaInit() {
            $('[data-toggle="popover"]').popover();
            bsCustomFileInput.init();

            mediaInitDropzone();            // Attach Dropzone Instance
            mediaInitGoBack();              // Show | Hide GoBack Buttons
            mediaInitInteractions();        // Show  Hide Interaction Buttons
            mediaInitBreadcrumbs();         // Update Breadcrumb List
            mediaInitDetails();             // Update Details Meta Data
        }
        mediaInit();

        /*
         |  HANDLE :: HANDLE MEDIA ACTION BUTTONs
         |  @since  0.2.0
         */
        function mediaHandleActions() {
            $(document).on("click", "[data-media-action]", function(event) {
                let action = this.getAttribute("data-media-action");

                // Check Action
                if(typeof MediaHandler[action] === "undefined") {
                    return;
                }

                // Call Action
                let promise = MediaHandler[action](this);
                if(promise === false) {
                    return;
                }
                if(promise === true) {
                    event.preventDefault();
                    return;
                }

                // Handle Action
                event.preventDefault();
                promise.then((msg) => {
                    createStatus("status", "success", action, msg);

                    // Update Media Elements
                    mediaInit();
                }, (msg) => {
                    createStatus("status", "danger", action, msg);
                });
            });
        }
        mediaHandleActions();

        /*
         |  HANDLE :: HANDLE MEDIA FORMS
         |  @since  0.1.0
         |
         |  @param  object  All available modal forms.
         |
         |  @return void
         */
        function mediaHandleForms(forms) {
            if(!Media.admin) {
                return;
            }

            $(document).on("submit", "form[data-media-form]", function(event) {
                let action = this.getAttribute("data-media-form");

                // Check Action
                if(typeof MediaHandler[action] === "undefined") {
                    return;
                }

                // Call Action
                let promise = MediaHandler[action](new FormData(this));
                if(promise === false) {
                    return;
                }
                if(promise === true) {
                    event.preventDefault();
                    return;
                }

                // Handle Action
                event.preventDefault();
                promise.then((msg) => {
                    createStatus("status", "success", action, msg);

                    // Hide Modal
                    let modal = this;
                    do {
                        modal = modal.parentElement;
                    } while(modal.parentElement && !modal.parentElement.classList.contains("media-modal"));
                    if(modal && modal.parentElement) {
                        $(modal.parentElement).modal("hide");
                    }

                    // Update Media Elements
                    mediaInit();
                }, (msg) => {
                    createStatus("status", "danger", action, msg);
                });
            })
        }
        mediaHandleForms();

        /*
         |  HANDLE :: HANDLE MEDIA MODALs
         |  @since  0.2.0
         */
        function mediaHandleModals(items) {
            items.on("show.bs.modal", function(event) {
                var path = event.relatedTarget.getAttribute("data-media-path") || MediaHandler.path;

                $(this).find('input[name="path"]').val(path);
                $(this).find('[data-media-value]').each(function() {
                    this.value = $(event.relatedTarget).attr("data-media-" + this.getAttribute("data-media-value"));
                });
                $(this).find('[data-media-check]').each(function() {
                    if(this.value === path) {
                        this.parentElement.style.display = "none";
                        return;
                    }
                    var slug = path.substr(0, path.lastIndexOf("/"));
                    this.checked = slug === this.value;
                });
            });

            items.on("shown.bs.modal", function(event) {
                let selector = 'input:not([type="button"]):not([type="checkbox"]):not([type="radio"]):not([type="hidden"])';
                $(this).find(selector).first().focus();
            });

            items.on("hidden.bs.modal", function(event) {
                let selector = 'input:not([type="button"]):not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),textarea';
                $(this).find(selector).val("");
                $(this).find('input[type="checkbox"]').prop("checked", false);
                $(this).find('select option:not([disabled])').first().attr("selected", "selected");
                $(this).find('.list-item:hidden').show();
            });

            // Handle Embed Modal
            $("#media-embed-file.media-modal").on("show.bs.modal", function(event) {
                let type = event.relatedTarget.getAttribute("data-media-type") || "link";

                switch(type) {
                    case "image":
                        var filter = `[data-embed-field="width"],[data-embed-field="height"],[data-embed-field="position"],[data-embed-field="alt"]`;
                        var width = "100%";
                        var height = "100%";
                        break;
                    case "audio":   //@pass
                    case "video":
                        var filter = `[data-embed-field="width"],[data-embed-field="height"],[data-embed-field="autoplay"],[data-embed-field="loop"],[data-embed-field="muted"]`;
                        var width = "100%";
                        var height = "400px";
                        break;
                    case "pdf":
                        var filter = `[data-embed-field="width"],[data-embed-field="height"]`;
                        var width = "100%";
                        var height = "400px";
                        break;
                    default:
                        var filter = `[data-embed-field="title"]`;
                        break;
                }
                $(this).find("[data-embed-field]").not(filter).hide();
                $(this).find("[data-embed-field]").filter(filter).show();

                if(typeof width !== "undefined") {
                    $(this).find('[data-embed-field="width"]').val(width);
                }
                if(typeof height !== "undefined") {
                    $(this).find('[data-embed-field="height"]').val(height);
                }
            });
            $("#media-embed-file.media-modal").on("shown.bs.modal", function(event) {
                let selector = '.form-group:visible input:not([type="button"]):not([type="checkbox"]):not([type="radio"]):not([type="hidden"])';
                $(this).find(selector).first().focus();
            });
        }
        mediaHandleModals($(".media-modal"));
    });
}).call(this, jQuery);
