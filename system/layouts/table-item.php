<?php
declare(strict_types=1);
/*
 |  Media       The advanced Media & File Manager for Bludit
 |  @file       ./system/layouts/table-item.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.2.0 [0.1.0] - Beta
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
?>
<tr data-name="<?php echo $basename; ?>" data-type="<?php echo $type; ?>">
    <td class="td-checkbox align-middle">
        <div class="file-thumbnail d-inline-block align-middle text-center <?php echo $color; ?>">
            <span class="<?php echo $icon; ?> d-block text-center text-light"></span>
        </div>
    </td>

    <td class="td-filename align-middle">
        <a href="<?php echo $open; ?>" class="text-secondary" data-media-action="list">
            <strong><?php echo $basename; ?></strong>
        </a>
    </td>

    <?php if(PAW_MEDIA_PLUS && isset($favorite)) { ?>
        <td class="td-favorite align-middle text-center">
            <a href="<?php echo $favorite; ?>" class="text-danger d-block <?php echo $this->isFavorite($absolute)? "active": ""; ?>" data-media-action="favorite">
                <span class="fa <?php echo $this->isFavorite($absolute)? "fa-heart": "fa-heart-o"; ?>"></span>
            </a>
        </td>
    <?php } ?>

    <td class="td-filetype align-middle text-center">
        <?php echo $text; ?>
    </td>

    <td class="td-filesize align-middle <?php echo $type === "file"? 'text-right': "text-center"; ?>">
        <?php echo $type === "file"? $media_manager->calcFileSize(filesize($real)): "-"; ?>
    </td>

    <td class="td-actions align-middle text-right">
        <?php if(is_file($absolute)) { ?>
            <?php if($this->view === "modal") { ?>
                <a href="<?php echo $url; ?>?action=embed" class="media-action action-success" data-media-name="<?php echo $basename; ?>" data-media-action="embed" data-media-mime="<?php echo $file_mime; ?>" data-tooltip="<?php bt_e("Embed"); ?>">
                    <svg class="media-icon"><use href="#octicon-diff-added" /></svg>
                </a>
                <a href="<?php echo $open; ?>" class="media-action action-primary" data-media-action="list" data-tooltip="<?php bt_e("Details"); ?>">
                    <svg class="media-icon"><use href="#octicon-file-symlink-file" /></svg>
                </a>
            <?php } else { ?>
                <a href="<?php echo $open; ?>" class="media-action action-primary" data-media-action="list" data-tooltip="<?php bt_e("Details"); ?>">
                    <svg class="media-icon"><use href="#octicon-file-symlink-file" /></svg>
                </a>
                <a href="<?php echo $url; ?>" class="media-action action-info" target="_blank" data-media-action="external" data-tooltip="<?php bt_e("View"); ?>">
                    <svg class="media-icon"><use href="#octicon-link-external" /></svg>
                </a>
            <?php } ?>
        <?php } else { ?>
            <a href="<?php echo $open; ?>" class="media-action action-primary" data-media-action="list" data-tooltip="<?php bt_e("Open"); ?>">
                <svg><use href="#octicon-file-symlink-directory" /></svg>
            </a>
        <?php } ?>

        <?php if(!(dirname($absolute) === rtrim(PATH_UPLOADS, DS) && in_array($basename, ["media", "pages", "profiles", "thumbnails"]))) { ?>
            <a href="#media-edit-item" class="media-action action-warning" data-tooltip="<?php bt_e("Edit"); ?>" data-toggle="modal" data-media-path="<?php echo $absolute; ?>" data-media-name="<?php echo $basename; ?>">
                <svg class="media-icon"><use href="#octicon-pencil" /></svg>
            </a>
            <a href="#media-delete-item" class="media-action action-danger" data-tooltip="<?php bt_e("Delete"); ?>" data-toggle="modal" data-media-path="<?php echo $absolute; ?>">
                <svg class="media-icon"><use href="#octicon-trashcan" /></svg>
            </a>
        <?php } ?>
    </td>
</tr>
