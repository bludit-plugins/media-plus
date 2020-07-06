<?php
/*
 |  Media       The advanced Media & File Manager for Bludit
 |  @file       ./admin/admin-modal.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.2.0 [0.1.0] - Beta
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
    global $security;
    global $media_admin;

?>
<div id="media-manager-modal" class="modal" tabindex="-1" data-nonce="<?php echo $security->getTokenCSRF(); ?>">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><div class="media-logo-container"><div class="media-logo smaller"><span></span><span></span><span></span><span></span></div></div> Media Manager</h5>
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
                            <a href="<?php echo $media_admin->buildURL("media", ["path" => "pages/" . PAGE_IMAGES_KEY, "temp" => "true"]); ?>" class="page-folder btn btn-light" data-media-action="list">
                                <?php bt_e("Page Folder"); ?>
                            </a>
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

                    <div class="action-handle text-right pl-2">
                        <div class="btn-group">
                            <button class="btn btn-light" data-toggle="modal" data-target="#media-create-folder">
                                <span class="fa fa-folder"></span><?php bt_e("Create Folder"); ?>
                            </button>
                            <button class="btn btn-light media-trigger-upload clickable">
                                <span class="fa fa-upload"></span><?php bt_e("Upload"); ?>
                            </button>
                        </div>

                        <div class="btn-group">
                			<?php $href = $media_admin->buildURL("media", ["layout" => "table"], false); ?>
                            <a href="<?php echo $href; ?>" class="btn btn-light <?php echo $this->getValue("layout") === "table"? "active": ""; ?>" data-media-action="reload" data-media-layout="table">
                				<svg class="media-icon"><use href="#octicon-three-bars" /></svg>
                            </a>

                			<?php $href = $media_admin->buildURL("media", ["layout" => "grid"], false); ?>
                            <a href="<?php echo $href; ?>" class="btn btn-light <?php echo $this->getValue("layout") === "grid"? "active": ""; ?>" data-media-action="reload" data-media-layout="grid">
                				<svg class="media-icon"><use href="#octicon-display-grid" /></svg>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="media-container mt-3" data-path="<?php echo $pathinfo["slug"]; ?>" data-token="<?php echo $security->getTokenCSRF(); ?>">
                	<?php print($media_admin->renderList($media_manager->list($pathinfo["relative"]), $pathinfo["relative"])); ?>
                </div>
            </div>
        </div>
    </div>
</div>
