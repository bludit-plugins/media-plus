CHANGELOG
=========

Version 0.2.0 - Alpha
---------------------
-   Add: New option to change the default upload path (root, root/pages, root/media).
-   Add: New option to resolve folder names to its slugs or titles.
-   Add: The new `delete()` method on the `MediaManager` class.
-   Add: The new `_rename()` action method on the `MediaAdmin` class.
-   Add: The new `modal-edit.php` modal (file).
-   Add: The new `modal-delete.php` modal (file).
-   Update: Add an own static media upload directory.
-   Update: Change the default upload path to the new static one.
-   Update: Using the real bt toolset v1.0.0.
-   Update: The plugins `post()` method to easily add or change options.
-   Update: Use the `Filesystem` helper methods on the `MediaManager` class.
-   Remove: The `deleteDir()` and `deleteFile()` methods has been replaced with `delete()`.
-   Remove: The `move()` method on the `MediaManager` class (`Filesystem::mv` is used instead).
-   Bugfix: Undefined JavaScript Variables on the `writeEditorContent()` function.
-   Bugfix: Height of 'Page Folder' button is not accurate.
-   Bugfix: Wrong variable used on error message.

Version 0.1.1 - Alpha
---------------------
-   Update: Remove symlink when the source path doesn't exist anymore.
-   Bugfix: Directory Scan Loop breaks when a link points to a non existing path.

Version 0.1.0 - Alpha
---------------------
-   Initial Version
