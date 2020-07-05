<?php
declare(strict_types=1);
/*
 |  Media       The advanced Media & File Manager for Bludit
 |  @file       ./system/layouts/grid-item.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.2.0 [0.1.0] - Beta
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
?>
<div class="col col-md-4 col-sm-6 col-12" data-name="<?php echo $pathinfo["basename"]; ?>" data-type="<?php echo $pathinfo["type"]; ?>">
    <div class="card mb-4 shadow-sm">
        <div class="card-img-top p-3">
            <?php if($pathinfo["type"] === "file" && $file_type === "image") { ?>
                <a href="<?php echo $open; ?>" class="file-thumbnail text-center <?php echo $color; ?> text-white d-block rounded p-0" data-media-action="list">
                    <img src="<?php echo $pathinfo["url"]; ?>" class="m-0 d-block" alt="<?php bt_e("Thumbnail"); ?>" />
                </a>
            <?php } else { ?>
                <a href="<?php echo $open; ?>" class="file-thumbnail text-center <?php echo $color; ?> text-white d-block rounded" data-media-action="list">
                    <span class="<?php echo $icon; ?> d-block text-center text-light"></span>
                </a>
            <?php } ?>
        </div>

        <div class="card-body pt-1 pb-2">
            <h6 class="card-title">
                <a href="<?php echo $open; ?>" class="text-secondary" data-media-action="list"><?php echo $pathinfo["basename"]; ?></a>
            </h6>
        </div>

        <div class="card-footer text-right p-2">
            <div class="d-flex">
                <div class="flex-fill text-left">
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

                    <?php if(PAW_MEDIA_PLUS) { ?>
                        <a href="<?php echo $favorite; ?>" class="media-action action-danger <?php echo $this->isFavorite($pathinfo["absolute"])? "active": ""; ?>" data-tooltip="<?php bt_e("Open"); ?>" data-media-action="favorite" data-media-tooltip="<?php bt_e("Favorite"); ?>">
                            <span class="fa <?php echo $this->isFavorite($pathinfo["absolute"])? "fa-heart": "fa-heart-o"; ?>"></span>
                        </a>
                    <?php } ?>
                </div>
                <div class="flex-fill text-right">
                    <?php if(!(dirname($pathinfo["absolute"]) === rtrim(PATH_UPLOADS, DS) && in_array($pathinfo["basename"], ["media", "pages", "profiles", "thumbnails"]))) { ?>
                        <a href="#media-edit-item" class="media-action action-warning" data-tooltip="<?php bt_e("Edit"); ?>" data-toggle="modal" data-media-path="<?php echo $pathinfo["slug"]; ?>" data-media-name="<?php echo $pathinfo["basename"]; ?>">
                            <svg class="media-icon"><use href="#octicon-pencil" /></svg>
                        </a>
                        <a href="#media-delete-item" class="media-action action-danger" data-tooltip="<?php bt_e("Delete"); ?>" data-toggle="modal" data-media-path="<?php echo $pathinfo["slug"]; ?>">
                            <svg class="media-icon"><use href="#octicon-trashcan" /></svg>
                        </a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
