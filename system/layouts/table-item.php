<?php
declare(strict_types=1);
/*
 |  Media       An advanced Media & File Manager for Bludit
 |  @file       ./system/layouts/table-item.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.0 [0.1.0] - Alpha
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
?>
<tr data-name="<?php echo $basename; ?>" data-type="<?php echo is_file($file)? "file": "folder"; ?>">
    <td class="td-checkbox align-middle">
        <div class="file-thumbnail d-inline-block align-middle text-center <?php echo $color; ?> rounded">
            <span class="<?php echo $icon; ?> d-block text-center text-light"></span>
        </div>
    </td>

    <td class="td-filename align-middle">
        <?php if($type === "file"){ ?>
            <a href="<?php echo $details; ?>" data-media-action="list" class="text-secondary" data-media-action="list">
                <?php echo $basename; ?>
            </a>
        <?php } else {?>
            <a href="<?php echo $folder; ?>" data-media-action="<?php echo "list"; ?>" class="text-secondary" data-media-action="list">
                <strong><?php echo $basename; ?></strong>
            </a>
        <?php } ?>
    </td>

    <?php if(PAW_MEDIA_PLUS) { ?>
        <?php
            $favorite = $this->buildURL("media/favorite", [
                "nonce"         => $security->getTokenCSRF(),
                "media_action"  => "favorite",
                "path"          => $path
            ]);
        ?>
        <td class="td-favorite align-middle text-center">
            <a href="<?php echo $favorite; ?>" class="text-danger d-block <?php echo $this->isFavorite($path)? "active": ""; ?>" data-media-action="favorite">
                <span class="fa <?php echo $this->isFavorite($path)? "fa-heart": "fa-heart-o"; ?>"></span>
            </a>
        </td>
    <?php } ?>

    <td class="td-filetype align-middle text-center">
        <?php echo $text; ?>
    </td>

    <td class="td-filesize align-middle text-right">
        <?php echo is_file($file)? $media_manager->calcFileSize(filesize($file)): ""; ?>
    </td>

    <td class="td-actions align-middle text-right">
        <div class="btn-group">
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

                <a href="<?php echo $details; ?>" class="btn btn-sm btn-outline-primary" data-media-action="list">
                    <span class="fa fa-file"></span><?php paw_e("Details"); ?>
                </a>
            <?php } else { ?>
                <a href="<?php echo $folder; ?>" class="btn btn-sm btn-outline-primary" data-media-action="list">
                    <span class="fa fa-folder-open"></span><?php paw_e("Open"); ?>
                </a>
            <?php } ?>

            <a href="<?php echo $delete; ?>" class="btn btn-sm btn-outline-danger" data-media-action="delete">
                <span class="fa fa-trash"></span><?php paw_e("Delete"); ?>
            </a>
        </div>
    </td>
</tr>
