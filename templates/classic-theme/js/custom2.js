var resizeId, lastModal, $ = jQuery.noConflict();
window.jQuery = $;
var defaultColor, customizerEnabled = 1;

function openModal(a, t) {
    $("body").append('<div class="modal modal-external fade" id="' + a + '" tabindex="-1" role="dialog" aria-labelledby="' + a + '"><i class="loading-icon fa fa-circle-o-notch fa-spin"></i></div>'), $("#" + a + ".modal").on("show.bs.modal", function () {
        var e = $(this);
        lastModal = e, $.ajax({
            url: "assets/external/" + t, method: "POST", data: {id: a}, success: function (a) {
                e.append(a), $("head").append($('<link rel="stylesheet" type="text/css">').attr("href", "assets/css/bootstrap-select.min.css")), $(".selectpicker").selectpicker(), e.find(".gallery").addClass("owl-carousel"), ratingPassive(".modal");
                var t = e.find(".gallery img:first")[0];
                t ? $(t).load(function () {
                    timeOutActions(e)
                }) : timeOutActions(e), socialShare(), e.on("hidden.bs.modal", function () {
                    $(lastClickedMarker).removeClass("active"), $(".pac-container").remove(), e.remove()
                })
            }, error: function (a) {
                console.log(a)
            }
        })
    })
}

function bgTransfer() {
    viewport.is("xs"), $(".bg-transfer").each(function () {
        $(this).css("background-image", "url(" + $(this).find("img").attr("src") + ")")
    })
}

function ratingPassive(a) {
    $(a).find(".rating-passive").each(function () {
        for (var a = 0; a < 5; a++) a < $(this).attr("data-rating") ? $(this).find(".stars").append("<figure class='active fa fa-star'></figure>") : $(this).find(".stars").append("<figure class='fa fa-star'></figure>")
    })
}

function initializeFitVids() {
    $(".video").length > 0 && $(".video").fitVids()
}

function initializeOwl() {
    $(".owl-carousel").length && $(".owl-carousel").each(function () {
        var a = parseInt($(this).attr("data-owl-items"), 10);
        a || (a = 1);
        var t = parseInt($(this).attr("data-owl-nav"), 2);
        t || (t = 0);
        var e = parseInt($(this).attr("data-owl-dots"), 2);
        e || (e = 0);
        var i = parseInt($(this).attr("data-owl-center"), 2);
        i || (i = 0);
        var s = parseInt($(this).attr("data-owl-loop"), 2);
        s || (s = 0);
        var r = parseInt($(this).attr("data-owl-margin"), 2);
        r || (r = 0);
        var n = parseInt($(this).attr("data-owl-auto-width"), 2);
        n || (n = 0);
        var o = $(this).attr("data-owl-nav-container");
        o || (o = 0);
        var l = $(this).attr("data-owl-autoplay");
        l || (l = 0);
        var c = $(this).attr("data-owl-fadeout");
        if (c = c ? "fadeOut" : 0, $("body").hasClass("rtl")) var d = !0; else d = !1;
        $(this).owlCarousel({
            navContainer: o,
            animateOut: c,
            autoplaySpeed: 2e3,
            autoplay: l,
            autoheight: 1,
            center: i,
            loop: s,
            margin: r,
            autoWidth: n,
            items: a,
            nav: t,
            dots: e,
            autoHeight: !0,
            rtl: d,
            navText: []
        })
    })
}

function doneResizing() {
    for (var a = $(".container"), t = 0; t < a.length; t++) equalHeight(a);
    responsiveNavigation()
}

function responsiveNavigation() {
    viewport.is("xs") && $("body").addClass("nav-btn-only"), $("body").hasClass("nav-btn-only") && ($(".primary-nav .has-child").children("a").attr("data-toggle", "collapse"), $(".primary-nav .has-child").find(".nav-wrapper").addClass("collapse"), $(".mega-menu .heading").each(function (a) {
        $(this).wrap("<a href='#mega-menu-collapse-" + a + "'></a>"), $(this).parent().attr("data-toggle", "collapse"), $(this).parent().addClass("has-child"), $(this).parent().attr("aria-controls", "mega-menu-collapse-" + a)
    }), $(".mega-menu ul").each(function (a) {
        $(this).attr("id", "mega-menu-collapse-" + a), $(this).addClass("collapse")
    }))
}

function equalHeight(a) {
    if (!viewport.is("xs")) {
        var t, e = 0, i = 0, s = new Array;
        $(a).find(".equal-height").each(function () {
            if (t = $(this), $(t).height("auto"), topPostion = t.position().top, i != topPostion) {
                for (currentDiv = 0; currentDiv < s.length; currentDiv++) s[currentDiv].height(e);
                s.length = 0, i = topPostion, e = t.height(), s.push(t)
            } else s.push(t), e = e < t.height() ? t.height() : e;
            for (currentDiv = 0; currentDiv < s.length; currentDiv++) s[currentDiv].height(e)
        })
    }
}

$(document).ready(function (a) {
    "use strict";
    if (a("body").hasClass("navigation-fixed") ? fixedNavigation(!0) : fixedNavigation(!1), a(".tse-scrollable").length && a(".tse-scrollable").TrackpadScrollEmulator(), a(".date-picker").length && a(".date-picker").datepicker(), a(".count-down").length) {
        var t = new Date;
        a(".count-down").countdown({
            until: new Date(t.getFullYear(), t.getMonth(), t.getDate() + 2),
            padZeroes: !0,
            format: "HMS"
        })
    }
    if (a("select").on("rendered.bs.select", function () {
        a("head").append(a('<link rel="stylesheet" type="text/css">').attr("href", "assets/css/bootstrap-select.min.css")), viewport.is("xs") || a(".search-form.vertical").css("top", a(".quickad-section").height() / 2 - a(".search-form .wrapper").height() / 2), trackpadScroll("initialize")
    }), viewport.is("xs") || (a(".search-form.vertical").css("top", a(".quickad-section").height() / 2 - a(".search-form .wrapper").height() / 2), trackpadScroll("initialize")), a('.main-nav a[href^="#"], a[href^="#"].scroll').on("click", function (t) {
        t.preventDefault();
        var e = this.hash, i = a(e);
        a("html, body").stop().animate({scrollTop: i.offset().top}, 2e3, "swing", function () {
            window.location.hash = e
        })
    }), a(document).on("show.bs.modal", ".modal", function () {
        var t = 1040 + 10 * a(".modal:visible").length;
        a(this).css("z-index", t), setTimeout(function () {
            a(".modal-backdrop").not(".modal-stack").css("z-index", t - 1).addClass("modal-stack")
        }, 0)
    }), a(document).on("click", function (t) {
        "controls-more" == t.target.className ? (a(".controls-more.show").removeClass("show"), a(t.target).addClass("show")) : a(".controls-more.show").each(function () {
            a(this).removeClass("show")
        })
    }), a(".nav-btn").on("click", function () {
        a(this).toggleClass("active"), a(".primary-nav").toggleClass("show")
    }), a("input[type=file].with-preview").length && a("input.file-upload-input").MultiFile({list: ".file-upload-previews"}), a(".ui-slider").length > 0 && a(".ui-slider").each(function () {
        if (a("body").hasClass("rtl")) var t = "rtl"; else t = "ltr";
        var e;
        e = a(this).attr("data-step") ? parseInt(a(this).attr("data-step")) : 10;
        var i = a(this).attr("id"), s = (a("#" + i), parseInt(a(this).attr("data-value-min"))),
            r = parseInt(a(this).attr("data-value-max")), n = parseInt(a(this).attr("data-start-min")),
            o = parseInt(a(this).attr("data-start-max"));
        a(this).noUiSlider({
            start: [n, o],
            connect: !0,
            direction: t,
            range: {min: s, max: r},
            step: e
        }), "price" == a(this).attr("data-value-type") ? "before" == a(this).attr("data-currency-placement") ? (a(this).Link("lower").to(a(this).children(".values").children(".value-min"), null, wNumb({
            prefix: a(this).attr("data-currency"),
            decimals: 0,
            thousand: "."
        })), a(this).Link("upper").to(a(this).children(".values").children(".value-max"), null, wNumb({
            prefix: a(this).attr("data-currency"),
            decimals: 0,
            thousand: "."
        }))) : "after" == a(this).attr("data-currency-placement") && (a(this).Link("lower").to(a(this).children(".values").children(".value-min"), null, wNumb({
            postfix: a(this).attr("data-currency"),
            decimals: 0,
            thousand: " "
        })), a(this).Link("upper").to(a(this).children(".values").children(".value-max"), null, wNumb({
            postfix: a(this).attr("data-currency"),
            decimals: 0,
            thousand: " "
        }))) : (a(this).Link("lower").to(a(this).children(".values").children(".value-min"), null, wNumb({decimals: 0})), a(this).Link("upper").to(a(this).children(".values").children(".value-max"), null, wNumb({decimals: 0})))
    }), a(".calendar").length) {
        for (var e = (t = new Date).getMonth(), i = 1; i <= 12; i++) a(".calendar-wrapper").append('<div id="month_' + i + '" class="month"></div>'), a("#month_" + i).zabuto_calendar({
            ajax: {
                url: "assets/php/calendar.php",
                modal: !0
            },
            action: function () {
                var t = a("#" + this.id).data("date");
                return a("#modal-date").val(t), checkDate(this.id)
            },
            language: "en",
            month: i,
            show_previous: !1,
            show_next: !1,
            today: !0,
            nav_icon: {prev: '<i class="arrow_left"></i>', next: '<i class="arrow_right"></i>'}
        });
        a(".calendar-wrapper").owlCarousel({items: 2, nav: !0, autoHeight: !0, navText: [], startPosition: e})
    }
    a(".form-email .btn[type='submit']").on("click", function () {
        var t = a(this), e = a(this).closest("form");
        t.prepend("<div class='status'></div>"), e.validate({
            submitHandler: function () {
                return a.post("assets/external/email.php", e.serialize(), function (a) {
                    t.find(".status").append(a), e.addClass("submitted")
                }), !1
            }
        })
    }), equalHeight(".container"), ratingPassive("body"), bgTransfer(), responsiveNavigation()
}), $(window).load(function () {
    initializeOwl(), $(".load-wrapp").fadeOut(100)
}), $(window).resize(function () {
    clearTimeout(resizeId), resizeId = setTimeout(doneResizing, 250), responsiveNavigation()
});
var viewport = function () {
    var a = ["xs", "sm", "md", "lg"], t = function () {
        return window.getComputedStyle(document.body, ":before").content.replace(/"/g, "")
    };
    return {
        is: function (e) {
            if (-1 == a.indexOf(e)) throw"no valid viewport name given";
            return t() == e
        }, isEqualOrGreaterThan: function (e) {
            if (-1 == a.indexOf(e)) throw"no valid viewport name given";
            return a.indexOf(t()) >= a.indexOf(e)
        }
    }
}();

function rating(a) {
    a || (a = ""), $.each($(a + " .star-rating"), function (a) {
        $(this).append('<span class="stars"><i class="fa fa-star s1" data-score="1"></i><i class="fa fa-star s2" data-score="2"></i><i class="fa fa-star s3" data-score="3"></i><i class="fa fa-star s4" data-score="4"></i><i class="fa fa-star s5" data-score="5"></i><i class="fa fa-star s6" data-score="6"></i><i class="fa fa-star s7" data-score="7"></i><i class="fa fa-star s8" data-score="8"></i><i class="fa fa-star s9" data-score="9"></i><i class="fa fa-star s10" data-score="10"></i></span>'), $(this).hasClass("active") && $(this).append('<input readonly hidden="" name="score_' + $(this).attr("data-name") + '" id="score_' + $(this).attr("data-name") + '">');
        for (var t = $(this).attr("data-rating"), e = 0; e < t; e++) {
            var i = e + 1;
            console.log("a"), $(this).children(".stars").children(".s" + i).addClass("active")
        }
    });
    var t = $(".star-rating.active i");
    t.mouseenter(function () {
        for (var a = 0; a < $(this).attr("data-score"); a++) {
            var t = a + 1;
            $(this).parent().children(".s" + t).addClass("hover")
        }
    }).mouseleave(function () {
        for (var a = 0; a < $(this).attr("data-score"); a++) {
            var t = a + 1;
            $(this).parent().children(".s" + t).removeClass("hover")
        }
    }), t.on("click", function () {
        $(this).parents(".star-rating").find("input").val($(this).attr("data-score")), $(this).parent().children(".fa").removeClass("active");
        for (var a = 0; a < $(this).attr("data-score"); a++) {
            var t = a + 1;
            $(this).parent().children(".s" + t).addClass("active")
        }
        return !1
    })
}

function initializeReadMore() {
    $.ajax({
        type: "GET", url: "assets/js/readmore.min.js", success: function () {
            var a, t = $(".read-more");
            a = t.attr("data-collapse-height") ? parseInt(t.attr("data-collapse-height"), 10) : 55, t.readmore({
                speed: 500,
                collapsedHeight: a,
                blockCSS: "display: inline-block; width: auto; min-width: 120px;",
                moreLink: '<a href="#" class="btn btn-primary btn-xs btn-light-frame btn-framed btn-rounded">More<i class="icon_plus"></i></a>',
                lessLink: '<a href="#" class="btn btn-primary btn-xs btn-light-frame btn-framed btn-rounded">Less<i class="icon_minus-06"></i></a>'
            })
        }, dataType: "script", cache: !0
    })
}

function fixedNavigation(a) {
    if (1 == a) {
        $("body").addClass("navigation-fixed");
        var t = $("#page-header").height();
        $("#page-header").css("position", "fixed"), $("#page-content").css({
            "-webkit-transform": "translateY(" + t + "px)",
            "-moz-transform": "translateY(" + t + "px)",
            "-ms-transform": "translateY(" + t + "px)",
            "-o-transform": "translateY(" + t + "px)",
            transform: "translateY(" + t + "px)"
        })
    } else 0 == a && ($("body").removeClass("navigation-fixed"), $("#page-header").css("position", "relative"), $("#page-content").css({
        "-webkit-transform": "translateY(0px)",
        "-moz-transform": "translateY(0px)",
        "-ms-transform": "translateY(0px)",
        "-o-transform": "translateY(0px)",
        transform: "translateY(0px)"
    }))
}

$("[data-show-after-scroll]").each(function () {
    var a = $(this), t = a.attr("data-show-after-scroll");
    $(this).offset().top, $(window).scroll(function () {
        $(window).scrollTop() >= t ? a.addClass("show") : a.removeClass("show")
    })
});
function trackpadScroll(a) {
    "initialize" == a ? $(".results-wrapper").find("form").length && $(".results-wrapper .results").height($(".results-wrapper").height() - $(".results-wrapper .form")[0].clientHeight) : "recalculate" == a && setTimeout(function () {
        $(".tse-scrollable").length && $(".tse-scrollable").TrackpadScrollEmulator("recalculate")
    }, 1e3)
}

$("#address-autocomplete").on("keyup keypress", function (a) {
    if (13 === (a.keyCode || a.which)) return a.preventDefault(), !1
}), $(document).ready(function () {
    $(".live-search-box").on("keyup", function () {
        var a = $(this).val().toLowerCase();
        $("#js-table-list tr").each(function () {
            $(this).filter("[data-search-term *= " + a + "]").length > 0 || a.length < 1 ? ($(this).show(), $("#norecord").hide()) : ($(this).hide(), 0 == $(this).filter("[data-search-term *= " + a + "]").length && $("#norecord").show())
        })
    })
});