//SWIPER
const swiper = new Swiper('.swiper-container', {
    loop: true,
    slidesPerView: 1,
    spaceBetween: 10,
    pagination: {
        el: '.swiper-pagination',
        clickable: true,
    },
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
    autoplay: {
        delay: 3000,
        disableOnInteraction: false,
    },
});

//INCREMENT TIME JUST NOW ETC
function incrementTimeAgo() {
    const elements = document.querySelectorAll('.time-ago');

    elements.forEach(function (element) {
        const createdAt = new Date(element.getAttribute('data-time'));
        const now = new Date();

        const diffInSeconds = Math.floor((now - createdAt) / 1000);
        const minutes = Math.floor(diffInSeconds / 60);
        const hours = Math.floor(diffInSeconds / 3600);
        const days = Math.floor(diffInSeconds / (3600 * 24));
        const months = Math.floor(diffInSeconds / (3600 * 24 * 30));
        const years = Math.floor(diffInSeconds / (3600 * 24 * 365));

        let timeAgo = '';

        if (years > 0) {
            timeAgo = years + " year" + (years > 1 ? "s" : "") + " ago";
        } else if (months > 0) {
            timeAgo = months + " month" + (months > 1 ? "s" : "") + " ago";
        } else if (days > 0) {
            timeAgo = days + " day" + (days > 1 ? "s" : "") + " ago";
        } else if (hours > 0) {
            timeAgo = hours + " hour" + (hours > 1 ? "s" : "") + " ago";
        } else if (minutes > 0) {
            timeAgo = minutes + " minute" + (minutes > 1 ? "s" : "") + " ago";
        } else {
            timeAgo = "Just now";
        }
        element.innerText = timeAgo;
    });
}

setInterval(incrementTimeAgo, 60000);
incrementTimeAgo();