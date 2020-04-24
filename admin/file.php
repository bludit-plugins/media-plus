<?php
/*
 |  Media       An advanced Media & File Manager for Bludit
 |  @file       ./admin/file.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.0 [0.1.0] - Alpha
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

<div class="media-actions row">
    <div class="col-sm">
        <?php if(!empty($relative)) { ?>
            <a href="<?php echo $media_admin->buildURL("media", ["path" => MediaManager::slug($relative)]); ?>" class="btn btn-success" data-media-action="back">
				<span class="fa fa-arrow-left"></span> <?php paw_e("Go Back"); ?>
			</a>
        <?php } ?>
    </div>

    <div class="col-sm text-right">
		<?php
			$delete = $media_admin->buildURL("media/delete", [
				"nonce"         => $security->getTokenCSRF(),
				"media_action"  => "delete",
				"file"          => $file
			]);
		?>
        <a href="<?php echo $delete; ?>" class="btn btn-danger">
            <span class="fa fa-trash"></span><?php paw_e("Delete File"); ?>
        </a>
    </div>
</div>

<nav class="media-toolbar <?php echo PAW_MEDIA_PLUS? "media-toolbar-plus": ""; ?> mt-4">
    <ol class="breadcrumb">
		<?php
			?><li class="breadcrumb-item"><a href="<?php echo $media_admin->buildURL("media"); ?>" data-media-action="list">root</a></li><?php

			// Breadbrumbs || Search
            $sub = [];
            $parts = explode(DS, trim($relative, DS));
            $count = 0;
            foreach($parts AS $folder) {
                $sub[] = $folder;
                $crumb = $media_admin->buildURL("media", ["path" => implode("/", $sub)]);
                ?><li class="breadcrumb-item"><a href="<?php echo $crumb; ?>" data-media-action="list"><?php echo $folder; ?></a></li><?php
            }
		?>
		<li class="breadcrumb-item active"><?php echo basename($file); ?></li>
    </ol>

	<?php if(PAW_MEDIA_PLUS) { ?>
        <?php
			$relative = MediaManager::relative($file);
            $favorite = $media_admin->buildURL("media/favorite", [
                "nonce"         => $security->getTokenCSRF(),
                "media_action"  => "favorite",
                "path"          => $relative
            ]);
        ?>
		<div class="tools btn-group">
	        <a href="<?php echo $favorite; ?>" class="btn btn-outline-danger <?php echo $media_admin->isFavorite($file)? "active": ""; ?>">
	            <span class="fa <?php echo $media_admin->isFavorite($file)? "fa-heart": "fa-heart-o"; ?>"></span>
	        </a>
		</div>
	<?php } ?>

</nav>

<?php print($media_admin->renderFile($file)); ?>
