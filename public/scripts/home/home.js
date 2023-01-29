// Set video starting time at random
const video = document.querySelector('.home-video video')

video.addEventListener('loadeddata', () => {
    video.currentTime = (Math.round(Math.random() * 100) / 100) * video.duration
}, {once: true} )