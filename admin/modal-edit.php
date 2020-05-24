<?php
/*
 |  Media       The advanced Media & File Manager for Bludit
 |  @file       ./admin/modal-edit.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.2.0 [0.1.0] - Beta
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */

    /*
    $dirs = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(PAW_MEDIA_ROOT, FilesystemIterator::KEY_AS_FILENAME | FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS), RecursiveIteratorIterator::SELF_FIRST
    );
    */
?>
<div id="media-edit-item" class="media-modal modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php bt_e("Rename Item"); ?></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="media-edit-rename-form" method="post" action="<?php echo $media_admin->buildURL("media/rename", [], true); ?>" data-media-form="rename">
                    <input type="hidden" name="nonce" value="<?php echo $security->getTokenCSRF(); ?>" />
                    <input type="hidden" name="tokenCSRF" value="<?php echo $security->getTokenCSRF(); ?>" />
                    <input type="hidden" name="path" value="" data-media-value="path" />
                    <input type="hidden" name="media_action" value="rename" />

                    <div class="input-group">
                        <input type="text" class="form-control" name="rename" value="" placeholder="<?php bt_e("Folder Name"); ?>" data-media-value="name" />
                        <div class="input-group-append">
                            <button name="media_action" value="rename" class="btn btn-outline-primary"><?php bt_e("Rename"); ?></button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-body border-top" style="display: none;">
                <form id="media-edit-move-form" method="post" action="<?php echo $media_admin->buildURL("media/move", [], true); ?>" data-media-form="move">
                    <div class="list">
                        <?php
                        /*
                        foreach($dirs AS $name => $dir) {
                            if(!$dir->isDir()) {
                                continue;
                            }
                        }
                        */
                        ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
