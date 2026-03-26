document.addEventListener("DOMContentLoaded", () => {
    const burger = document.querySelector('.header-wrapper__burger');
    const menu = document.querySelector('.header-wrapper__menu-mobile');
    const body = document.body;

    burger.addEventListener('click', () => {
        burger.classList.toggle('active');
        menu.classList.toggle('active');
        body.classList.toggle('no-scroll');
    });
});


// table slider

if(window.innerWidth < 992) {
    var swiper = new Swiper(".society__slider", {    
        slidesPerView: 1,
        spaceBetween: 1,
        autoHeight: true,
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
    });
}

// new-project 

if(window.innerWidth < 992) {
    var swiper = new Swiper(".new-project__slider", {    
        slidesPerView: 1,
        spaceBetween: 1,
        autoHeight: true,
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
    });
}

var swiper = new Swiper(".membership-slider", {    
    breakpoints: {
        0: {
            slidesPerView: 1,
            spaceBetween: 10,
            autoHeight: true,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
        },
        768: {
            slidesPerView: 2,
            spaceBetween: 28,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
        },
        992: {
            slidesPerView: 3,
            spaceBetween: 28,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
        },
        1200: {
            slidesPerView: 4,
            spaceBetween: 28,
        },
    },
});

var swiper = new Swiper(".details-project__slider-thumb", {
    freeMode: true,
    watchSlidesProgress: true,
    breakpoints: {
        
        992: {
            slidesPerView: 5,
            spaceBetween: 12,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
        },
        1400: {
            slidesPerView: 8,
            spaceBetween: 12,
        },
    },
});
var swiper2 = new Swiper(".details-project__slider", {
    spaceBetween: 10,
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
    thumbs: {
        swiper: swiper,
    },
});


Fancybox.bind("[data-fancybox]", {
  // Your custom options
});


const tabs = document.querySelectorAll('.main-tabs-click');
const panes = document.querySelectorAll('.main-tabs-pane');

tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        const tabName = tab.dataset.tab;

        // убрать активные классы
        tabs.forEach(t => t.classList.remove('main-tabs-click--active'));
        panes.forEach(p => p.classList.remove('main-tabs-pane--active'));

        // добавить активный
        tab.classList.add('main-tabs-click--active');
        document.querySelector(`.main-tabs-pane[data-tab="${tabName}"]`)
            .classList.add('main-tabs-pane--active');
    });
});


const tabsProject = document.querySelectorAll('.main-tabs-click-project');
const panesProject = document.querySelectorAll('.main-tabs-pane-project');

tabsProject.forEach(tab => {
    tab.addEventListener('click', () => {
        const tabName = tab.dataset.tab;

        // убрать активные классы
        tabsProject.forEach(t => t.classList.remove('main-tabs-click-project--active'));
        panesProject.forEach(p => p.classList.remove('main-tabs-pane-project--active'));

        // добавить активный
        tab.classList.add('main-tabs-click-project--active');
        document.querySelector(`.main-tabs-pane-project[data-tab="${tabName}"]`)
            .classList.add('main-tabs-pane-project--active');
    });
});


// gallery

var swiper = new Swiper(".project-gallery__swiper", {
      slidesPerView: "auto",
      centeredSlides: true,
      spaceBetween: 30,
      autoplay: {
        delay: 2500,
        disableOnInteraction: false,
      },
      navigation: {
        nextEl: ".gallery-button-next",
        prevEl: ".gallery-button-prev",
      },
      loop: true,
    });