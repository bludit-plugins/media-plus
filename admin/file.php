<?php
/*
 |  Media       The advanced Media & File Manager for Bludit
 |  @file       ./admin/file.php
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
        <?php if(!empty($relative)) { ?>
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
		
    </div>
</div>

<nav class="media-toolbar <?php echo PAW_MEDIA_PLUS? "media-toolbar-plus": ""; ?> mt-4">
    <ol class="breadcrumb">
		<?php
			?><li class="breadcrumb-item"><a href="<?php echo $media_admin->buildURL("media"); ?>" data-media-action="list">root</a></li><?php

			// Breadbrumbs || Search
            $sub = [];
            $count = 0;
            foreach(explode(DS, dirname($relative)) AS $folder) {
                $sub[] = $folder;
                $crumb = $media_admin->buildURL("media", ["path" => implode("/", $sub)]);
                ?><li class="breadcrumb-item"><a href="<?php echo $crumb; ?>" data-media-action="list"><?php echo $folder; ?></a></li><?php
            }
		?>
		<li class="breadcrumb-item active"><?php echo $basename; ?></li>
    </ol>

	<?php if(PAW_MEDIA_PLUS) { ?>
        <?php
            $favorite = $media_admin->buildURL("media/favorite", [
                "nonce"         => $security->getTokenCSRF(),
                "media_action"  => "favorite",
                "path"          => $relative
            ]);
        ?>
		<div class="tools btn-group">
	        <a href="<?php echo $favorite; ?>" class="btn btn-outline-danger <?php echo $media_admin->isFavorite($relative)? "active": ""; ?>" data-media-action="favorite">
	            <span class="fa <?php echo $media_admin->isFavorite($relative)? "fa-heart": "fa-heart-o"; ?>"></span>
	        </a>
		</div>
	<?php } ?>

</nav>

<?php print($media_admin->renderFile($absolute)); ?>
