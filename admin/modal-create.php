<?php
/*
 |  Media       The advanced Media & File Manager for Bludit
 |  @file       ./admin/modal-create.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.2.0 [0.1.0] - Beta
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
?>
<div id="media-create-folder" class="media-modal modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php bt_e("Create a new Folder"); ?></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="media-create-folder-form" method="post" action="<?php echo $media_admin->buildURL("media/create", [], true); ?>" data-media-form="create">
                    <input type="hidden" name="token" value="<?php echo $security->getTokenCSRF(); ?>" />
                    <input type="hidden" name="tokenCSRF" value="<?php echo $security->getTokenCSRF(); ?>" />
                    <input type="hidden" name="path" value="<?php echo $pathinfo["slug"]; ?>" />
                    <input type="hidden" name="type" value="folder" />
                    <input type="hidden" name="action" value="create" />

                    <div class="input-group">
                        <input type="text" class="form-control" name="item" value="" placeholder="<?php bt_e("Folder Name"); ?>" />
                        <div class="input-group-append">
                            <button name="action" value="create" class="btn btn-primary"><?php bt_e("Create"); ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
