/**
 * For Login With Ajax.
 * 23-08-2020
 */

"use strict"
var siteurl = '{SITE_URL}';
var _w = screen.width - (screen.width*25/100);
var _h = screen.height - (screen.height*25/100);
var _left = (screen.width / 2) - (_w / 2);
var _top = (screen.height / 2) - (_h / 2);

$(window).on("load", function() {
    $('.facebook-login').on('click',function(e){
        e.preventDefault();
        var newWin = window.open(siteurl+"includes/social_login/facebook/index.php", "fblogin", 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, copyhistory=no,display=popup, width=' + _w + ', height=' + _h + ', top=' + _top + ', left=' + _left);
    });

    $('.gmail-login').on('click',function(e){
        e.preventDefault();
        var newWin = window.open(siteurl+"includes/social_login/google/index.php", "gmlogin", 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, copyhistory=no, width=' + _w + ', height=' + _h + ', top=' + _top + ', left=' + _left);
    });

    $('.forgot-page').on('click',function(e){
        e.preventDefault();
        $('.forny-content').removeClass('show-it').addClass('hide-it');
        $('#forgot-form').addClass('show-it').removeClass('hide-it');
    });
    $('.login-page').on('click',function(e){
        e.preventDefault();
        $('.forny-content').removeClass('show-it').addClass('hide-it');
        $('#login-form').addClass('show-it').removeClass('hide-it');
    });
    $('.signup-page').on('click',function(e){
        e.preventDefault();
        $('.forny-content').removeClass('show-it').addClass('hide-it');
        $('#signup-form').addClass('show-it').removeClass('hide-it');
    });

    $('.mobile-page').on('click',function(e){
        e.preventDefault();
        $('.forny-content').removeClass('show-it').addClass('hide-it');
        $('#mobile-form').addClass('show-it').removeClass('hide-it');
    });
});

$(document).ready(function (e) {
    $("#login").on('click',function (e) {
        e.preventDefault();
        $(this).addClass('ajax-loader');
        $("#login-status").html('<span class="text-primary">'+LANG_AUTHENTICATING+'</span>');
        var action = $("#lg-form").attr('action');
        if($('#remember').is(":checked"))
            var remember = '1';
        else
            var remember = '0';
        console.log(remember);

        var form_data = {
            action: action,
            username: $("#username").val(),
            password: $("#password").val(),
            remember: remember,
            is_ajax: 1
        };
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: form_data,
            success: function (response) {
                $("#login").removeClass('ajax-loader');
                if (response == "success") {
                    $("#lg-form").slideUp('slow', function () {
                        $("#login-status").html('<span class="text-success">'+LANG_LOGGED_IN_SUCCESS+'</span>');
                        location.reload();
                    });
                }
                else {
                    $("#login-status").html('<span class="text-danger">'+response+'</span>');
                    $("#signup").removeClass('ajax-loader');
                }
            }
        });
        return false;
    });

    $($('[name="signup-form"]')).on('submit', function(e) {
        e.preventDefault();
        $("#signup").addClass('ajax-loader');
        $("#signup-status").html('<span class="text-primary">'+LANG_AUTHENTICATING+'</span>');
        var form_data = $(this).serialize();
        var action = $("#reg-form").attr('action');
        $.ajax({
            type: "POST",
            url: ajaxurl+'?action='+action,
            data: form_data,
            success: function (response) {
                if (response == "success") {
                    $("#reg-form").slideUp('slow', function () {
                        $("#signup-status").html('<span class="text-success">'+LANG_LOGGED_IN_SUCCESS+'</span>');
                        location.reload();
                    });
                }
                else {
                    $("#signup-status").html('<span class="text-danger">'+response+'</span>');
                    $("#signup").removeClass('ajax-loader');
                }
            }
        });
        return false;
    });

    $($('[name="reset_pass_form"]')).on('submit', function(e) {
        e.preventDefault();
        $("#forgot").addClass('ajax-loader');
        $("#forgot-status").html('<span class="text-primary">'+LANG_AUTHENTICATING+'</span>');
        var form_data = $(this).serialize();
        var action = $("#reset_pass_form").attr('action');
        $.ajax({
            type: "POST",
            url: ajaxurl+'?action='+action,
            data: form_data,
            success: function (response) {
                if (response == "success") {
                    $("#reset_pass_form").slideUp('slow', function () {
                        $("#forgot-status").html('<span class="text-success">'+LANG_CHECKEMAILFORGOT+'</span>');
                        $("#forgot").removeClass('ajax-loader');
                    });
                }
                else {
                    $("#forgot-status").html('<span class="text-danger">'+response+'</span>');
                    $("#forgot").removeClass('ajax-loader');
                }
            }
        });
        return false;
    });

    $($('[name="pv-form"]')).on('submit', function (e){
        e.preventDefault();
        $("#mobile-submit").addClass('ajax-loader');
        var action = $("#pv-form").attr('action');
        var mobile_no = $("#verify-mobile").val();
        var form_data = $(this).serialize();
        $.ajax({
            type: "POST",
            url: ajaxurl+'?action='+action,
            data: form_data,
            success: function (response) {
                if (response == "success") {
                    $('.otp_mobile').html(mobile_no);
                    $('.reset-form')
                        .removeClass('d-block')
                        .addClass('d-none');
                    $('.reset-confirmation').addClass('d-block');

                    $('.reset-confirmation .otp_mobile_no').val(mobile_no);
                }
                else {
                    $("#mobile-status").html('<span class="status-not-available text-danger">'+response+'</span>');
                    $("#mobile-submit").removeClass('ajax-loader');
                }
            }
        });
        return false;
    });

    $($('[name="otp-form"]')).on('submit', function (e){
        e.preventDefault();
        $("#otp-submit").addClass('ajax-loader');
        var action = $("#otp-form").attr('action');
        var form_data = $(this).serialize();
        $.ajax({
            type: "POST",
            url: ajaxurl+'?action='+action,
            data: form_data,
            success: function (response) {
                if (response == "success") {
                    console.log(response);
                    $(".otp-form-content").slideUp('slow', function () {
                        $("#otp-status").html('<span class="text-success">'+LANG_PHONE_VERIFIED_MSG+'</span>');
                        location.reload();
                    });
                }
                else {
                    $("#otp-status").html('<span class="text-danger">'+response+'</span>');
                    $("#otp-submit").removeClass('ajax-loader');
                }
            }
        });
        return false;
    });
});


