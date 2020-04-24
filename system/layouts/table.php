<?php
declare(strict_types=1);
/*
 |  Media       An advanced Media & File Manager for Bludit
 |  @file       ./system/layouts/table.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.0 [0.1.0] - Alpha
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
?>
<table class="table mt-4 media-list" data-action="<?php echo $this->buildURL("media/upload") ?>" data-path="<?php echo MediaManager::slug($path); ?>" data-token="<?php echo $security->getTokenCSRF(); ?>">
    <thead>
        <tr>
            <th width="30px" class="th-checkbox" scope="col"></th>
            <th width="auto" class="th-filename" scope="col"><?php paw_e("Name"); ?></th>
            <?php if(PAW_MEDIA_PLUS) { ?>
                <th width="5%" class="th-favorite text-center" scope="col"><span class="fa fa-heart"></span></th>
            <?php } ?>
            <th width="10%" class="th-filetype text-center" scope="col"><?php paw_e("Type"); ?></th>
            <th width="10%" class="th-filesize text-center" scope="col"><?php paw_e("Filesize"); ?></th>
            <th width="25%" class="th-actions text-center" scope="col"></th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th class="text-muted text-center text-uppercase text-600" scope="col" colspan="6" style="font-weight:600;">
                <?php paw_e("Drag 'n' Drop Items on the list above to upload"); ?>
            </th>
        </tr>
    </tfoot>
    <tbody>
        <tr class="media-empty <?php echo empty($files)? "": "d-none"; ?>">
            <td class="td-empty text-center p-5" colspan="6"><i><?php paw_e("No Items available"); ?></i></td>
        </tr>

        <?php
            if(!empty($files)) {
                foreach($files AS $file => $real) {
                    print($this->renderItem($file, $real));
                }
            }
        ?>
    </tbody>
</table>
