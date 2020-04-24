<?php
/*
 |  Media       An advanced Media & File Manager for Bludit
 |  @file       ./admin/modal.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.0 [0.1.0] - Alpha
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
?>
<div id="media-manager-modal" class="modal" tabindex="-1" data-nonce="<?php echo $security->getTokenCSRF(); ?>">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Media Manager</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="media-actions d-flex">
                    <div class="flex-fill pr-2">
                        <?php
                        if(method_exists($media_admin, "renderToolbar")) {
                            $tools = $media_admin->renderToolbar();
                        }
                        ?>
                        <nav class="media-toolbar <?php echo isset($tools)? "media-toolbar-plus": ""; ?> m-0">
                            <ol class="breadcrumb m-0 p-2 flex-nowrap">
                                <?php if(empty($path)) { ?>
                                    <li class="breadcrumb-item active"><a href="<?php echo HTML_PATH_ADMIN_ROOT . "media?path=/"; ?>" data-media-action="list">root</a></li>
                                <?php } else { ?>
                                    <li class="breadcrumb-item"><a href="<?php echo HTML_PATH_ADMIN_ROOT . "media?path=/"; ?>" data-media-action="list">root</a></li>
                                <?php } ?>
                            </ol>
                        	<?php
                        		if(isset($tools)) {
                        			print($tools);
                        		}
                        	?>
                        </nav>
                    </div>

                    <div class="text-right pl-2">
                        <div class="btn-group">
                            <button class="btn btn-light" data-toggle="modal" data-target="#media-create-folder">
                                <span class="fa fa-folder"></span><?php paw_e("Create Folder"); ?>
                            </button>
                            <button class="btn btn-light media-trigger-upload clickable">
                                <span class="fa fa-upload"></span><?php paw_e("Upload"); ?>
                            </button>
                        </div>

                        <div class="btn-group">
                            <?php $href = $media_admin->buildURL("media", ["layout" => "table"], false); ?>
                            <a href="<?php echo $href; ?>" class="btn btn-light <?php echo $this->getValue("layout") === "table"? "active": ""; ?>" data-media-action="list" data-media-layout="table">
                                <span class="fa fa-th-list"></span>
                            </a>

                            <?php $href = $media_admin->buildURL("media", ["layout" => "grid"], false); ?>
                            <a href="<?php echo $href; ?>" class="btn btn-light <?php echo $this->getValue("layout") === "grid"? "active": ""; ?>" data-media-action="list" data-media-layout="grid">
                                <span class="fa fa-th-large"></span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="media-list-upload mt-3" />
                	<?php print($media_admin->renderList($media_manager->list($relative), $relative)); ?>
                </div>
            </div>
        </div>
    </div>
</div>
