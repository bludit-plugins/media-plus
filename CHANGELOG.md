CHANGELOG
=========

Version 0.2.0 - Beta
--------------------
-   Add: Support for PDF Files.
-   Add: Support for Editor and Author user roles.
-   Add: The new `media` folder for a permanent file storage.
-   Add: New vector octicon icons for a better view.
-   Add: A new colourized and animated media logo / loading spinner.
-   Add: The new delete modal, which allows to recursive delete folders.
-   Add: The new edit modal, which allows to rename and move files and folders.
-   Add: The new embed modal, which allows to configure the editor insert.
-   Add: The new history class and functions, which logs file changes.
-   Add: The new option `allowed_admin_roles`, to configure which role can access the media page.
-   Add: The new option `allowed_modal_roles`, to configure which role can access the media modal.
-   Add: The new option `resolve_folders`, to change the folder name listing.
-   Add: The new option `root_directoy`, to change the root upload directly.
-   Add: A new JavaScript which adds the Media menu item for authors on the sidebar.
-   Add: Some native plugin functions and hooks to easify the interactions.
-   Add: The new `_rename()` method on the MediaAdmin class.
-   Add: The new `pathinfo` static method on the MediaManager class.
-   Update: All core plugin methods and hooks.
-   Update: The main response / request handler and functions.
-   Update: Improved media plugin configuration form.
-   Update: Switched to the BluTools helper functions.
-   Update: Switched to SASS as pre-processor for the stylesheet.
-   Update: Many design changes has been made also.
-   Update: The whole JavaScript environment.
-   Update: A new Dropzone Upload toast interface.
-   Update: Use the `Filesystem` helper methods on the `MediaManager` class.
-   Rename: The `createFile` and `createDir` MediaManager methods has been merged to `create`.
-   Rename: The `moveFile` and `moveDir` MediaManager methods has been merged to `move`.
-   Rename: The `delteFile` and `deleteDir` MediaManager methods has been merged to `delete`.
-   Bugfix: Undefined JavaScript Variables on the `writeEditorContent()` function.
-   Bugfix: Height of 'Page Folder' button is not accurate.
-   Bugfix: Wrong variable used on error message.

### [PLUS]
-   Add: The new possibility to edit (plain / text) files.
-   Add: The CodeMirror library used for the syntax highlighting when editing files.

Version 0.1.1 - Alpha
---------------------
-   Update: Remove symlink when the source path doesn't exist anymore.
-   Bugfix: Directory Scan Loop breaks when a link points to a non existing path.

Version 0.1.0 - Alpha
---------------------
-   Initial Version
