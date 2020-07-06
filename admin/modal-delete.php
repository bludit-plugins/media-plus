<?php
/*
 |  Media       The advanced Media & File Manager for Bludit
 |  @file       ./admin/modal-delete.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.2.0 [0.1.0] - Beta
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
?>
<div id="media-delete-item" class="media-modal modal fade" tabindex="-1">
    <div class="modal-dialog">
        <form id="media-delete-form" method="post" action="<?php echo $media_admin->buildURL("media/delete", [], true); ?>" class="modal-content" data-media-form="delete">
            <div class="modal-header">
                <h5 class="modal-title"><?php bt_e("Delete Item"); ?></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php bt_e("Are you sure you want to delete this item?"); ?>
            </div>
            <div class="modal-footer text-right d-flex">
                <div class="col text-left">
                    <div class="custom-control custom-checkbox">
                        <input id="delete-recursive" type="checkbox" name="recursive" value="1" class="custom-control-input" />
                        <label for="delete-recursive" class="custom-control-label"><?php bt_e("Delete Recursive"); ?></label>
                    </div>
                </div>
                <div class="col">
                    <input type="hidden" name="token" value="<?php echo $security->getTokenCSRF(); ?>" />
                    <input type="hidden" name="tokenCSRF" value="<?php echo $security->getTokenCSRF(); ?>" />
                    <input type="hidden" name="path" value="<?php echo $pathinfo["slug"]; ?>" />
                    <input type="hidden" name="action" value="delete" />
                    <button name="action" value="delete" class="btn btn-danger"><?php bt_e("Delete Permanently"); ?></button>
                </div>
            </div>
        </form>
    </div>
</div>
