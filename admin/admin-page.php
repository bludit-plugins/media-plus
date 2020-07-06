<?php
/*
 |  Media       The advanced Media & File Manager for Bludit
 |  @file       ./admin/admin-page.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.2.0 [0.1.0] - Beta
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
?>
<h2 class="media-title mt-0 mb-3">
	<div class="media-logo-container"><div class="media-logo smaller"><span></span><span></span><span></span><span></span></div></div>
	<span>Media Manager</span>
</h2>

<?php
	if(!empty($media_admin->status)) {
		?><div class="alert alert-<?php echo ($media_admin->status[0])? "success": "danger"; ?>"><?php
			echo $media_admin->status[1];

			// Additional Error Data
			if(isset($media_admin->status[2]) && !empty($media_admin->status[2]["errors"] ?? "")) {
				?><div class='pl-2 pr-2'><?php echo implode("<br>", $media_admin->status[2]["errors"]); ?></div><?php
			}
		?></div><?php
	}
?>

<div class="media-actionbar row">
    <div class="action-goback col-sm">
        <?php if(!empty($pathinfo["relative"]) || $media_admin->method === "search") { ?>
            <?php
				if(strpos($pathinfo["slug"], "/") !== false) {
					$back = substr($pathinfo["slug"], 0, strrpos($pathinfo["slug"], "/"));
				} else {
					$back = "/";
				}
            ?>
            <a href="<?php echo $media_admin->buildURL("media", ["path" => $back]); ?>" class="btn btn-success" data-media-action="list">
				<span class="fa fa-arrow-left"></span> <?php bt_e("Go Back"); ?>
			</a>
        <?php } ?>
    </div>

    <div class="action-handle col-sm text-right">
        <?php if(!is_file($pathinfo["absolute"])) { ?>
            <div class="btn-group">
    			<a href="#media-create-folder" class="btn btn-light" data-toggle="modal">
    				<span class="fa fa-folder"></span> <?php bt_e("Create Folder"); ?>
    			</a>
                <button class="btn btn-light media-trigger-upload clickable">
    				<span class="fa fa-upload"></span> <?php bt_e("Upload"); ?>
    			</button>
            </div>

            <div class="btn-group">
    			<?php $href = $media_admin->buildURL("media", ["path" => $pathinfo["slug"], "layout" => "table"], false); ?>
                <a href="<?php echo $href; ?>" class="btn btn-light <?php echo $this->getValue("layout") === "table"? "active": ""; ?>" data-media-action="list" data-media-layout="table">
    				<svg class="media-icon"><use href="#octicon-three-bars" /></svg>
                </a>

    			<?php $href = $media_admin->buildURL("media", ["path" => $pathinfo["slug"], "layout" => "grid"], false); ?>
                <a href="<?php echo $href; ?>" class="btn btn-light <?php echo $this->getValue("layout") === "grid"? "active": ""; ?>" data-media-action="list" data-media-layout="grid">
    				<svg class="media-icon"><use href="#octicon-display-grid" /></svg>
                </a>
            </div>
        <?php } ?>
    </div>
</div>

<?php
	if(!is_file($pathinfo["absolute"]) && method_exists($media_admin, "renderToolbar")) {
		$tools = $media_admin->renderToolbar();
	}
?>
<nav class="media-toolbar <?php echo isset($tools)? "media-toolbar-plus": ""; ?> mt-4">
    <ol class="breadcrumb">
		<?php
			$search = (PAW_MEDIA_PLUS && $media_admin->method === "search");

			// Root Crumb
			?><li class="breadcrumb-item"><a href="<?php echo $media_admin->buildURL("media"); ?>?path=/" data-media-action="list">root</a></li><?php

			// Breadbrumbs || Search
			if(!$search) {
	            $sub = [];
	            $parts = explode(DS, trim($pathinfo["relative"], DS));
	            $count = 0;
	            foreach($parts AS $folder) {
					if(empty(trim($folder))) {
						continue;
					}
	                $sub[] = $folder;
	                $crumb = $media_admin->buildURL("media", ["path" => implode("/", $sub)]);

	                if(count($parts) === ++$count) {
	                    ?><li class="breadcrumb-item active"><?php echo $folder; ?></li><?php
	                } else {
	                    ?><li class="breadcrumb-item"><a href="<?php echo $crumb; ?>" data-media-action="list"><?php echo $folder; ?></a></li><?php
	                }
	            }
			} else {
				?><li class="breadcrumb-item active"><?php
					echo bt_("Search for: ") . '"' . Sanitize::html(strip_tags($media_admin->search)) . '"';
				?></li><?php
			}
		?>
    </ol>
	<?php
		if(isset($tools)) {
			print($tools);
		} else if(is_file($pathinfo["absolute"]) && PAW_MEDIA_PLUS) {
            ?>
        		<div class="tools btn-group">
        	        <a href="<?php echo $favorite; ?>" class="btn btn-outline-danger <?php echo $media_admin->isFavorite($pathinfo["relative"])? "active": ""; ?>" data-media-action="favorite">
        	            <span class="fa <?php echo $media_admin->isFavorite($pathinfo["relative"])? "fa-heart": "fa-heart-o"; ?>"></span>
        	        </a>
        		</div>
            <?php
        }
	?>
</nav>

<div class="media-container" data-path="<?php echo $pathinfo["slug"]; ?>" data-token="<?php echo $security->getTokenCSRF(); ?>">
	<?php
	    if(is_file($pathinfo["absolute"])) {
	        print($media_admin->renderFile($pathinfo["absolute"]));
	    } else {
    		$limit = $this->getValue("items_per_page");
    		$page = is_numeric($_GET["page"] ?? "!")? intval($_GET["page"]): 0;
    		print($media_admin->renderList($media_manager->list($pathinfo["relative"], $limit, $page), $pathinfo["relative"]));
	    }
	?>
</div>
