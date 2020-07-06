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
<tr data-name="<?php echo $pathinfo["basename"]; ?>" data-type="<?php echo $pathinfo["type"]; ?>">
    <td class="td-checkbox align-middle">
        <div class="file-thumbnail d-inline-block align-middle text-center <?php echo $color; ?>">
            <span class="<?php echo $icon; ?> d-block text-center text-light"></span>
        </div>
    </td>

    <td class="td-filename align-middle">
        <a href="<?php echo $open; ?>" class="text-secondary" data-media-action="list">
            <strong class="d-inline-block"><?php echo $basename; ?></strong>
            <?php
                if(dirname($pathinfo["absolute"]) === rtrim(PATH_UPLOADS, DS)) {
                    if($pathinfo["basename"] === "media") {
                        echo '<span class="fa fa-info-circle align-top ml-1" data-toggle="popover" data-trigger="hover" data-content="'.bt_("Permanent: Keeps files even if the associated page is deleted.").'" style="font-size:16px;"></span>';
                    }
                    if($pathinfo["basename"] === "pages") {
                        echo '<span class="fa fa-info-circle align-top ml-1" data-toggle="popover" data-trigger="hover" data-content="'.bt_("Temporary: Keeps files until the associated page is deleted.").'" style="font-size:16px;"></span>';
                    }
                    if($pathinfo["basename"] === "profiles") {
                        echo '<span class="fa fa-info-circle align-top ml-1" data-toggle="popover" data-trigger="hover" data-content="'.bt_("Temporary: Keeps Avatars until the associated user is deleted.").'" style="font-size:16px;"></span>';
                    }
                    if($pathinfo["basename"] === "thumbnails") {
                        echo '<span class="fa fa-info-circle align-top ml-1" data-toggle="popover" data-trigger="hover" data-content="'.bt_("Temporary: Keeps Thumbnails until the associated page is deleted.").'" style="font-size:16px;"></span>';
                    }
                }
            ?>
        </a>
    </td>

    <?php if(PAW_MEDIA_PLUS && isset($favorite)) { ?>
        <td class="td-favorite align-middle text-center">
            <a href="<?php echo $favorite; ?>" class="text-danger d-block <?php echo $this->isFavorite($pathinfo["absolute"])? "active": ""; ?>" data-media-action="favorite">
                <span class="fa <?php echo $this->isFavorite($pathinfo["absolute"])? "fa-heart": "fa-heart-o"; ?>"></span>
            </a>
        </td>
    <?php } ?>

    <td class="td-filetype align-middle text-center">
        <?php echo $text; ?>
    </td>

    <td class="td-filesize align-middle <?php echo $pathinfo["type"] === "file"? 'text-right': "text-center"; ?>">
        <?php echo $pathinfo["type"] === "file"? $media_manager->calcFileSize(filesize($real)): "-"; ?>
    </td>

    <td class="td-actions align-middle text-right">
        <?php if(is_file($pathinfo["absolute"])) { ?>
            <?php if(!$this->custom) { ?>
                <a href="<?php echo $pathinfo["url"]; ?>?action=embed" class="media-action action-success" data-media-name="<?php echo $pathinfo["basename"]; ?>" data-media-action="embed" data-media-mime="<?php echo $file_mime; ?>" data-tooltip="<?php bt_e("Quick Embed"); ?>">
                    <svg class="media-icon"><use href="#octicon-diff-renamed" /></svg>
                </a>
                <a href="#media-embed-file" class="media-action action-success" data-toggle="modal" data-media-name="<?php echo $pathinfo["basename"]; ?>" data-media-type="<?php echo $file_mime === "application/pdf"? "pdf": $file_type; ?>" data-media-mime="<?php echo $file_mime; ?>" data-media-source="<?php echo $pathinfo["url"]; ?>" data-tooltip="<?php bt_e("Advanced Embed"); ?>">
                    <svg class="media-icon"><use href="#octicon-diff-added" /></svg>
                </a>
                <a href="<?php echo $open; ?>" class="media-action action-primary" data-media-action="list" data-tooltip="<?php bt_e("Details"); ?>">
                    <svg class="media-icon"><use href="#octicon-file-symlink-file" /></svg>
                </a>
            <?php } else { ?>
                <a href="<?php echo $open; ?>" class="media-action action-primary" data-media-action="list" data-tooltip="<?php bt_e("Details"); ?>">
                    <svg class="media-icon"><use href="#octicon-file-symlink-file" /></svg>
                </a>
                <a href="<?php echo $pathinfo["url"]; ?>" class="media-action action-info" target="_blank" data-media-action="external" data-tooltip="<?php bt_e("View"); ?>">
                    <svg class="media-icon"><use href="#octicon-link-external" /></svg>
                </a>
            <?php } ?>
        <?php } else { ?>
            <a href="<?php echo $open; ?>" class="media-action action-primary" data-media-action="list" data-tooltip="<?php bt_e("Open"); ?>">
                <svg><use href="#octicon-file-symlink-directory" /></svg>
            </a>
        <?php } ?>

        <?php if(!(dirname($pathinfo["absolute"]) === rtrim(PATH_UPLOADS, DS) && in_array($pathinfo["basename"], ["media", "pages", "profiles", "thumbnails"]))) { ?>
            <a href="#media-edit-item" class="media-action action-warning" data-tooltip="<?php bt_e("Edit"); ?>" data-toggle="modal" data-media-path="<?php echo $pathinfo["slug"]; ?>" data-media-name="<?php echo $pathinfo["basename"]; ?>">
                <svg class="media-icon"><use href="#octicon-pencil" /></svg>
            </a>
            <a href="#media-delete-item" class="media-action action-danger" data-tooltip="<?php bt_e("Delete"); ?>" data-toggle="modal" data-media-path="<?php echo $pathinfo["slug"]; ?>">
                <svg class="media-icon"><use href="#octicon-trashcan" /></svg>
            </a>
        <?php } ?>
    </td>
</tr>
