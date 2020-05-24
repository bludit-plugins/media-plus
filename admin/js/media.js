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
;(jQuery(function($) {
    /*
     |  JAVASCRIPT :: INDEX
     |
     |  1. HELPER FUNCTIONs
     |      a)  'createToast(type, status, title, content, config)'
     |      b)  'buildMessage(data)'
     |      c)  'addMediaItems(items)'
     |      d)  'removeMediaItems(items, type)'
     |      e)  'updateMediaList(content)'
     |      f)  'updateBreadcrumbs(path, file)'
     |      g)  'setLoader(status)'
     |      h)  'writerEditorContent(mime, args)'
     |
     |  2. GENERALs
     |      a)  Init bsCustomFileInput script
     |      b)  Init dropzone script
     |      c)  [PLUS] Keep Favorites Dropdown open
     |      d)  Replace Core Content Images Modal
     |
     |  3. MODALs
     |      a)  'mediaFocusModalForm(items)'
     |      b)  'mediaSubmitModalForm(forms)'
     |
     |  4. DETAILs
     |      a)  'mediaDetailsPreview(items)'
     |      b)  'mediaDetailsForm(forms)'
     |      c)  'mediaInitDetailsPage()'
     |      d)  [PLUS] 'mediaCodeMirrorEditor()'
     |      e)  'mediaDetailsResize()'
     |
     |  5. MEDIA ACTIONs
     |      a)  'mediaActionHandler()'
     |      b)  'media.actions.list()'
     |      c)  [PLUS] 'media.actions.favorite()'
     |      d)  'media.actions.embed()'
     |      e)  'media.actions.cover()'
     |      f)  'media.actions.delete()'
     |      g)  'media.actions.external()'
     |      h)  'media.actions.rename()'
     |      i)  'media.actions.resize()'
     */
    "use strict";

    /*
     |  HELPER :: CREATE TOAST
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
    function createToast(type, status, title, content, config) {
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
     |  HELPER :: BUILD ERROR MESSAGE
     |  @since  0.1.0
     |
     |  @param  object  The AJAX response as JSON object.
     |
     |  @return string  The build up error message.
     */
    function buildMessage(data) {
        if(typeof data.message === "undefined") {
            return media.strings["js-unknown"];
        }

        var _return = data.message;
        if(data.data.errors) {
            _return = _return + "<br>" + data.data.errors.join("<br>");
        }
        return _return;
    }

    /*
     |  HELPER :: ADD MEDIA ITEMs
     |  @since  0.1.0
     |
     |  @param  object  The items to add to the media list.
     |
     |  @return bool    TRUE on success, FALSE on failure.
     */
    function addMediaItems(items) {
        let container = $(".media-list");
        if(container.length === 0) {
            createToast("status", "danger", media.strings["js-error-title"], media.strings["js-error-text"]);
            return false;
        }

        // Check Items
        if(typeof items !== "object" || (items.length && items.length === 0)){
            return true;
        }

        // Add Items
        for(let item in items) {
            let element;

            // Create Element dpeending on Layout
            if(container[0].tagName.toUpperCase() === "TABLE") {
                element = $("<table></table>", { html: items[item] }).find("tr");
            } else if(container[0].tagName.toUpperCase() === "DIV") {
                element = $("<div></div>", { html: items[item] }).children();
            }
            let type = element.attr("data-type");

            // Get Item List
            let names = container.find(`[data-type="${type}"]`).map(function() {
                return this.getAttribute("data-name");
            }).get();
            names.push(element.attr("data-name"));
            names.sort();

            // Get Item Position
            let after = null;
            let index = names.indexOf(element.attr("data-name"));
            if(index < (names.length - 1)) {
                after = container.find(`[data-name="${names[index+1]}"]`);
            } else if(type !== "file" && container.find("[data-type='file']").length > 0) {
                after = container.find(`[data-type='file']`).eq(0);
            }

            // Insert Item
            if(after) {
                element.insertBefore(after);
            } else {
                if(container[0].tagName.toUpperCase() === "TABLE") {
                    element.appendTo(container.find("tbody"));
                } else {
                    element.appendTo(container);
                }
            }
        }

        // Hide Empty & Return
        container.find(".media-empty:not(.d-none)").addClass("d-none");
        return true;
    }

    /*
     |  HELPER :: REMOVE MEDIA ITEMs
     |  @since  0.1.0
     |
     |  @param  object  The items to remove from the media list.
     |
     |  @return bool    TRUE on success, FALSE on failure.
     */
    function removeMediaItems(items, type) {
        let container = $(".media-list");
        if(container.length === 0) {
            createToast("status", "danger", media.strings["js-error-title"], media.strings["js-error-text"]);
            return false;
        }

        // Check Items
        if(typeof items !== "object" || (items.length && items.length === 0)){
            return true;
        }

        // Remove Items
        for(let key in items) {
            let name = items[key];
            if(name.lastIndexOf("/") !== false) {
                name = name.substr(name.lastIndexOf("/") + 1);
            }
            container.find(`[data-type="${type}"][data-name="${name}"]`).remove();
        }

        // Show Empty & Return
        if(container.find("[data-type][data-name]").length === 0) {
            container.find(".media-empty:not(.d-none)").removeClass("d-none");
        }
        return true;
    }

    /*
     |  HELPER :: UPDATE MEDIA LIST
     |  @since  0.1.0
     |
     |  @param  string  Reload the whole media list container.s
     |
     |  @return bool    TRUE if everything is fluffy, FALSE if not.
     */
    function updateMediaList(content) {
        let container = $(".media-list");
        if(container.length === 0) {
            createToast("status", "danger", media.strings["js-error-title"], media.strings["js-error-text"]);
            return false;
        }

        // Validate Content
        content = $(content);
        if(!content.hasClass("media-list")) {
            createToast("status", "danger", media.strings["js-error-title"], media.strings["js-error-text"]);
            return false;
        }

        // Replace & Return
        container.replaceWith(content);
        return true;
    }

    /*
     |  HELPER :: UPDATE CREADCRUMBs
     |  @since  0.1.0
     |
     |  @param  string  The new path to add.
     |  @param  string  The file, if the details page is shown.
     |
     |  @return bool    TRUE if everything is fluffy, FALSE if not.
     */
    function updateBreadcrumbs(path, file) {
        let breadcrumbs = $(".breadcrumb");
        let root = breadcrumbs.children(":first-child");
            root.nextAll().remove();

        // Prepare URL
        let url = root.find("a").attr("href") || root.attr("data-url");
        if(url.indexOf("?") > 0) {
            url = url.substr(0, url.indexOf("?"));
        }

        // Loop Items
        let crumbs = [];
        [].map.call(path.trim("/").split("/"), function(item) {
            if(item === "") {
                return;
            }

            crumbs.push(item);
            breadcrumbs.append($("<li></li>", {
                html: `<a href="${url}?path=${crumbs.join("/")}" data-media-action="list">${item}</a>`,
                "class": "breadcrumb-item"
            }));
        });

        // Add File
        if(file) {
            breadcrumbs.append($("<li></li>", {
                text: file,
                "class": "breadcrumb-item active"
            }));
        } else if(breadcrumbs.children().length > 1) {
            let item = breadcrumbs.children(":last-child");
            item.html(item.children("a").html()).addClass("active");
            $(".media-actions .btn-group").css("visibility", "visible");
        }

        // Hide Buttons
        $(".media-actions .btn-group").css("visibility", (file)? "hidden": "visible");
        return true;
    }

    /*
     |  HELPER :: SET LOADER CIRCLE
     |  @since  0.1.0
     |
     |  @param  bool    TRUE to set the loader,
     |                  FALSE to remove it.
     |
     |  @return void
     */
    function setLoader(status) {
        let loader = $(".media-ajax-loader");

        // Set Loader
        if(status && loader.length === 0) {
            $(".media-list").after($("<div></div>", {
                "class": `media-ajax-loader d-flex justify-content-center align-items-center`,
                html: `<div class='spinner-border text-light'></div>`
            }));
        }

        // Delete Loader
        if(!status && loader.length > 0) {
            loader.remove();
        }
    };

    /*
     |  HELPER :: WRITE CONTENT TO EDITOR
     |  @since  0.1.0
     |
     |  @param  string  The mime type of the content.
     |  @param  object  The required attributes for the new element.
     |
     |  @return bool    TRUE if everything is fluffy, FALSE if not.
     */
    function writeEditorContent(mime, args) {
        let render = {
            link: function(markup, args) {
                let content = `<a href="${args.source}">${args.title}</a>`;
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
                let content = `<img src="${args.source}" alt="${args.title}" />`;
                switch(markup) {
                    case "markdown":
                        content = `![${args.title}](${args.source})`; break;
                    case "textile":
                        content = `!${args.source}(${args.title})!`; break;
                    case "bbcode":
                        content = `[img]${args.source}[/img]`; break;
                }
                return content;
            },
            audio: function(markup, args) {
                let content = `<audio controls><source src="${args.source}" type="${mime}" /></audio>`;
                switch(markup) {
                    case "textile":
                        content = "notextile.. " + content; break;
                }
                return content;
            },
            video: function(markup, args) {
                let content = `<video controls><source src="${args.source}" type="${mime}" /></video>`;
                switch(markup) {
                    case "textile":
                        content = "notextile.. " + content; break;
                }
                return content;
            },
            pdf: function(markup, args) {
                let content = `<object width="100%" height="auto" data="${args.source}" type="application/pdf" style="min-height:400px;margin:auto;"></object>`;
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
            CKEDITOR.instances.jseditor.insertHtml(render[type]("html", args) + (type === "link"? "&nbsp;": "<br />"), "unfiltered_html");
            return true;
        }

        // Return
        $("#jseditor").val($('#jseditor').val() + render[type]("html", args) + (type === "link"? " ": "<br />"));
        return true;
    }



    // Get Ready
    $(document).ready(function($) {
        /*
         |  GENERAL :: INIT CUSTOM FILE INPUT 4 BOOTSTRAP
         |  @since  0.1.0
         |  @author Johann-S
         |  @source https://github.com/Johann-S/bs-custom-file-input
         */
        bsCustomFileInput.init();

        /*
         |  GENERAL :: INIT DROPZONE SCRIPT
         |  @since  0.1.0
         |  @author Matias Meno <m@tias.me>
         |  @source https://www.dropzonejs.com
         */
        if($(".media-list-upload").length > 0) {
            let dropzone = new Dropzone($(".media-list-upload")[0], {
                url: $(".media-list-upload .media-list").attr("data-action"),
                paramName: "media",
                previewsContainer: false,
                clickable: ".media-trigger-upload"
            });

            // Append some Data
            dropzone.on("sending", function(file, xhr, formData) {
                var container = $(".media-list-upload .media-list");
                formData.append("path", container.attr("data-path"));
                formData.append("nonce", container.attr("data-token"));
                formData.append("tokenCSRF", container.attr("data-token"));
                formData.append("media_action", "upload");

                console.log(container.attr("data-path"));
            });

            // Handle File Toasts
            dropzone.on("addedfile", function(file) {
                //@todo Add Upload Toast for each file
                //createToast("upload", "warning", media.strings["js-form-upload"], file.previewTemplate);
            });

            // Complete AJAX Request
            dropzone.on("complete", function(file) {
                var data = JSON.parse(file.xhr.response);

                // Show Error Message
                if(data.status === "error") {
                    createToast("status", "error", media.strings["js-form-upload"], buildMessage(data));
                    return;
                }

                // Handle on Success
                if(data.status === "success") {
                    createToast("status", "success", media.strings["js-form-upload"], data.message);

                    if(typeof data.data.items !== "undefined") {
                        addMediaItems(data.data.items);
                    }
                }
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
         |  GENERAL :: REPLACE CORE MODAL WITH MEDIA MODAL
         |  @since  0.1.0
         */
        if($("#jsmediaManagerOpenModal").length > 0 && $("#media-manager-modal").length > 0) {
            let modal = $("#media-manager-modal");
            let button = $("#jsmediaManagerOpenModal");
            let token = modal.attr("data-nonce");

            // Good-Bye Core Modal
            $(window).off("dragover dragenter");
            $(window).off("drop");
            $('#jsmediaManagerModal').on('shown.bs.modal', function() {
                $('#jsmediaManagerModal').modal('dispose');
            });

            // Change Button Text
            button.html(`<span class="fa fa-image"></span>` + media.strings["js-media-title"]);
            button.attr("data-target", "#media-manager-modal");

            // Click on Modals
            $(".media-modal").on("show.bs.modal", function() {
                setLoader(true);
                modal.css("opacity", 0.5);
            }).on("hidden.bs.modal", function() {
                modal.css("opacity", 1.0);
                setLoader(false);
            });

            // Hide Modal & Prevent Loader on Error
            modal.on("hidden.bs.modal", function() {
                modal.css("opacity", 1.0);
                setLoader(false);
            });
        }

        /*
         |  MODALS :: INIT MODAL DATA
         |  @since  0.1.0
         |
         |  @param  object  All available modal links.
         |
         |  @return void
         */
        function mediaModalInit(items) {
            items.on("show.bs.modal", function(event) {
                $(this).find('[data-media-value]').each(function() {
                    this.value = $(event.relatedTarget).attr("data-media-" + this.getAttribute("data-media-value"));
                });
            });
        }
        mediaModalInit($(".media-modal"));


        /*
         |  MODALS :: FOCUS MODAL INPUT
         |  @since  0.1.0
         |
         |  @param  object  All available modals.
         |
         |  @return void
         */
        function mediaFocusModalForm(items) {
            items.on("shown.bs.modal", function(event) {
                this.querySelector(`input:not([type="button"]):not([type="hidden"])`).focus();
            });
        }
        mediaFocusModalForm($(".media-modal"));


        /*
         |  MODALS :: SUBMIT MODAL FORM
         |  @since  0.1.0
         |
         |  @param  object  All available modal forms.
         |
         |  @return void
         */
        function mediaSubmitModalForm(forms) {
            if(!media.admin) {
                return;
            }

            forms.submit(function(event) {
                event.preventDefault();

                // Set Data
                let self = this;
                let action = this.querySelector(`[name="media_action"]`).value;
                let form = new FormData(this);
                    form.append("media_action", action);
                    form.append("path", $(".media-list").attr("data-path"));

                // AJAX Submit
                $.post({
                    url: this.getAttribute("action"),
                    data: form,
                    processData: false,
                    contentType: false,
                    dataType: "json"
                }).done(function(data) {
                    createToast("status", "success", media.strings[`js-form-${action}`], data.message);

                    // Add Items
                    if(typeof data.data.items !== "undefined") {
                        addMediaItems(data.data.items);
                    }

                    // Empty Create Modal Form
                    if(action === "create") {
                        $(self).find(`input[type="text"]`).val("");
                    }

                    // Applied Search Form
                    if(action === "search" && typeof data.data.content !== "undefined") {
                        updateMediaList(data.data.content);
                    }
                }).fail(function(xhr) {
                    createToast("status", "danger", media.strings[`js-form-${action}`], buildMessage(xhr.responseJSON));
                });
            });
        }
        mediaSubmitModalForm($(".media-modal form"));


        /*
         |  DETAILS :: LOAD PREVIEW
         |  @since  0.1.0
         |
         |  @param  object  The preview items.
         |
         |  @return void
         */
        function mediaDetailsPreview(items) {
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
            items.each(function(){ meta.call(this); })
        }

        /*
         |  DETAILS :: SUBMIT DETAILS FORM
         |  @since  0.1.0
         |
         |  @param  object  The details form.
         |
         |  @return void
         */
        function mediaDetailsForm(forms) {
            if(!media.admin) {
                return;
            }

            forms.submit(function(event) {
                event.preventDefault();

                // Set Data
                let self = this;
                let action = this.querySelector(`[name="media_action"]`).value;
                let form = new FormData(this);
                    form.append("media_action", action);
                    form.append("path", $(".media-list").attr("data-path"));

                // AJAX Submit
                $.post({
                    url: this.getAttribute("action"),
                    data: form,
                    processData: false,
                    contentType: false,
                    dataType: "json"
                }).done(function(data) {
                    createToast("status", "success", media.strings[`js-form-${action}`], data.message);

                    // Update Content
                    if(typeof data.data.content !== "undefined") {
                        updateMediaList(data.data.content);
                        updateBreadcrumbs(data.data.path, data.data.file);
                        mediaInitDetailsPage();
                    }

                    // Update File
                    $(".media-single-details img,.media-single-details source").each(function() {
                        this.src = this.src + "?t=" + new Date().getTime();
                    });
                }).fail(function(xhr) {
                    createToast("status", "danger", media.strings[`js-form-${action}`], buildMessage(xhr.responseJSON));
                });
            })
        }

        /*
         |  DETAILS :: INIT DETAILS PAGE
         |  @since  0.1.0
         |
         |  @return void
         */
        function mediaInitDetailsPage() {
            mediaDetailsPreview($(".media-preview-video video,.media-preview-audio audio"));
            mediaDetailsForm($(".media-single-details form"));
            bsCustomFileInput.init();
        }
        mediaInitDetailsPage();


        /*
         |  DETAILS :: INIT CODEMIRROR EDITOR
         |  @since  0.2.0
         |
         |  @return void
         */
        function mediaCodeMirrorEditor(textarea) {
            var type = textarea.attr("data-type");
            var myCodeMirror = CodeMirror.fromTextArea(textarea.get(0), {
                mode: type,
                theme: "neo",
                lineNumbers: true,
            });
        }
        if($("#media-plus-file-editor").length > 0) {
            mediaCodeMirrorEditor($("#media-plus-file-editor"));
        }


        /*
         |  ACTIONS :: HANDLE MEDIA ACTIONs
         |  @since  0.2.0
         */
        function mediaActionHandler(event) {
            let action = this.getAttribute("data-media-action");

            if(action in media.actions) {
                if(media.actions[action].call(this, event)) {
                    event.preventDefault();
                }
            }
        }
        $(document).on("click", "a[data-media-action]:not([target]),button[data-media-action]:not(:disabled):not([disabled])", mediaActionHandler);

        /*
         |  ACTIONS :: SUBMIT MEDIA ACTION
         |  @since  0.2.0
         */
        function mediaActionSubmit(url, action, callback) {
            if(url.indexOf("admin/media?") >= 0) {
                url = url.replace("admin/media?", `admin/media/${action}?`);
            } else if(url.endsWith("admin/media")) {
                url = url + "/list?path=/";
            }
            if(url.indexOf("media_action=") < 0) {
                url = `${url}&media_action=${action}`;
            }
            if(url.indexOf("nonce=") < 0) {
                url = `${url}&nonce=${$(".media-list").attr("data-token")}`;
            }
            if(url.indexOf("tokenCSRF=") < 0) {
                url = `${url}&tokenCSRF=${$(".media-list").attr("data-token")}`;
            }

            // AJAX Request
            $.get({ url: url, dataType: "json" }).done(function(data) {
                createToast("status", "success", media.strings[`js-link-${action}`], data.message);
                callback.call(this, "success", data);
            }).fail(function(xhr) {
                createToast("status", "danger", media.strings[`js-link-${action}`], buildMessage(xhr.responseJSON));
                callback.call(this, "error", data);
            });
        }


        /*
         |  ACTIONS OBJECT
         |  @since  0.2.0
         */
        media.actions = {
            /*
            |  ACTION :: LIST CONTENT
            |  @since  0.2.0
            */
            list: function(event) {
                if(!media.admin) {
                    return false;
                }
                setLoader(true);

                // Call Ajax
                var self = this;
                mediaActionSubmit(this.href, "list", function(status, data) {
                    setLoader(false);
                    if(status === "error" || typeof data.data === "undefined") {
                        return false;
                    }

                    // Update Data
                    updateMediaList(data.data.content);
                    updateBreadcrumbs(data.data.path, data.data.file);

                    // Init Details Page
                    if($(".media-list").hasClass("media-single-details")) {
                        mediaInitDetailsPage();
                    }

                    // Update Layout Buttons
                    let layout = $(".media-list")[0].tagName.toUpperCase() === "TABLE";
                    $(`[data-media-layout="table"]`)[layout? "addClass": "removeClass"]("active");
                    $(`[data-media-layout="grid"]`)[layout? "removeClass": "addClass"]("active");
                });
                return true;
            },


            /*
            |  ACTION :: FAVORITE CONTENT
            |  @since  0.2.0
            */
            favorite: function(event) {
                if(!media.admin) {
                    return false;
                }
                setLoader(true);

                // Call Ajax
                var self = this;
                mediaActionSubmit(this.href, "list", function(status, data) {
                    setLoader(false);
                    if(status === "error") {
                        return false;
                    }

                    // Set Favorite
                    if(data.data.favorite[1]) {
                        self.classList.add("active");
                        self.querySelector(".fa").classList.remove("fa-heart-o");
                        self.querySelector(".fa").classList.add("fa-heart");
                    } else {
                        self.classList.remove("active");
                        self.querySelector(".fa").classList.remove("fa-heart");
                        self.querySelector(".fa").classList.add("fa-heart-o");
                    }
                });
                return true;
            },


            /*
            |  ACTION :: EMBED CONTENT
            |  @since  0.2.0
            */
            embed: function(event) {
                let type = this.getAttribute("data-media-mime");
                let title = this.getAttribute("data-media-name");
                let source = this.href.substr(0, this.href.indexOf("?"));

                writeEditorContent(type, {title: title, source: source});
                $("#media-manager-modal").modal("hide");
                return true;
            },


            /*
            |  ACTION :: SET AS COVER IMAGE
            |  @since  0.2.0
            */
            cover: function(event) {
                $("#jscoverImage").val(this.href.substr(0, this.href.indexOf("?")));
                $("#jscoverImagePreview").attr("src", this.href.substr(0, this.href.indexOf("?")));
                $("#media-manager-modal").modal("hide");
                return true;
            },


            /*
            |  ACTION :: DELETE CONTENT
            |  @since  0.2.0
            */
            delete: function(event) {
                if(!media.admin) {
                    return false;
                }
                setLoader(true);

                // Call Ajax
                var self = this;
                mediaActionSubmit(this.href, "list", function(status, data) {
                    setLoader(false);
                    if(status === "error") {
                        return false;
                    }

                    // Remove Media Item
                    removeMediaItems(data.data.items, data.data.type);
                });
                return true;
            },


            /*
            |  ACTION :: OPEN CONTENT IN NEW TAB
            |  @since  0.2.0
            */
            external: function(event) {
                window = window.open(this.href, "media-preview");
                return true;
            },


            /*
            |  ACTION :: RENAME CONTENT [DETAILS VIEW]
            |  @since  0.2.0
            */
            rename: function(event) {
                let form = $('form[data-media-form="rename"]');
                let input = $('input[name="rename"]');

                if(input.prop("readonly")) {
                    input.prop("readonly", false).focus();
                    input.get(0).setSelectionRange(input.val().lastIndexOf("."), input.val().lastIndexOf("."));

                    $(this).parent().children("[data-media-value]").removeClass("d-none");
                    $(this).parent().children(":not([data-media-value])").addClass("d-none");
                } else {
                    if(this.getAttribute("data-media-value") === "cancel") {
                        let value = form.find('input[name="path"]').val().replace(/\\/g, "/");
                        input.val(value.substr(value.lastIndexOf("/")+1));
                        input.prop("readonly", true);

                        $(this).parent().children(":not([data-media-value])").removeClass("d-none");
                        $(this).parent().children("[data-media-value]").addClass("d-none");
                    }

                    if(this.getAttribute("data-media-value") === "submit") {
                        if(!media.admin) {
                            form.submit();
                            return true;
                        }

                    }
                }
                return true;
            },


            /*
            |  ACTION :: EDIT CONTENT [DETAILS VIEW]
            |  @since  0.2.0
            */
            edit: function(event) {
                let form = $('form[data-media-form="edit"]');

                if(this.getAttribute("data-media-value") === "submit") {
                    if(!media.admin) {
                        form.submit();
                        return true;
                    }
                }
                return true;
            },


            /*
            |  ACTION :: RESIZE DETAILS PAGE [DETAILS VIEW]
            |  @since  0.2.0
            */
            resize: function(event) {
                let cols = $(".media-single-details > .row > div");
                if(cols.length === 2) {
                    if(cols.first().hasClass("col-8")) {
                        cols.first().removeClass("col-8").addClass("col-12");
                        cols.last().removeClass("col-4 pl-5").addClass("col-12 row px-5 py-5 justify-content-around align-items-start");
                        this.innerHTML = `<svg class="media-icon"><use href="#octicon-screen-normal" /></svg>`;
                    } else {
                        cols.first().addClass("col-8").removeClass("col-12");
                        cols.last().addClass("col-4 pl-5").removeClass("col-12 row px-5 py-5 justify-content-around align-items-start");
                        this.innerHTML = `<svg class="media-icon"><use href="#octicon-screen-full" /></svg>`;
                    }
                    return true;
                }
                return false;
            }
        };
    });
}));
