/* Custom Cursor Start */
function customCursor() {
    var cursor = $(".cursor"),
        follower = $(".cursor-follower");
    var posX = 0,
        posY = 0;
    var mouseX = 0,
        mouseY = 0;
    TweenMax.to({}, 0.01, {
        repeat: -1,
        onRepeat: function() {
            posX += (mouseX - posX) / 9;
            posY += (mouseY - posY) / 9;

            TweenMax.set(follower, {
                css: {
                    left: posX - 0,
                    top: posY - 0,
                },
            });

            TweenMax.set(cursor, {
                css: {
                    left: mouseX,
                    top: mouseY,
                },
            });
        },
    });
    $(document).on("mousemove", function(e) {
        mouseX = e.clientX;
        mouseY = e.clientY;
    });
    $("a[href]").on("mouseenter", function() {
        cursor.addClass("hovered");
        follower.addClass("hovered");
    });
    $("a[href]").on("mouseleave", function() {
        cursor.removeClass("hovered");
        follower.removeClass("hovered");
    });
    $(".drag--img").on("mouseenter", function() {
        cursor.addClass("img-hover");
        follower.addClass("img-hover");
    });
    $(".drag--img").on("mouseleave", function() {
        cursor.removeClass("img-hover");
        follower.removeClass("img-hover");
    });
    $(".drag--img--white").on("mouseenter", function() {

        cursor.addClass("white-bg");
        follower.addClass("white-bg");

    });
    $(".drag--img--white").on("mouseleave", function() {

        cursor.removeClass("white-bg");
        follower.removeClass("white-bg");
    });
}


$(document).ready(function() {

    customCursor();

});
/* Custom Cursor End */