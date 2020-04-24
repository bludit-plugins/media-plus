<?php
declare(strict_types=1);
/*
 |  Media       An advanced Media & File Manager for Bludit
 |  @file       ./system/layouts/grid-item.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.0 [0.1.0] - Alpha
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
?>
<div class="col col-md-4 col-sm-6 col-12" data-name="<?php echo basename($file); ?>" data-type="<?php echo is_file($file)? "file": "folder"; ?>">
    <div class="card mb-4 shadow-sm">
        <div class="card-img-top p-3">
            <?php if($type === "file" && $file_type === "image") { ?>
                <a href="<?php echo ($type === "file")? $details: $folder; ?>" class="file-thumbnail text-center <?php echo $color; ?> text-white d-block rounded p-0" data-media-action="list">
                    <img src="<?php echo $source; ?>" class="m-0 d-block" alt="<?php paw_e("Thumbnail"); ?>" />
                </a>
            <?php } else { ?>
                <a href="<?php echo ($type === "file")? $details: $folder; ?>" class="file-thumbnail text-center <?php echo $color; ?> text-white d-block rounded" data-media-action="list">
                    <span class="<?php echo $icon; ?> d-block text-center text-light"></span>
                </a>
            <?php } ?>
        </div>

        <div class="card-body pt-1 pb-2">
            <h6 class="card-title">
                <a href="<?php echo ($type === "file")? $details: $folder; ?>" class="text-secondary" data-media-action="list"><?php echo $basename; ?></a>
            </h6>
        </div>

        <div class="card-footer text-right p-2">
            <div class="d-flex">
                <div class="flex-fill text-left">
                    <div class="btn-group">
                        <?php if(is_file($file)) { ?>
                            <a href="<?php echo $details; ?>" class="btn btn-sm btn-outline-primary" data-media-action="list">
                                <span class="fa fa-file"></span><?php paw_e("Details"); ?>
                            </a>
                        <?php } else { ?>
                            <a href="<?php echo $folder; ?>" class="btn btn-sm btn-outline-primary" data-media-action="list">
                                <span class="fa fa-folder-open"></span><?php paw_e("Open"); ?>
                            </a>
                        <?php } ?>

                        <?php if(is_file($file)) { ?>
                            <?php if(strpos($url->slug(), "media/list") !== false || strpos($url->slug(), "media/upload") !== false) { ?>
                                <a href="<?php echo $source; ?>?action=embed" class="btn btn-sm btn-outline-secondary" data-media-name="<?php echo $basename; ?>" data-media-action="embed" data-media-mime="<?php echo $file_mime; ?>">
                                    <span class="fa fa-copy"></span> <?php paw_e("Insert"); ?>
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo $source; ?>" class="btn btn-sm btn-outline-secondary" target="_blank">
                                    <span class="fa fa-external-link-square"></span><?php paw_e("View"); ?>
                                </a>
                            <?php } ?>
                        <?php } ?>
                    </div>

                    <?php if(PAW_MEDIA_PLUS) { ?>
                        <?php
                            $favorite = $this->buildURL("media/favorite", [
                                "nonce"         => $security->getTokenCSRF(),
                                "media_action"  => "favorite",
                                "path"          => $path
                            ]);
                        ?>
                        <a href="<?php echo $favorite; ?>" class="btn btn-sm btn-outline-danger <?php echo $this->isFavorite($path)? "active": ""; ?>" data-media-action="favorite">
                            <span class="fa <?php echo $this->isFavorite($path)? "fa-heart": "fa-heart-o"; ?>"></span>
                        </a>
                    <?php } ?>
                </div>
                <div class="flex-fill text-right">
                    <a href="<?php echo $delete; ?>" class="btn btn-sm btn-outline-danger" data-media-action="delete">
                        <span class="fa fa-trash"></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
