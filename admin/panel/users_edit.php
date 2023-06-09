<?php
require_once('../datatable-json/includes.php');

$fetchuser = ORM::for_table($config['db']['pre'].'user')->find_one($_GET['id']);

$fetchusername  = $fetchuser['username'];
$fetchuserpic     = $fetchuser['image'];

if($fetchuserpic == "")
    $fetchuserpic = "default_user.png";

?>
<header class="slidePanel-header overlay">
    <div class="overlay-panel overlay-background vertical-align">
        <div class="service-heading">
            <h2>Edit User</h2>
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
                    <form name="form2"  class="form form-horizontal" method="post" data-ajax-action="editUser" id="sidePanel_form">
                        <div class="form-body">
                            <input type="hidden" name="id" value="<?php echo $_GET['id']?>">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="col-md-2">
                                            <img src="../storage/profile/<?php echo $fetchuserpic; ?>" alt="<?php echo $fetchuser['name'];?>" style="width: 80px; border-radius: 50%">
                                        </div>
                                        <div class="col-md-10">
                                            <label class="control-label">Profile Picture</label>
                                            <input class="form-control input-sm" type="file" id="file" name="file" placeholder=".input-sm" />
                                            <span class="help-block"> Change Your Photo</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="exampleInputfullname">Full Name</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><i class="ion-person"></i></div>
                                            <input type="text" class="form-control" id="exampleInputfullname" placeholder="Full Name" name="name" value="<?php echo $fetchuser['name'];?>">
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">Status</label>
                                        <select class="form-control" name="status">
                                            <option value="0" <?php echo ($fetchuser['status'] == "0")? "selected" : "" ?>>Active</option>
                                            <option value="1" <?php echo ($fetchuser['status'] == "1")? "selected" : "" ?>>Verify</option>
                                            <option value="2" <?php echo ($fetchuser['status'] == "2")? "selected" : "" ?>>Ban</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">Gender</label>
                                        <select class="form-control" name="sex">
                                            <option value="Male" <?php if($fetchuser['sex'] == "Male") { echo "selected"; }?>>Male</option>
                                            <option value="Female" <?php if($fetchuser['sex'] == "Female") { echo "selected"; }?>>Female</option>
                                        </select>
                                        <span class="help-block"></span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">Country</label>
                                        <select class="form-control" name="country">
                                            <?php $country = get_country_list($fetchuser['country']);
                                            foreach ($country as $value){
                                                echo '<option value="'.$value['asciiname'].'" '.$value['selected'].'>'.$value['asciiname'].'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">About Us</label>
                                        <textarea name="about" class="form-control" ><?php echo $fetchuser['description'];?></textarea>
                                    </div>
                                </div>

                                <!-- User Membership -->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <h4 class="box-title m-b-0">User Membership</h4>
                                        <hr>

                                        <label for="exampleInputuname">Current Plan</label>
                                        <select class="form-control" name="current_plan">
                                            <option value="free" <?php echo ($fetchuser['group_id'] == "free")? "selected" : "" ?>>Free</option>
                                            <option value="trial" <?php echo ($fetchuser['group_id'] == "trial")? "selected" : "" ?>>Trial</option>
                                            <?php $rows = ORM::for_table($config['db']['pre'].'plans')
                                                ->where('status', '1')
                                                ->find_many();;
                                            foreach ($rows as $row){
                                                $selected = ($fetchuser['group_id'] == $row['id'])? "selected" : "";
                                                echo '<option value="'.$row['id'].'" '.$selected.'>'.$row['name'].'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputuname">Trial Done</label>
                                        <select class="form-control" name="plan_trial_done">
                                            <option value="1" <?php echo get_user_option($_GET['id'], 'package_trial_done',0) == '1'?'selected':''; ?>>Yes</option>
                                            <option value="0" <?php echo get_user_option($_GET['id'], 'package_trial_done',0) == '0'?'selected':''; ?>>No</option>
                                        </select>
                                    </div>

                                    <div class="form-group plan_expiration_date">
                                        <label for="exampleInputuname">Expiration Date</label>
                                        <?php

                                        $upgrades = ORM::for_table($config['db']['pre'].'upgrades')
                                            ->select('upgrade_expires')
                                            ->where('user_id',$_GET['id'])
                                            ->find_one();
                                        $default_expiration = date('Y-m-d', isset($upgrades['upgrade_expires'])?$upgrades['upgrade_expires']:time());
                                        ?>
                                        <input type="date" class="form-control" name="plan_expiration_date" value="<?php echo $default_expiration; ?>">
                                    </div>
                                </div>

                                <!-- Account Settings -->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <h4 class="box-title m-b-0">Account Setting</h4>
                                        <hr>

                                        <label for="exampleInputuname">Username</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><i class="ion-person"></i></div>
                                            <input type="text" class="form-control" id="exampleInputuname" placeholder="Username" name="username" value="<?php echo $fetchuser['username'];?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Email address</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><i class="ion-android-mail"></i></div>
                                            <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Email" name="email" value="<?php echo $fetchuser['email'];?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="exampleInputpwd2">New Password</label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><i class="ion-android-lock"></i></div>
                                            <input type="password" class="form-control" id="exampleInputpwd2" placeholder="Enter New Password" name="password">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="submit">
                        </div>

                    </form>
                </div>
            </div>
        </div>
        <!-- /.row -->
    </div>
</div>
<script>
    $('[name="current_plan"]').off().on('change',function () {
        if($(this).val() == 'free'){
            $('.plan_expiration_date').slideUp();
        }else{
            $('.plan_expiration_date').slideDown();
        }
    }).trigger('change');
</script>