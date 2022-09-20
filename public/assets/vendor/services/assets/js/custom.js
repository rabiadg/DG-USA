var sw = $(window).width();
var sh = $(window).height();
var sliderVarArr = ["serviceOfferSlider", "portfolioContentThumb", "portfolioContentSlider", "resultDrivenThumb", "resultDrivenSlider", "worthReadSlider"];

// ----------------------------------- Fontresizer Function ----------------------------------------------------------------

function fontResizer() {
    if (sw > 2200) {
        var perc = parseInt(sw) / 128.5;
    } else {
        var perc = parseInt(sw) / 118.9375;
    }
    $("body").css("font-size", perc);
}

// ----------------------------------- Window Load Function ----------------------------------------------------------------

$(window).on("load", function () {
    if (sw > 1025) {
        fontResizer();
    }

    // loader init
    setTimeout(function () {
        $(".loader-first").fadeOut("slow");
        $("html").removeClass("loadjs");
    }, 2500);


    if ($(document).find("img").hasClass("svg-convert")) {
        $(".svg-convert").svgConvert({
            onComplete: function () { },
        });
    }

    setTimeout(function () {
        /* ---------------- In View Animation -------------------- */
        $(".animate").bind("inview", function (event, isInView) {
            console.log("is inview", event);
            if (isInView) {
                var animate = $(this).attr("data-animation");
                var speedDuration = $(this).attr("data-duration");
                var $t = $(this);
                setTimeout(function () {
                    $t.removeClass("animate");
                    $t.addClass(animate + " animate__animated");
                }, speedDuration);
            }
        });
        $('.technology-accordion .accordion-header:first-child').addClass('active');
        $('.technology-accordion .accordion-header:first-child').next().css("display", "block");
    }, 1500);
});

// ----------------------------------- Window Resize Function ----------------------------------------------------------------

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

// ----------------------------------- Document Ready Function ----------------------------------------------------------------

$(document).ready(function () {
    if (sw < 1025) {
        badge();
    }
    ourServices();
    portfolioSlider();
    initAccordion();
    resultDriven();
    worthRead();
    headerMenuToggle();
    breakChar();
    headerService();
    headerQuoteForm();


});

// ----------------------------------- Sliders Functions ----------------------------------------------------------------

function ourServices() {
    sliderVarArr[0] = new Swiper(".service-offer-slider", {
        grid: {
            rows: 2,
            fill: "row"
        },
        slidesPerView: 2,
        slidesPerGroup: 4,
        breakpoints: {

            600: {
                grid: {
                    rows: 2,
                    fill: "row"
                },
                slidesPerView: 2,
                slidesPerGroup: 2,
            }

        },
        navigation: {
            nextEl: ".serv-next-btn",
            prevEl: ".serv-prev-btn",
        }

    });
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

function portfolioSlider() {
    sliderVarArr[1] = new Swiper(".portfolio-thumb", {
        spaceBetween: 10,
        slidesPerView: 3,
        speed: 3000,
        watchSlidesProgress: true,
    });
    sliderVarArr[2] = new Swiper(".portfolio-content-slider", {
        spaceBetween: 10,
        slidesPerView: 1,
        speed: 3000,
        allowTouchMove: false,
        autoplay: {
            delay: 2500,
            disableOnInteraction: false,
        },
        effect: "fade",
        thumbs: {
            swiper: sliderVarArr[1],
        },
    });
}

function resultDriven() {
    sliderVarArr[3] = new Swiper(".result-driven-thumb", {
        // slidesPerView: 3,
        watchSlidesProgress: true,
        freeMode: false,
        // delay: 2500,
        speed: 2500,
        breakpoints: {
            576: {
                slidesPerView: 1,
                spaceBetween: 0
            },
            767: {
                slidesPerView: 2,
                spaceBetween: 0
            },

            1199: {
                slidesPerView: 3,


            }

        },
    });

    sliderVarArr[4] = new Swiper(".result-driven-text", {
        speed: 1800,
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        breakpoints: {
            576: {
                slidesPerView: 1,
                spaceBetween: 10
            }
        },
        thumbs: {
            swiper: sliderVarArr[3],
        },
    });
}

function worthRead() {
    sliderVarArr[5] = new Swiper(".worth-read-slider", {
        slidesPerView: 1.6,
        loop: false,
        speed: 800,
        spaceBetween: 5,
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        breakpoints: {
            767: {
                slidesPerView: 1.8,
                spaceBetween: 10,
            },
            1024: {
                slidesPerView: 2.6,
                spaceBetween: 50,
            },
            1399: {
                slidesPerView: 2.6,
                spaceBetween: 150,
            }
        },
    });
}

// ----------------------------------- Accordion Function ----------------------------------------------------------------

function initAccordion() {
    $(".accordion").on("click", ".accordion-header", function () {

        $(this).toggleClass("active").parent().toggleClass("active");
        $(this).next().slideToggle(600);
        $(".accordion-collapse").not($(this).next()).slideUp(600);
        $(this).siblings(".accordion-header").removeClass("active");
    });
}

// ----------------------------------- Header menu Toggle Function ----------------------------------------------------------------

function headerMenuToggle() {
    $(".toggle-menu").on("click", function () {
        $(this).toggleClass("open")
        $(".navigation-wrapper").toggleClass("open-menu");
        $(".nav-primary-list").toggleClass("has-opened");
    })
}

// ----------------------------------- Break Characters Function ----------------------------------------------------------------
function breakChar() {
    $(".navigation-item a").html(function (index, html) {
        return html.replace(/\S/g, '<span>$&</span>');
    });
}

// ----------------------------------- Open Header Services Function ----------------------------------------------------------------
function headerService() {
    $(".services-btn").on("click", function () {
        $(this).toggleClass("active");
        $(".navigation-box").toggleClass("service--opened")
    })
}

function headerQuoteForm() {
    $("#start-project,#close-project").on("click", function () {
        $(".start-project-wrapper").toggleClass("opened");
    })

}
var result = $('.service-offer-slider .swiper-wrapper .swiper-slide');
if (result.length <= 4) {
    $(".serv-nav").css("display", "none");
}









