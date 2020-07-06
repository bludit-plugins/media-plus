<?php
declare(strict_types=1);
/*
 |  Media       The advanced Media & File Manager for Bludit
 |  @file       ./system/layouts/details.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.2.0 [0.1.0] - Beta
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
?>
<div class="media-list media-list-details mb-5" data-name="<?php echo $pathinfo["basename"]; ?>" data-type="<?php echo $pathinfo["type"]; ?>">
    <div class="row">
        <div class="col-8">
            <div class="card shadow-sm">
                <div class="card-header p-2 bg-white">
                    <form method="post" action="<?php echo $this->buildURL("media/rename"); ?>" class="row" data-media-form="rename">
                        <div class="col-10">
                            <input type="text" class="form-control form-control-clean" name="newname" value="<?php echo $pathinfo["basename"]; ?>" readonly />
                        </div>
                        <div class="col-2 text-right">
                            <div class="btn-group">
                                <button type="submit" class="btn btn-success d-none">
                                    <svg class="media-icon icon-white"><use href="#octicon-check" /></svg>
                                </button>
                                <button type="cancel" class="btn btn-danger d-none" data-media-action="renamed">
                                    <svg class="media-icon icon-white"><use href="#octicon-x" /></svg>
                                </button>

                                <button type="button" class="btn btn-light" data-media-action="renamed">
                                    <svg class="media-icon"><use href="#octicon-pencil" /></svg>
                                </button>
                                <button type="button" class="btn btn-light" data-media-action="resize">
                                    <svg class="media-icon"><use href="#octicon-screen-full" /></svg>
                                </button>

                                <input type="hidden" name="tokenCSRF" value="<?php echo $security->getTokenCSRF(); ?>" />
                                <input type="hidden" name="token" value="<?php echo $security->getTokenCSRF(); ?>" />
                                <input type="hidden" name="path" value="<?php echo $pathinfo["absolute"]; ?>" />
                                <input type="hidden" name="action" value="rename" />
                            </div>
                        </div>
                    </form>
                </div>

                <?php if($file_type === "image") { ?>
                    <div class="media-preview media-preview-image card-body p-0 text-center bg-light">
                        <img src="<?php echo $pathinfo["url"]; ?>" class="d-block" alt="<?php bt_e("Image"); ?>" />
                    </div>
                <?php } else if($file_type === "video") { ?>
                    <div class="media-preview media-preview-video card-body">
                        <video width="100%" controls>
                            <source src="<?php echo $pathinfo["url"]; ?>" type="<?php echo $file_mime; ?>" />
                        </video>
                    </div>
                <?php } else if($file_type === "audio") { ?>
                    <div class="media-preview media-preview-audio card-body">
                        <audio width="100%" controls>
                            <source src="<?php echo $pathinfo["url"]; ?>" type="<?php echo $file_mime; ?>" />
                        </audio>
                    </div>
                <?php } else if($file_mime === "application/pdf") { ?>
                    <div class="media-preview media-preview-audio card-body p-0">
                        <object width="100%" data="<?php echo $pathinfo["url"]; ?>" type="application/pdf" class="d-block mb-0">
                            <p><?php bt_a("Your browser doesn't support to show PDF files, please download the file :here.", [':here' => '<a href="'.$url.'">'.bt_("here").'</a>']); ?></p>
                        </object>
                    </div>
                <?php } else if(PAW_MEDIA_PLUS && $file_type === "text") { ?>
                    <?php if(($mode ?? "") === "edit") { ?>
                        <div class="media-preview media-preview-text card-body text-center bg-light p-0">
                            <form method="post" action="<?php echo $this->buildURL("media/edit"); ?>" class="w-100" data-media-form="edit">
                                <textarea id="media-plus-file-editor" class="form-control" name="content" data-type="<?php echo $media_manager->getMIME($pathinfo["absolute"]); ?>"><?php echo file_get_contents($pathinfo["absolute"]); ?></textarea>

                                <input type="hidden" name="token" value="<?php echo $security->getTokenCSRF(); ?>" />
                                <input type="hidden" name="tokenCSRF" value="<?php echo $security->getTokenCSRF(); ?>" />
                                <input type="hidden" name="path" value="<?php echo $pathinfo["absolute"]; ?>" />
                                <input type="hidden" name="action" value="edit" />
                            </form>
                        </div>
                    <?php } else { ?>
                        <div class="media-preview media-preview-text card-body text-center bg-light p-0">
                            <span class="fa fa-file"></span><br />
                            <a href="<?php echo $edit; ?>" class="btn btn-sm btn-primary" data-media-action="list" data-media-mode="edit"><?php bt_e("Edit this File"); ?></a>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="media-preview media-preview-icon card-body text-center bg-light p-0">
                        <span class="fa fa-file"></span>
                    </div>
                <?php } ?>

                <div class="card-footer bg-white">
                    <?php if(PAW_MEDIA_PLUS && ($mode ?? "") === "edit") { ?>
                        <div class="d-flex">
                            <div class="flex-fill text-left">
                                <a href="<?php echo str_replace("mode=edit", "mode=list", $edit); ?>" class="btn btn-sm btn-secondary" data-media-action="list" data-media-mode="list"><?php bt_e("Cancel"); ?></a>
                            </div>
                            <div class="flex-fill text-right">
                                <button class="btn btn-sm btn-success" data-media-action="edited" data-media-value="submit"><?php bt_e("Save Changes"); ?></button>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="d-flex">
                            <div class="flex-fill text-left">
                                <span class="badge badge-primary"><?php echo $media_manager->calcFileSize(filesize($pathinfo["absolute"])); ?></span>
                                <span class="badge badge-secondary"><?php echo $media_manager->getMIME($pathinfo["absolute"]); ?></span>
                            </div>
                            <div class="flex-fill text-right">
                                <?php if($file_type === "image") { ?>
                                    <?php $size = getimagesize($pathinfo["absolute"]); ?>
                                    <?php if(is_array($size)) { ?>
                                        <span class="badge badge-secondary"><?php echo $size[0] . "x" . $size[1]; ?></span>
                                    <?php } ?>
                                <?php } else if($file_type === "video") { ?>
                                    <span class="badge badge-secondary" data-media-video="dimension">0x0</span>
                                    <span class="badge badge-secondary" data-media-video="duration">00:00</span>
                                <?php } else if($file_type === "audio") { ?>
                                    <span class="badge badge-secondary" data-media-audio="duration">00:00</span>
                                <?php } else { ?>
                                    <span class="badge badge-secondary"><?php

                                        // Advanced MIME/TYPE recognition
                                        $real_mime = $media_manager->getMIME($pathinfo["absolute"]);
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
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="col-4 pl-5">
            <div class="card shadow-sm mb-5" style="width:290px;">
                <div class="card-body">
                    <?php if(!$this->custom) { ?>
                        <a href="<?php echo $pathinfo["url"]; ?>?action=embed" class="btn btn-light btn-block" data-media-name="<?php echo $pathinfo["basename"]; ?>" data-media-action="embed" data-media-mime="<?php echo $file_mime; ?>">
                            <?php bt_e("Insert File"); ?>
                        </a>
                        <?php if($file_type === "image") { ?>
                            <a href="<?php echo $pathinfo["url"]; ?>?action=cover" class="btn btn-light btn-block" data-media-action="cover">
                                <?php bt_e("Set as Cover Image"); ?>
                            </a>
                        <?php } ?>
                    <?php } ?>
                    <a href="<?php echo $pathinfo["url"]; ?>" class="btn btn-secondary btn-block" target="_blank">
                        <svg class="media-icon icon-white"><use href="#octicon-link-external" /></svg> <?php bt_e("View in a new Tab"); ?>
                    </a>
                    <a href="<?php echo $delete; ?>" class="btn btn-danger btn-block" data-media-path="<?php echo $pathinfo["slug"]; ?>">
                        <svg class="media-icon icon-white"><use href="#octicon-trashcan" /></svg> <?php bt_e("Delete File"); ?>
                    </a>
                </div>
            </div>

            <div class="card shadow-sm mb-5" style="width:290px;">
                <form method="post" action="<?php echo $this->buildURL("media/upload"); ?>" class="card-body" enctype="multipart/form-data" data-media-form="upload">
                    <div class="input-group mb-3">
                        <div class="custom-file">
                            <input id="media_file" type="file" name="media" class="custom-file-input" />
                            <label for="media_file" class="custom-file-label"><?php bt_e("Choose new file"); ?></label>
                        </div>
                    </div>
                    <div class="custom-control custom-checkbox mb-3 ml-1">
                        <input id="media_revision" type="checkbox" name="revision" value="1" class="custom-control-input" />
                        <label for="media_revision" class="custom-control-label" style="line-height: 20px;"><?php bt_e("Keep current Version"); ?></label>
                    </div>

                    <input type="hidden" name="token" value="<?php echo $security->getTokenCSRF(); ?>" />
                    <input type="hidden" name="tokenCSRF" value="<?php echo $security->getTokenCSRF(); ?>" />
                    <input type="hidden" name="path" value="<?php echo $pathinfo["slug"]; ?>" />
                    <input type="hidden" name="overwrite" value="1" />
                    <input type="hidden" name="action" value="upload" />
                    <button type="submit" name="action" value="upload" class="btn btn-primary btn-block"><?php bt_e("Upload a new Version"); ?></button>
                </form>

                <div class="card-footer">
                    <div class="mb-1">
                        <a href="#show-history" class="text-secondary" data-toggle="collapse" data-target="#show-history">
                            <?php bt_e("Show History"); ?>
                            <span class="dropdown-toggle"></span>
                        </a>
                    </div>

                    <div id="show-history" class="collapse">
                        <?php $history = $media_history->get($pathinfo["slug"]); ?>
                        <ul class="media-history list-unstyled pt-2 pb-1 px-0 my-0 text-muted">
                            <?php if(empty($history)) { ?>
                                <li><?php bt_e("No changes made so far"); ?></li>
                            <?php } else { ?>
                                <?php foreach($history AS $time => $log) { ?>
                                    <li>
                                        <strong><?php echo date("d/m/Y - H:i:s") ?></strong>
                                        <?php
                                            switch($log["action"]) {
                                                case "edit":
                                                    bt_ae("File has been edited by :user", [":user" => $log["username"]]);
                                                    break;
                                                case "rename":
                                                    bt_ae("Renamed from :before into :after by :user", [
                                                        ":before"   => "<code>" . $log["before"] . "</code>",
                                                        ":after"    => "<code>" . $log["after"] . "</code>",
                                                        ":user"     => $log["username"]
                                                    ]);
                                                    break;
                                                case "move":
                                                    bt_ae("Moved from :before to :after by :user", [
                                                        ":before"   => "<code>" . dirname($log["before"]) . "</code>",
                                                        ":after"    => "<code>" . dirname($log["after"]) . "</code>",
                                                        ":user"     => $log["username"]
                                                    ]);
                                                    break;
                                                case "revise":
                                                    if(empty($log["before"])) {
                                                        bt_ae("New version uploaded by :user", [
                                                            ":user" => $log["username"]
                                                        ]);
                                                        break;
                                                    }

                                                    if($pathinfo["slug"] === $log["after"]) {
                                                        $rev_text = bt_("View old Version of this file.");
                                                        $rev_file = $log["before"];
                                                    } else {
                                                        $rev_text = bt_("View new Version of this file.");
                                                        $rev_file = $log["after"];
                                                    }
                                                    if(MediaManager::absolute($rev_file) !== null) {
                                                        $rev_link = $this->buildURL("media", ["path" => $rev_file]);
                                                    }

                                                    bt_ae("File has been revised by :user.:link", [
                                                        ":user" => $log["username"],
                                                        ":link" => isset($rev_link)? ' <a href="'.$rev_link.'">'.$rev_text.'</a>': ''
                                                    ]);
                                                    break;
                                            }
                                        ?>
                                    </li>
                                <?php } ?>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
