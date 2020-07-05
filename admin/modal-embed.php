<?php
/*
 |  Media       The advanced Media & File Manager for Bludit
 |  @file       ./admin/modal-embed.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.2.0 [0.1.0] - Beta
 |
 |  @website    https://github.com/pytesNET/media
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2019 - 2020 pytesNET <info@pytes.net>
 */
?>
<div id="media-embed-file" class="media-modal modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php bt_e("Advanced Embed"); ?></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="media-embed-file-form" method="post" action="" data-media-form="embed">
                    <input type="hidden" name="path" value="<?php echo $pathinfo["slug"]; ?>" />
                    <input type="hidden" name="mime" value="" data-media-value="mime" />
                    <input type="hidden" name="source" value="" data-media-value="source" />
                    <input type="hidden" name="action" value="embed" />

                    <div class="form-group row" data-embed-field="title">
                        <label class="col-4"><?php bt_e("Title"); ?></label>
                        <div class="col-8">
                            <input type="text" name="title" value="" placeholder="" class="form-control form-control-sm" data-media-value="name" />
                        </div>
                    </div>

                    <div class="form-group row" data-embed-field="alt">
                        <label class="col-4"><?php bt_e("Alternative Text"); ?></label>
                        <div class="col-8">
                            <input type="text" name="alt" value="" placeholder="" class="form-control form-control-sm" data-media-value="name" />
                        </div>
                    </div>

                    <div class="form-group row" data-embed-field="width">
                        <label class="col-4"><?php bt_e("Width"); ?></label>
                        <div class="col-4">
                            <input type="text" name="width" value="" placeholder="" class="form-control form-control-sm" />
                        </div>
                    </div>

                    <div class="form-group row" data-embed-field="height">
                        <label class="col-4"><?php bt_e("Height"); ?></label>
                        <div class="col-4">
                            <input type="text" name="height" value="" placeholder="" class="form-control form-control-sm" />
                        </div>
                    </div>

                    <div class="form-group row" data-embed-field="position">
                        <label class="col-4"><?php bt_e("Position"); ?></label>
                        <div class="col-4">
                            <select name="position" class="custom-select custom-select-sm">
                                <option value=""><?php bt_e("Default"); ?></option>
                                <option value="left"><?php bt_e("Float Left"); ?></option>
                                <option value="right"><?php bt_e("Float Right"); ?></option>
                                <option value="center"><?php bt_e("Center Block"); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row" data-embed-field="autoplay">
                        <label class="col-4"><?php bt_e("AutoPlay"); ?></label>
                        <div class="col-8">
                            <div class="custom-control custom-switch">
                                <input id="embed-auotplay" type="checkbox" name="autoplay" value="1" class="custom-control-input" />
                                <label for="embed-auotplay" class="custom-control-label"><?php bt_e("Enable AutoPlay"); ?></label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row" data-embed-field="loop">
                        <label class="col-4"><?php bt_e("Loop"); ?></label>
                        <div class="col-8">
                            <div class="custom-control custom-switch">
                                <input id="embed-loop" type="checkbox" name="loop" value="1" class="custom-control-input" />
                                <label for="embed-loop" class="custom-control-label"><?php bt_e("Loop File"); ?></label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row" data-embed-field="muted">
                        <label class="col-4"><?php bt_e("Muted"); ?></label>
                        <div class="col-8">
                            <div class="custom-control custom-switch">
                                <input id="embed-muted" type="checkbox" name="muted" value="1" class="custom-control-input" />
                                <label for="embed-muted" class="custom-control-label"><?php bt_e("Start Muted"); ?></label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mb-0 pb-0">
                        <div class="col-12 text-right mb-0 pb-0">
                            <button type="submit" name="action" value="embed" class="btn btn-sm btn-primary"><?php bt_e("Embed File"); ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
