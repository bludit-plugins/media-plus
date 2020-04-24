<?php
declare(strict_types=1);
/*
 |  Media       An advanced Media & File Manager for Bludit
 |  @file       ./system/layouts/details.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.0 [0.1.0] - Alpha
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
?>
<div class="media-list media-single-details" data-action="<?php echo $this->buildURL("media") ?>" data-path="<?php echo $dirname; ?>" data-file="<?php echo $basename; ?>" data-token="<?php echo $security->getTokenCSRF(); ?>">
    <div class="row">
        <div class="col-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <?php echo $basename; ?>
                </div>

                <?php if($file_type === "image") { ?>
                    <div class="media-preview media-preview-image card-body p-0 text-center bg-light">
                        <img src="<?php echo $source; ?>" class="d-block" alt="<?php paw_e("Image"); ?>" />
                    </div>
                <?php } else if($file_type === "video") { ?>
                    <div class="media-preview media-preview-video card-body">
                        <video width="100%" controls>
                            <source src="<?php echo $source; ?>" type="<?php echo $file_mime; ?>" />
                        </video>
                    </div>
                <?php } else if($file_type === "audio") { ?>
                    <div class="media-preview media-preview-audio card-body">
                        <audio width="100%" controls>
                            <source src="<?php echo $source; ?>" type="<?php echo $file_mime; ?>" />
                        </audio>
                    </div>
                <?php } else { ?>
                    <div class="media-preview media-preview-icon card-body text-center bg-light p-0">
                        <span class="fa fa-file"></span>
                    </div>
                <?php } ?>

                <div class="card-footer bg-white">
                    <div class="d-flex">
                        <div class="flex-fill text-left">
                            <span class="badge badge-primary"><?php echo $media_manager->calcFileSize(filesize($file)); ?></span>
                            <span class="badge badge-secondary"><?php echo $file_mime; ?></span>
                        </div>
                        <div class="flex-fill text-right">
                            <?php if($file_type === "image") { ?>
                                <?php $size = getimagesize($file); ?>
                                <span class="badge badge-secondary"><?php echo $size[0] . "x" . $size[1]; ?></span>
                            <?php } else if($file_type === "video") { ?>
                                <span class="badge badge-secondary" data-media-video="dimension">0x0</span>
                                <span class="badge badge-secondary" data-media-video="duration">00:00</span>
                            <?php } else if($file_type === "audio") { ?>
                                <span class="badge badge-secondary" data-media-audio="duration">00:00</span>
                            <?php } else { ?>
                                <span class="badge badge-secondary"><?php

                                    // Advanced MIME/TYPE recognition
                                    $real_mime = $media_manager->getMIME($file);
                                    switch($real_mime) {
                                        case "application/pdf":
                                            print("PDF Document"); break;
                                        case "application/rtf":
                                            print("Rich Text Format"); break;
                                        case "application/msword": ///@pass
                                        case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
                                            print("MS Word Document"); break;
                                        case "application/vnd.ms-powerpoint": ///@pass
                                        case "application/vnd.openxmlformats-officedocument.presentationml.presentation":
                                            print("MS PowerPoint Presentation"); break;
                                        case "application/vnd.ms-excel": ///@pass
                                        case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
                                            print("MS Excel Spreadsheet"); break;
                                        case "application/vnd.oasis.opendocument.presentation":
                                            print("Open Document Presentation"); break;
                                        case "application/vnd.oasis.opendocument.spreadsheet":
                                            print("Open Document Spreadsheet"); break;
                                        case "application/vnd.oasis.opendocument.text":
                                            print("Open Document Text"); break;
                                        case "application/x-bzip": ///@pass
                                        case "application/x-bzip2": ///@pass
                                        case "application/gzip": ///@pass
                                        case "application/vnd.rar": ///@pass
                                        case "application/x-tar": ///@pass
                                        case "application/zip": ///@pass
                                        case "application/x-7z-compressed":
                                            print("Archive"); break;
                                        case "text/markdown":
                                            print("Markdown File"); break;
                                        case "text/textile":
                                            print("Textile File"); break;
                                        case "text/csv":
                                            print("Comma-Separated-Values"); break;
                                        case "text/css":
                                            print("Stylesheet"); break;
                                        case "text/html": ///@pass
                                        case "application/xhtml+xml":
                                            print("HTML Document"); break;
                                        case "text/xml":
                                            print("XML Document"); break;
                                        case "text/x-php":
                                            print("PHP Document"); break;
                                        case "text/javascript":
                                            print("JavaScript Document"); break;
                                        case "text/typescript":
                                            print("TypeScript Document"); break;
                                        case "application/json":
                                            print("JSON Document"); break;
                                        default:
                                            print("File"); break;
                                    }
                                ?></span>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-4 pl-5">
            <?php if(strpos($url->slug(), "media/list") !== false || strpos($url->slug(), "media/upload") !== false) { ?>
                <a href="<?php echo $source; ?>?action=embed" class="btn btn-light btn-block" data-media-name="<?php echo $basename; ?>" data-media-action="embed" data-media-mime="<?php echo $file_mime; ?>">
                    <?php paw_e("Insert File"); ?>
                </a>
                <?php if($file_type === "image") { ?>
                    <a href="<?php echo $source; ?>?action=cover" class="btn btn-light btn-block" data-media-action="cover">
                        <?php paw_e("Set as Cover Image"); ?>
                    </a>
                <?php } ?>
            <?php } ?>
            <a href="<?php echo $source; ?>" class="btn btn-light btn-block mb-5" target="_blank"><?php paw_e("View in a new Tab"); ?></a>

            <div class="card shadow-sm mb-5">
                <form method="post" action="<?php echo $this->buildURL("media/upload"); ?>" class="card-body" enctype="multipart/form-data">
                    <div class="custom-control custom-checkbox mb-4">
                        <input id="media_revision" type="checkbox" name="revision" value="1" class="custom-control-input" />
                        <label for="media_revision" class="custom-control-label" style="line-height: 20px;"><?php paw_e("Keep current Version"); ?></label>
                    </div>
                    <div class="input-group mb-3">
                        <div class="custom-file">
                            <input id="media_file" type="file" name="media" class="custom-file-input" />
                            <label for="media_file" class="custom-file-label"><?php paw_e("Choose file"); ?></label>
                        </div>
                    </div>

                    <input type="hidden" name="tokenCSRF" value="<?php echo $security->getTokenCSRF(); ?>" />
                    <input type="hidden" name="nonce" value="<?php echo $security->getTokenCSRF(); ?>" />
                    <input type="hidden" name="path" value="<?php echo $dirname; ?>" />
                    <input type="hidden" name="name" value="<?php echo $basename; ?>" />
                    <input type="hidden" name="overwrite" value="1" />
                    <button type="submit" name="media_action" value="upload" class="btn btn-primary btn-block"><?php paw_e("Upload a new Version"); ?></button>
                </form>
            </div>

            <div class="card shadow-sm mb-5">
                <form method="post" action="<?php echo $this->buildURL("media/move"); ?>" class="card-body">
                    <div class="custom-control custom-checkbox mb-4">
                        <input id="media_force" type="checkbox" name="force" value="1" class="custom-control-input" />
                        <label for="media_force" class="custom-control-label" style="line-height: 20px;"><?php paw_e("Force new Extension"); ?></label>
                    </div>

                    <input type="text" name="rename" value="<?php echo $basename; ?>" placeholder="<?php paw_e("Filename"); ?>" class="form-control mb-4" />

                    <input type="hidden" name="tokenCSRF" value="<?php echo $security->getTokenCSRF(); ?>" />
                    <input type="hidden" name="nonce" value="<?php echo $security->getTokenCSRF(); ?>" />
                    <input type="hidden" name="path" value="<?php echo $dirname; ?>" />
                    <input type="hidden" name="name" value="<?php echo $basename; ?>" />
                    <button type="submit" name="media_action" value="move" class="btn btn-secondary btn-block"><?php paw_e("Rename File"); ?></button>
                </form>
            </div>

            <a href="<?php echo $delete; ?>" class="btn btn-danger btn-block" data-media-action="delete"><?php paw_e("Delete File"); ?></a>
        </div>
    </div>
</div>
