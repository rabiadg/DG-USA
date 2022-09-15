

 if ($(document).find("html").hasClass("homepage")) {
gsap.to(".translateY-top", {
    y: -100,
    ease: "none",
    scrollTrigger: {
        trigger: ".translateY-parent",
        start: "top bottom", // the default values
        end: "bottom top",
        scrub: 1.5
    },
});

gsap.to(".translateY-bottom", {
    y: 100,
    ease: "none",
    scrollTrigger: {
        trigger: ".translateY-parent",
        start: "top bottom", // the default values
        end: "bottom top",
        scrub: 1.5
    },
});

gsap.to(".clippath-item", {
    "clip-path": "inset(0 0 0 0)",
    ease: "expo.inOut",
    scrollTrigger: {
        trigger: ".clippath-item",
        start: "top bottom", // the default values
        end: "bottom 100px",

        duration: 1,
    },
});


if ($(window).width() > 767) {
    const translateXBox = gsap.utils.toArray('.translateX-right');
    translateXBox.forEach(box => {
        gsap.to(box, {
            x: 250,
            ease: "none",
            scrollTrigger: {
                trigger: box,
                start: "top bottom", // the default values
                end: "bottom top",
                scrub: 1.5
            },
        })
    });
}
 }