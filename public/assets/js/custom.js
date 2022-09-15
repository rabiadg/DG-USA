var sw = $(window).width();
var sh = $(window).height();


// Font Resizer-------------------Start
function fontResizer() {
    var perc = parseInt(sw) / 118.9375;
    $("body").css("font-size", perc);
}
// Font Resizer-------------------End

$(window).on("load", function () {

    setTimeout(function () {

        //loader-------------------
        setTimeout(function () {
            $(".loader-first").fadeOut();
            $("html").removeClass("loadjs");
        }, 20);
        // -------------------------
    }, 700);

    if (sw > 1025) {
        fontResizer();
    }


    if ($(document).find("img").hasClass("svg-convert")) {
        $(".svg-convert").svgConvert({
            onComplete: function () { },
        });
    }
    if ($(document).find("html").hasClass("homepage")) {
        setTimeout(function () {
            //Home Video-------------------
            function openVideo(file) {
                document.getElementById("video").innerHTML = "<video  autoplay muted loop playsinline><source src='" + file + "' type='video/mp4'></video>"
            }
            // call using the following format
            openVideo('/assets/video/hero-bg.mp4');
            // -------------------------
        }, 1000);
    }
});




$(document).ready(function () {


    /* Navigation Active */
    $("#toggle").click(function () {
        $(this).toggleClass("active");
        $(".navigation").toggleClass("open");
        $("html").toggleClass("overflow-hidden");
    });
    /* Navigation Active */

    //Services Slider Thumb --------------
    if (sw > 767) {
        if ($(document).find("div").hasClass("services-thumb-slider")) {
            var services_thumb_slider = new Swiper(".services-thumb-slider", {
                slidesPerView: 5,
                speed: 400,
                direction: "vertical",
                centeredSlides: true,
                mousewheel: true,
                initialSlide: '2',
                // freeMode: true,
                // followFinger: true,
                // loop: true,
                // autoplay: {
                //     delay: 2500,
                //     disableOnInteraction: false,
                // },
            });
            $('.services-thumb-slider .swiper-slide').click(function () {
                services_thumb_slider.slideTo($(this).index());
            })
        }
    }
    // ------------------------------

    //Services Slider Gallery --------------
    if (sw > 767) {
        if ($(document).find("div").hasClass("services-slider")) {
            var services_slider = new Swiper(".services-slider", {
                slidesPerView: 1,
                initialSlide: '2',
                speed: 400,
                centeredSlides: true,
                effect: "fade",
                fadeEffect: {
                    crossFade: false,
                },
                // autoplay: {
                //     delay: 2500,
                //     disableOnInteraction: false,
                // },

            });
            services_slider.controller.control = services_thumb_slider;
            services_thumb_slider.controller.control = services_slider;
        }
    }
    // ------------------------------

    //Our Work Slider --------------
    if ($(document).find("div").hasClass("ourWork__Slider")) {
        var ourWork_Slider = new Swiper(".ourWork__Slider", {
            slidesPerView: 1.1,
            spaceBetween: 10,
            speed: 2000,
            // autoplay: {
            //     delay: 2500,
            //     disableOnInteraction: false,
            // },
            breakpoints: {
                576: {
                    slidesPerView: 2,
                    spaceBetween: 25,
                },
                1024: {
                    slidesPerView: 3.5,
                    spaceBetween: 25,
                },
                1199: {
                    slidesPerView: 4.5,
                    spaceBetween: 25,
                },
            },
        });
    }
    // ------------------------------

    //collabCompanies Slider --------------
    if ($(document).find("div").hasClass("collabCompanies__Slider")) {
        var collabCompanies_Slider = new Swiper(".collabCompanies__Slider", {
            slidesPerView: 2,
            speed: 2000,
            // autoplay: {
            //     delay: 2500,
            //     disableOnInteraction: false,
            // },
            grid: {
                rows: 2,
            },
            scrollbar: {
                el: ".collabCompanies__Slider .swiper-scrollbar",
                draggable: true,

            },
            breakpoints: {
                576: {
                    slidesPerView: 2,
                },
                768: {
                    slidesPerView: 4,
                },
                1024: {
                    slidesPerView: 7,
                },
            },
        });
    }
    // ------------------------------

    //Faq Slider --------------
    if ($(document).find("div").hasClass("faq__Slider")) {
        var faq_Slider = new Swiper(".faq__Slider", {
            slidesPerView: 1.1,
            spaceBetween: 10,
            speed: 2000,
            // autoplay: {
            //     delay: 2500,
            //     disableOnInteraction: false,
            // },
            breakpoints: {
                576: {
                    slidesPerView: 2,
                    spaceBetween: 30,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
                1240: {
                    slidesPerView: 5,
                    spaceBetween: 30,
                },
            },
        });
    }
    // ------------------------------

    //Worth Read Slider --------------
    if ($(document).find("div").hasClass("worth-read__Slider")) {
        var worth_read_slider = new Swiper(".worth-read__Slider", {
            slidesPerView: 1.25,
            spaceBetween: 30,
            centeredSlides: false,
            loop: false,
            speed: 2000,
            scrollbar: {
                el: ".worth-read__Slider .swiper-scrollbar",
                draggable: true,

            },
            // autoplay: {
            //     delay: 2500,
            //     disableOnInteraction: false,
            // },
            breakpoints: {
                576: {
                    slidesPerView: 2,
                    spaceBetween: 80,
                    centeredSlides: true,
                },
                1024: {
                    slidesPerView: 4.5,
                    centeredSlides: false,
                },
            },
        });
    }
    // ------------------------------


    //Menu Slider Thumb --------------
    if ($(document).find("div").hasClass("menu-slider-thumb1")) {
        var menu_slider_thumb1 = new Swiper(".menu-slider-thumb1", {
            slidesPerView: 6,
            spaceBetween: 10,
            direction: 'vertical',
            mousewheel: true,
            speed: 800,
            // autoplay: {
            //     delay: 2500,
            //     disableOnInteraction: false,
            // },
            breakpoints: {
                576: {
                    spaceBetween: 20,
                    slidesPerView: 6,
                },
            },
        });

    }
    // ------------------------------
    //Menu Slider Thumb --------------
    if ($(document).find("div").hasClass("menu-slider-thumb2")) {
        var menu_slider_thumb2 = new Swiper(".menu-slider-thumb2", {
            slidesPerView: 1,
            spaceBetween: 10,
            direction: 'vertical',
            mousewheel: true,
            speed: 2000,
            // autoplay: {
            //     delay: 2500,
            //     disableOnInteraction: false,
            // },
            breakpoints: {
                991: {
                    spaceBetween: 20,
                    slidesPerView: 6,
                },
            },
        });

    }
    // ------------------------------
    //Menu Slider Thumb --------------
    if ($(document).find("div").hasClass("menu-slider-thumb3")) {
        var menu_slider_thumb3 = new Swiper(".menu-slider-thumb3", {
            slidesPerView: 2,
            spaceBetween: 10,
            direction: 'vertical',
            speed: 2000,
            // autoplay: {
            //     delay: 2500,
            //     disableOnInteraction: false,
            // },
            breakpoints: {
                991: {
                    spaceBetween: 20,
                    slidesPerView: 6,
                },
            },
        });

    }
    // ------------------------------

    //Menu Slider Gallery --------------
    if ($(document).find("div").hasClass("menu-slider-gallery1")) {
        var menu_slider_gallery1 = new Swiper(".menu-slider-gallery1", {
            slidesPerView: 1,
            speed: 1000,
            effect: "fade",
            fadeEffect: {
                crossFade: true,
            },
            // autoplay: {
            //     delay: 2500,
            //     disableOnInteraction: false,
            // },
            thumbs: {
                swiper: menu_slider_thumb1,
            }
        });
        $('.menu-slider-thumb1 .swiper-slide').on('mouseover', function () {
            menu_slider_gallery1.slideTo($(this).index());
        })
    }
    // ------------------------------

    //Menu Slider Gallery --------------
    if ($(document).find("div").hasClass("menu-slider-gallery2")) {
        var menu_slider_gallery2 = new Swiper(".menu-slider-gallery2", {
            slidesPerView: 1,
            speed: 1000,
            effect: "fade",
            fadeEffect: {
                crossFade: true,
            },
            // autoplay: {
            //     delay: 2500,
            //     disableOnInteraction: false,
            // },
            thumbs: {
                swiper: menu_slider_thumb2,
            }
        });
        $('.menu-slider-thumb2 .swiper-slide').on('mouseover', function () {
            menu_slider_gallery2.slideTo($(this).index());
        })
    }
    // ------------------------------

    //Menu Slider Gallery --------------
    if ($(document).find("div").hasClass("menu-slider-gallery3")) {
        var menu_slider_gallery3 = new Swiper(".menu-slider-gallery3", {
            slidesPerView: 1,
            speed: 1000,
            effect: "fade",
            fadeEffect: {
                crossFade: true,
            },
            // autoplay: {
            //     delay: 2500,
            //     disableOnInteraction: false,
            // },
            thumbs: {
                swiper: menu_slider_thumb3,
            }
        });
        $('.menu-slider-thumb3 .swiper-slide').on('mouseover', function () {
            menu_slider_gallery3.slideTo($(this).index());
        })
    }
    // ------------------------------

});


// Menu Dropdown----------------Start
if (sw > 991) {
    $(".menu__menuitem").hover(function () {
        var isHovered = $(this).is(":hover");
        if (isHovered) {
            $(this).children(".dropdown-js").stop().slideToggle();
            $(this).closest(".menu__menuitem").addClass("dropdown-open");

        } else {
            $(this).children(".dropdown-js").stop().slideToggle();
            $(this).closest(".menu__menuitem").removeClass("dropdown-open");
        }
    });
}

if (sw < 992) {
    $(".menu__menuitem--menulink").click(function () {
        // var isHovered = $(this).is(":hover");
        // if (isHovered) {
        var tag2 = $(this).siblings(".dropdown-js");
        if ($(this).parent(".menu__menuitem").hasClass("dropdown-open")) {
            $(this).parent(".menu__menuitem").removeClass("dropdown-open");
            $(this).siblings(".dropdown-js").slideUp(600);

        } else {
            $(".menu > .menu__menuitem ").removeClass("dropdown-open");
            $(".menu__menuitem > .dropdown-js").slideUp(600);

            $(this).parent().addClass("dropdown-open");
            tag2.slideDown();
        }


        // } else {
        //     $(this).children(".dropdown-js").stop().slideUp(600);
        //     $(this).closest(".menu__menuitem").removeClass("dropdown-open");
        // }
    });

    // $("#anchor").click(function() {
    //     $('html, body').animate({
    //         scrollTop: $("#menu_slider_gallery_wrapper").offset().top
    //     }, 2000);

    // });

    // $("#anchor").on('click', function(e) {
    //     e.preventDefault();
    //     $('html, body').animate({ scrollTop: 0 }, '800');
    //     console.log("yesssss")
    // });
}
// Menu Dropdown----------------END


// Footer Mob Dropdown-----START
$(".toggle-btn > p").on("click", function () {
    if (sw < 992) {
        var tag = $(this).parent().find("ul");
        if ($(this).hasClass("opened")) {
            $(this).removeClass("opened");
            $(".toggle-btn > .qlinks-menu").slideUp();
        } else {
            $(".toggle-btn > p").removeClass("opened");
            $(".toggle-btn > .qlinks-menu").slideUp();
            $(this).addClass("opened");
            tag.slideDown();
        }
    }
});

// Footer Mob Dropdown-----END

// Landscape Mode off----------------Start
$(window).on("resize orientation", function () {
    sw = $(window).width();
    sh = $(window).height();
    if (sh < 450 && sw > 480 && sw < 820) {
        $("#portrait-warnning").css("display", "flex");
    } else {
        $("#portrait-warnning").css("display", "none");
    }

    setTimeout(function () {
        if (sw > 1025) {
            if (sw < 1400 && sw > 1300 && sh > 900) { } else {
                fontResizer();
            }
        }
    }, 1000);



});
// Landscape Mode off----------------End

// Sticky Header----------------Start
$(window).scroll(function () {
    var header1 = $(".header");
    var sticky1 = 0;
    if (window.pageYOffset > sticky1) {
        header1.addClass("sticky");
    } else {
        header1.removeClass("sticky");
    }
});
// Sticky Header----------------End








// if ($(document).find("body").hasClass("scroll-down")) {
//     $("#skip-sec").click(function() {
//         $('html, body').animate({
//             scrollTop: $("#ourWork").offset().top
//         }, 2000);
//     });
// }

// if ($(document).find("body").hasClass("scroll-up")) {
//     $("#skip-sec").click(function() {
//         $('html, body').animate({
//             scrollTop: $("#heroSec").offset().top
//         }, 2000);
//     });
// }

$("#skip-sec").click(function () {

    if ($(document).find("body").hasClass("scroll-down")) {
        $('html, body').animate({
            scrollTop: $("#ourWork").offset().top
        }, 2000);
    }
    if ($(document).find("body").hasClass("scroll-up")) {
        $('html, body').animate({
            scrollTop: $("#heroSec").offset().top
        }, 2000);
    }
});

if (sw < 1025) {
    badge();
}

function badge() {
    var swiper = new Swiper(".badge-slider", {
        slidesPerView: 1,
        spaceBetween: 30,
        breakpoints: {
            767: {
                slidesPerView: 2,
            }

        },

    });
}

// $(".why-choose-us .rounded-btn").click(function () {
//     $(".why-choose-us").toggleClass("active");
// });

$('.moreless-button').click(function () {
    $('.why-choose-content').slideToggle();
    if ($('.moreless-button').text() == "show more") {
        $(this).text("show less")
    } else {
        $(this).text("show more")
    }
});