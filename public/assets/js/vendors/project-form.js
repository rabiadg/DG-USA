// const menuToggleOpen = document.querySelector(".menu-toggle-open1, .menu-toggle-open2");

const menuToggleClose = document.querySelector("#menu-toggle-close");
const menu = document.querySelector(".projectForm--box");

const tl = gsap.timeline({
    paused: true
});



tl.to(".projectForm--box", {
    x: 0,
    duration: 1,
    ease: "expo.inOut"
});


tl.from(".projectForm .form-item", {
    y: 25,
    opacity: 0,
    duration: 0.4,
    ease: "expo.out",
    stagger: 0.1
});


const boxes = document.querySelectorAll('.menu-open');

boxes.forEach(box => {
    box.addEventListener('click', function handleClick(event) {
        console.log('box clicked', event);

        tl.play()
    });
});



// menuToggleOpen.addEventListener("click", () => );
menuToggleClose.addEventListener("click", () => {
    tl.reverse();
        
});



// gsap.utils.toArray(".getSocial").forEach(section => {
//     gsap.from(section.querySelectorAll(".animate--item"), {
//         scrollTrigger: section,
//         autoAlpha: 0,
//         y: 25,
//         scale: 1.5,
//         duration: 0.75,
//         stagger: 0.25
//     });
// });