<?php
/*
 |  Media       The advanced Media & File Manager for Bludit
 |  @file       ./admin/list.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.2.0 [0.1.0] - Beta
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
?>
<h2 class="media-title mt-0 mb-3">
	<span class="fa fa-image"></span><span>Media Manager</span>
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
    <div class="col-sm">
        <?php if(!empty($relative) || trim($media_admin->path, "/") === "media/search") { ?>
            <?php
				if(strpos($slug, "/") !== false) {
					$back = substr($slug, 0, strrpos($slug, "/"));
				} else {
					$back = "";
				}
            ?>
            <a href="<?php echo $media_admin->buildURL("media", ["path" => $back]); ?>" class="btn btn-success" data-media-action="list">
				<span class="fa fa-arrow-left"></span> <?php bt_e("Go Back"); ?>
			</a>
        <?php } ?>
    </div>

    <div class="col-sm text-right">
        <div class="btn-group">
			<a href="#media-create-folder" class="btn btn-light" data-toggle="modal">
				<span class="fa fa-folder"></span> <?php bt_e("Create Folder"); ?>
			</a>
            <button class="btn btn-light media-trigger-upload clickable">
				<span class="fa fa-upload"></span> <?php bt_e("Upload"); ?>
			</button>
        </div>

        <div class="btn-group">
			<?php $href = $media_admin->buildURL("media", ["layout" => "table"], false); ?>
            <a href="<?php echo $href; ?>" class="btn btn-light <?php echo $this->getValue("layout") === "table"? "active": ""; ?>" data-media-action="list" data-media-layout="table">
				<svg class="media-icon"><use href="#octicon-three-bars" /></svg>
            </a>

			<?php $href = $media_admin->buildURL("media", ["layout" => "grid"], false); ?>
            <a href="<?php echo $href; ?>" class="btn btn-light <?php echo $this->getValue("layout") === "grid"? "active": ""; ?>" data-media-action="list" data-media-layout="grid">
				<svg class="media-icon"><use href="#octicon-display-grid" /></svg>
            </a>
        </div>
    </div>
</div>

<?php
	if(method_exists($media_admin, "renderToolbar")) {
		$tools = $media_admin->renderToolbar();
	}
?>
<nav class="media-toolbar <?php echo isset($tools)? "media-toolbar-plus": ""; ?> mt-4">
    <ol class="breadcrumb">
		<?php
			$search = (PAW_MEDIA_PLUS && trim($media_admin->path, "/") === "media/search");

			// Root Crumb
			if(empty($relative) && !$search) {
				?><li class="breadcrumb-item"><a href="<?php echo $media_admin->buildURL("media"); ?>" data-media-action="list">root</a></li><?php
			} else {
				?><li class="breadcrumb-item"><a href="<?php echo $media_admin->buildURL("media"); ?>" data-media-action="list">root</a></li><?php
			}

			// Breadbrumbs || Search
			if(!$search) {
	            $sub = [];
	            $parts = explode(DS, trim($relative, DS));
	            $count = 0;
	            foreach($parts AS $folder) {
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
		}
	?>
</nav>

<div class="media-list-upload" />
	<?php
		$limit = $this->getValue("items_per_page");
		$page = is_numeric($_GET["page"] ?? "!")? intval($_GET["page"]): 0;
		print($media_admin->renderList($media_manager->list($relative, $limit, $page), $relative));
	?>
</div>
