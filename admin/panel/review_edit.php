<?php
require_once('../datatable-json/includes.php');

$info = ORM::for_table($config['db']['pre'].'reviews')
    ->where('reviewID', escape_html($_GET['id']))
    ->find_one();
if ($info['rating'] == 1) { $rating_selected_1 = 'selected="selected"'; } else { $rating_selected_1 = ''; }
if ($info['rating'] == 2) { $rating_selected_2 = 'selected="selected"'; } else { $rating_selected_2 = ''; }
if ($info['rating'] == 3) { $rating_selected_3 = 'selected="selected"'; } else { $rating_selected_3 = ''; }
if ($info['rating'] == 4) { $rating_selected_4 = 'selected="selected"'; } else { $rating_selected_4 = ''; }
if ($info['rating'] == 5) { $rating_selected_5 = 'selected="selected"'; } else { $rating_selected_5 = ''; }
if ($info['publish'] == 1) { $publish = 'checked="checked"'; } else { $publish = ''; }

$comments = escape_html($info['comments']);

?>

<header class="slidePanel-header overlay">
    <div class="overlay-panel overlay-background vertical-align">
        <div class="service-heading">
            <h2>Edit Review</h2>
        </div>
        <div class="slidePanel-actions">
            <div class="btn-group-flat">
                <button type="button" class="btn btn-floating btn-warning btn-sm waves-effect waves-float waves-light margin-right-10" id="post_sidePanel_data"><i class="icon ion-android-done" aria-hidden="true"></i></button>
                <button type="button" class="btn btn-pure btn-inverse slidePanel-close icon ion-android-close font-size-20" aria-hidden="true"></button>
            </div>
        </div>
    </div>
</header>
<div class="slidePanel-inner">
    <div class="panel-body">
        <!-- /.row -->
        <div class="row">
            <div class="col-sm-12">

                <div class="white-box">
                    <div id="post_error"></div>
                    <form name="form2"  class="form form-horizontal" method="post" data-ajax-action="editReview" id="sidePanel_form">
                        <div class="form-body">
                            <input type="hidden" name="id" value="<?php echo $_GET['id']?>">
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Rating *</label>
                                <div class="col-sm-6">
                                    <select id="rating-new" name="rating-new" class="form-control">
                                        <option value="1" <?php echo $rating_selected_1; ?>>1 Star</option>
                                        <option value="2" <?php echo $rating_selected_2; ?>>2 Stars</option>
                                        <option value="3" <?php echo $rating_selected_3; ?>>3 Stars</option>
                                        <option value="4" <?php echo $rating_selected_4; ?>>4 Stars</option>
                                        <option value="5" <?php echo $rating_selected_5; ?>>5 Stars</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Review *</label>
                                <div class="col-sm-6">
                                    <textarea class="form-control" name="comments" required=""><?php echo $comments; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Publish</label>
                                <div class="col-sm-6">
                                    <label class="css-input switch switch-sm switch-success">
                                        <input  name="publish" type="checkbox" value="1" <?php if($info['publish'] == '1') echo "checked"; ?> /><span></span>
                                    </label>
                                </div>
                            </div>

                        </div>

                    </form>
                </div>
            </div>
        </div>
        <!-- /.row -->
    </div>
</div>
