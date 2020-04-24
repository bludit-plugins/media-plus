<?php
/*
 |  Media       An advanced Media & File Manager for Bludit
 |  @file       ./admin/form.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.0 [0.1.0] - Alpha
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
?>
<div class="form-group row">
    <div class="col-3 pt-3">
        <label for="media_layout" class="text-right pt-2 mr-3" style="margin-top:0!important;"><?php paw_e("Media Layout"); ?></label>
    </div>
    <div class="col-9 pt-3">
        <select id="media_layout" name="layout" class="custom-select">
            <option value="table" <?php paw_selected($this->getValue("layout"), "table"); ?>><?php paw_e("Table"); ?></option>
            <option value="grid" <?php paw_selected($this->getValue("layout"), "grid"); ?>><?php paw_e("Grid"); ?></option>
        </select>
        <span class="tip"><?php paw_e("This option can be changed on the Media Manager too."); ?></span>
    </div>

    <div class="col-3 pt-3">
        <label for="media_items_order" class="text-right pt-2 mr-3" style="margin-top:0!important;"><?php paw_e("Items Order"); ?></label>
    </div>
    <div class="col-9 pt-3">
        <select id="media_items_order" name="items_order" class="custom-select">
            <option value="asc" <?php paw_selected($this->getValue("items_order"), "asc"); ?>><?php paw_e("Ascending"); ?> (A-Z)</option>
            <option value="desc" <?php paw_selected($this->getValue("items_order"), "desc"); ?>><?php paw_e("Descending"); ?> (Z-A)</option>
        </select>
        <span class="tip"><?php paw_e("This option can be changed on the Media Manager too."); ?></span>
    </div>

    <div class="col-3 pt-3">
        <label for="media_items_per_page" class="text-right pt-2 mr-3" style="margin-top:0!important;"><?php paw_e("Items per Page"); ?></label>
    </div>
    <div class="col-9 pt-3">
        <input id="media_items_per_page" type="number" name="items_per_page" value="<?php echo $this->getValue("items_per_page"); ?>" min="0" class="form-control" />
        <span class="tip"><?php paw_e("The number of items to be shown."); ?> <span class="text-danger"><?php paw_e("Not implemented yet!"); ?></span></span>
    </div>

    <div class="col-12 mt-3"><hr /></div>

    <div class="col-3 pt-3">
        <label for="media_layout" class="text-right pt-2 mr-3" style="margin-top:0!important;"><?php paw_e("AJAX Administration"); ?></label>
    </div>
    <div class="col-9 pt-2">
        <div class="custom-control custom-checkbox">
            <input type="hidden" name="enable_ajax_page" value="false" />
            <input id="media_enable_ajax_page" type="checkbox" name="enable_ajax_page" value="true" class="custom-control-input" <?php paw_checked($this->getValue("enable_ajax_page")); ?> />
            <label class="custom-control-label" for="media_enable_ajax_page"><?php paw_e("Enable AJAX on the Media Manager Admin Page"); ?></label>
        </div>
    </div>

    <div class="col-3 pt-3">
        <label class="text-right pt-2 mr-3" style="margin-top:0!important;"><?php paw_e("Root Upload"); ?></label>
    </div>
    <div class="col-9 pt-2">
        <div class="custom-control custom-checkbox">
            <input type="hidden" name="allow_root_upload" value="false" />
            <input id="media_allow_root_upload" type="checkbox" name="allow_root_upload" value="true" class="custom-control-input" <?php paw_checked($this->getValue("allow_root_upload")); ?> />
            <label class="custom-control-label" for="media_allow_root_upload"><?php paw_e("Allow uploading Files to Root directory"); ?></label>
        </div>
    </div>

    <div class="col-12 mt-3"><hr /></div>

    <div class="col-3 pt-3">
        <label class="text-right pt-2 mr-3" style="margin-top:0!important;"><?php paw_e("Special File Uploads"); ?></label>
    </div>
    <div class="col-9 pt-2">
        <div class="custom-control custom-checkbox">
            <input type="hidden" name="allow_js_upload" value="false" />
            <input id="media_allow_js_upload" type="checkbox" name="allow_js_upload" value="true" class="custom-control-input" <?php paw_checked($this->getValue("allow_js_upload")); ?> />
            <label class="custom-control-label" for="media_allow_js_upload">
                <?php paw_e("Allow JavaScript Files"); ?>
                <span class="text-muted">(.js, .mjs, .ts, .tsx)</span>
            </label>
        </div>

        <?php $checked = $this->getValue("allow_html_upload"); ?>
        <div class="custom-control custom-checkbox">
            <input type="hidden" name="allow_html_upload" value="false" />
            <input id="media_allow_html_upload" type="checkbox" name="allow_html_upload" value="true" class="custom-control-input" <?php paw_checked($this->getValue("allow_html_upload")); ?> />
            <label class="custom-control-label" for="media_allow_html_upload">
                <?php paw_e("Allow HTML Files"); ?>
                <span class="text-muted">(.html, .htm, .xml, .xhtml)</span>
            </label>
        </div>

        <?php $checked = $this->getValue("allow_php_upload"); ?>
        <div class="custom-control custom-checkbox mb-3">
            <input type="hidden" name="allow_php_upload" value="false" />
            <input id="media_allow_php_upload" type="checkbox" name="allow_php_upload" value="true" class="custom-control-input" <?php paw_checked($this->getValue("allow_php_upload")); ?> />
            <label class="custom-control-label" for="media_allow_php_upload">
                <?php paw_e("Allow PHP Files"); ?>
                <span class="text-muted">(.php, .phtml, .php*, .phps, .php-s, .pht, .phar)</span>
            </label>
        </div>
    </div>

    <div class="col-3 pt-3">
        <label for="media_custom_mime_types" class="text-right pt-2 mr-3" style="margin-top:0!important;"><?php paw_e("Custom Types"); ?></label>
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
        <span class="tip"><?php paw_e("One mime type per line."); ?> <?php paw_e("Syntax"); ?>: <code>mime/type#.ext1,.ext2</code>. <?php paw_e("Example"); ?>: <code>text/html#.html,.htm</code></span>
    </div>
</div>
