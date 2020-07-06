<?php
/*
 |  Media       The advanced Media & File Manager for Bludit
 |  @file       ./admin/admin-form.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.2.0 [0.1.0] - Beta
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
    defined("BLUDIT") or die("Go directly to Jail. Do not pass Go. Do not collect 200 Cookies!");
?>
<div class="navbar navbar-expand-lg navbar-light bg-white border rounded shadow-sm">
    <div class="col-12 px-0">
        <ul class="nav navbar-nav">
            <li class="nav-item">
                <a href="#mediaGeneral" class="nav-link active" data-toggle="tab"><?php bt_e("General"); ?></a>
            </li>
            <li class="nav-item">
                <a href="#mediaAdvanced" class="nav-link" data-toggle="tab"><?php bt_e("Advanced"); ?></a>
            </li>
            <li class="nav-item">
                <a href="#mediaDefaults" class="nav-link" data-toggle="tab"><?php bt_e("Defaults"); ?></a>
            </li>
            <li class="nav-item ml-auto">
                <a href="https://www.github.com/pytesNET/media" class="nav-link" target="_blank">Version: <?php echo self::VERSION; ?></a>
            </li>
        </ul>
    </div>
</div>

<div class="tab-content my-4 px-2">
    <div id="mediaGeneral" class="tab-pane fade active show">
        <div class="row">
            <div class="col-3 pt-3">
                <label class="text-right pt-2 mr-3" style="margin-top:0!important;"><?php bt_e("Admin Page Access"); ?></label>
            </div>
            <div class="col-9 pt-4">
                <div class="custom-control custom-checkbox">
                    <input id="admin-roles-admin" type="checkbox" name="allowed_admin_roles[]" value="admin" class="custom-control-input"<?php bt_checked(in_array("admin", $this->getValue("allowed_admin_roles"))); ?> disabled />
                    <label for="admin-roles-admin" class="custom-control-label" style="margin-top:0!important;"><?php bt_e("Admin"); ?></label>
                </div>
                <div class="custom-control custom-checkbox">
                    <input id="admin-roles-editor" type="checkbox" name="allowed_admin_roles[]" value="editor" class="custom-control-input"<?php bt_checked(in_array("editor", $this->getValue("allowed_admin_roles"))); ?> />
                    <label for="admin-roles-editor" class="custom-control-label" style="margin-top:.5rem!important;"><?php bt_e("Editor"); ?></label>
                </div>
                <div class="custom-control custom-checkbox">
                    <input id="admin-roles-author" type="checkbox" name="allowed_admin_roles[]" value="author" class="custom-control-input"<?php bt_checked(in_array("author", $this->getValue("allowed_admin_roles"))); ?> />
                    <label for="admin-roles-author" class="custom-control-label" style="margin-top:.5rem!important;"><?php bt_e("Author"); ?></label>
                </div>
                <span class="tip"><?php bt_e("Control who can access the media admin page."); ?></span>
            </div>

            <div class="col-3 pt-3">
                <label class="text-right pt-2 mr-3" style="margin-top:0!important;"><?php bt_e("Modal Access"); ?></label>
            </div>
            <div class="col-9 pt-4">
                <div class="custom-control custom-checkbox">
                    <input id="modal-roles-admin" type="checkbox" name="allowed_modal_roles[]" value="admin" class="custom-control-input"<?php bt_checked(in_array("admin", $this->getValue("allowed_modal_roles"))); ?> disabled />
                    <label for="modal-roles-admin" class="custom-control-label" style="margin-top:0!important;"><?php bt_e("Admin"); ?></label>
                </div>
                <div class="custom-control custom-checkbox">
                    <input id="modal-roles-editor" type="checkbox" name="allowed_modal_roles[]" value="editor" class="custom-control-input"<?php bt_checked(in_array("editor", $this->getValue("allowed_modal_roles"))); ?> />
                    <label for="modal-roles-editor" class="custom-control-label" style="margin-top:.5rem!important;"><?php bt_e("Editor"); ?></label>
                </div>
                <div class="custom-control custom-checkbox">
                    <input id="modal-roles-author" type="checkbox" name="allowed_modal_roles[]" value="author" class="custom-control-input"<?php bt_checked(in_array("author", $this->getValue("allowed_modal_roles"))); ?> />
                    <label for="modal-roles-author" class="custom-control-label" style="margin-top:.5rem!important;"><?php bt_e("Author"); ?></label>
                </div>
                <span class="tip"><?php bt_e("Control who can access the media modal on the content pages."); ?></span>
            </div>

            <div class="col-3 pt-3">
                <label class="text-right pt-2 mr-3" style="margin-top:0!important;"><?php bt_e("Special File Uploads"); ?></label>
            </div>
            <div class="col-9 pt-2">
                <div class="custom-control custom-checkbox">
                    <input type="hidden" name="allow_js_upload" value="false" />
                    <input id="media_allow_js_upload" type="checkbox" name="allow_js_upload" value="true" class="custom-control-input"<?php bt_checked($this->getValue("allow_js_upload")); ?> />
                    <label class="custom-control-label" for="media_allow_js_upload">
                        <?php bt_e("Allow JavaScript Files"); ?> <span class="text-muted">(.js, .mjs, .ts, .tsx)</span>
                    </label>
                </div>

                <?php $checked = $this->getValue("allow_html_upload"); ?>
                <div class="custom-control custom-checkbox">
                    <input type="hidden" name="allow_html_upload" value="false" />
                    <input id="media_allow_html_upload" type="checkbox" name="allow_html_upload" value="true" class="custom-control-input"<?php bt_checked($this->getValue("allow_html_upload")); ?> />
                    <label class="custom-control-label" for="media_allow_html_upload">
                        <?php bt_e("Allow HTML Files"); ?> <span class="text-muted">(.html, .htm, .xml, .xhtml)</span>
                    </label>
                </div>

                <?php $checked = $this->getValue("allow_php_upload"); ?>
                <div class="custom-control custom-checkbox mb-3">
                    <input type="hidden" name="allow_php_upload" value="false" />
                    <input id="media_allow_php_upload" type="checkbox" name="allow_php_upload" value="true" class="custom-control-input" <?php bt_checked($this->getValue("allow_php_upload")); ?> />
                    <label class="custom-control-label" for="media_allow_php_upload">
                        <?php bt_e("Allow PHP Files"); ?> <span class="text-muted">(.php, .phtml, .php*, .phps, .php-s, .pht, .phar)</span>
                    </label>
                </div>
            </div>

            <div class="col-3 pt-3">
                <label for="media_custom_mime_types" class="text-right pt-2 mr-3" style="margin-top:0!important;"><?php bt_e("Custom Types"); ?></label>
            </div>
            <div class="col-9 pt-3">
                <?php
                    $value = "";
                    $values = $this->getValue("custom_mime_types");
                    foreach($values AS $mime => $ext) {
                        $value .= "$mime#" . implode(",", $ext) . "\n";
                    }
                ?>
                <textarea id="media_custom_mime_types" name="custom_mime_types" placeholder="Your additional MIME Types (See Syntax below)" class="form-control"><?php echo $value; ?></textarea>
                <span class="tip"><?php bt_e("One mime type per line."); ?> <?php bt_e("Syntax"); ?>: <code>mime/type#.ext1,.ext2</code>. <?php bt_e("Example"); ?>: <code>text/html#.html,.htm</code></span>
            </div>
        </div>
    </div>

    <div id="mediaAdvanced" class="tab-pane fade">
        <div class="row">
            <div class="col-3 pt-3">
                <label class="text-right pt-2 mr-3" style="margin-top:0!important;"><?php bt_e("Resolve Page Folders"); ?></label>
            </div>
            <div class="col-9 pt-3">
                <select id="media_resolve_folders" name="resolve_folders" class="custom-select w-50">
                    <option value="symlink"<?php bt_selected($this->getValue("resolve_folders"), "symlink"); ?>><?php bt_e("Resolve with Symlink only"); ?></option>
                    <option value="page_title"<?php bt_selected($this->getValue("resolve_folders"), "page_title"); ?>><?php bt_e("Resolve with Page Title"); ?></option>
                    <option value="page_slug"<?php bt_selected($this->getValue("resolve_folders"), "page_slug"); ?>><?php bt_ae("Resolve with Page Slug"); ?></option>
                </select>
                <span class="tip"><?php bt_ae("Shows the Page Title or Page Slug instead of the UUID, when no Symlink is created."); ?></span>
            </div>

            <div class="col-3 pt-3">
                <label class="text-right pt-2 mr-3" style="margin-top:0!important;"><?php bt_e("Root Settings"); ?></label>
            </div>
            <div class="col-9 pt-3">
                <select id="media_root_directory" name="root_directory" class="custom-select w-50">
                    <option value="root"<?php bt_selected($this->getValue("root_directory"), "root"); ?>><?php bt_ae("Main :path directory", [":path" => "root"]); ?></option>
                    <option value="root/pages"<?php bt_selected($this->getValue("root_directory"), "root/pages"); ?>><?php bt_ae("Temporary :path folder", [":path" => "root/pages"]); ?></option>
                    <option value="root/media"<?php bt_selected($this->getValue("root_directory"), "root/media"); ?>><?php bt_ae("Permanently :path folder", [":path" => "root/media"]); ?></option>
                </select>
                <span class="tip"><?php bt_ae("Content stored in the ':path' folder may gets deleted, when the respective Content Page gets removed.", [":path" => "root/pages"]); ?></span>

                <div class="custom-control custom-checkbox">
                    <input type="hidden" name="allow_root_upload" value="false" />
                    <input id="media_allow_root_upload" type="checkbox" name="allow_root_upload" value="true" class="custom-control-input" <?php bt_checked($this->getValue("allow_root_upload")); ?> />
                    <label class="custom-control-label" for="media_allow_root_upload"><?php bt_e("Allow uploading to the direct root directory"); ?></label>
                </div>
            </div>

            <div class="col-3 pt-3">
                <label for="media_layout" class="text-right pt-2 mr-3" style="margin-top:0!important;"><?php bt_e("AJAX Administration"); ?></label>
            </div>
            <div class="col-9 pt-2">
                <div class="custom-control custom-checkbox">
                    <input type="hidden" name="enable_ajax_page" value="false" />
                    <input id="media_enable_ajax_page" type="checkbox" name="enable_ajax_page" value="true" class="custom-control-input" <?php bt_checked($this->getValue("enable_ajax_page")); ?> />
                    <label class="custom-control-label" for="media_enable_ajax_page"><?php bt_e("Enable AJAX on the Media Manager Admin Page"); ?></label>
                </div>
                <span class="tip"><span class="text-danger"><?php bt_e("This is an Experimental Function and may leads to some errors."); ?></span></span>
            </div>
        </div>
    </div>

    <div id="mediaDefaults" class="tab-pane fade">
        <div class="row">
            <div class="col-3 pt-3">
                <label for="media_layout" class="text-right pt-2 mr-3" style="margin-top:0!important;"><?php bt_e("Media Layout"); ?></label>
            </div>
            <div class="col-9 pt-3">
                <select id="media_layout" name="layout" class="custom-select w-50">
                    <option value="table" <?php bt_selected($this->getValue("layout"), "table"); ?>><?php bt_e("Table"); ?></option>
                    <option value="grid" <?php bt_selected($this->getValue("layout"), "grid"); ?>><?php bt_e("Grid"); ?></option>
                </select>
                <span class="tip"><?php bt_e("The default layout for all users."); ?></span>
            </div>

            <div class="col-3 pt-3">
                <label for="media_items_order" class="text-right pt-2 mr-3" style="margin-top:0!important;"><?php bt_e("Items Order"); ?></label>
            </div>
            <div class="col-9 pt-3">
                <select id="media_items_order" name="items_order" class="custom-select w-50">
                    <option value="asc" <?php bt_selected($this->getValue("items_order"), "asc"); ?>><?php bt_e("Ascending"); ?> (A-Z)</option>
                    <option value="desc" <?php bt_selected($this->getValue("items_order"), "desc"); ?>><?php bt_e("Descending"); ?> (Z-A)</option>
                </select>
                <span class="tip"><?php bt_e("The default items order for all users."); ?></span>
            </div>

            <div class="col-3 pt-3">
                <label for="media_items_per_page" class="text-right pt-2 mr-3" style="margin-top:0!important;"><?php bt_e("Items per Page"); ?></label>
            </div>
            <div class="col-9 pt-3">
                <input id="media_items_per_page" type="number" name="items_per_page" value="<?php echo $this->getValue("items_per_page"); ?>" min="0" class="form-control w-50" />
                <span class="tip"><?php bt_e("The default number of shown items for all users, use 0 to show all."); ?></span>
            </div>
        </div>
    </div>
</div>
