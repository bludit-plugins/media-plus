<?php
declare(strict_types=1);
/*
 |  Media       The advanced Media & File Manager for Bludit
 |  @file       ./system/layouts/grid.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.2.0 [0.1.0] - Beta
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
?>
<div class="row media-list" data-action="<?php echo $this->buildURL("media/upload") ?>" data-path="<?php echo $slug; ?>" data-token="<?php echo $security->getTokenCSRF(); ?>">
    <div class="media-empty col col-6 mx-auto <?php echo empty($files)? "": "d-none"; ?>">
        <div class="td-empty text-center p-5 bg-light rounded"><i><?php bt_e("No Items available"); ?></i></div>
    </div>

    <?php
        if(!empty($files)) {
            foreach($files AS $file => $real) {
                print($this->renderItem($file, $real));
            }
        }
    ?>
</div>
