// Set video starting time at random
const video = document.querySelector('.home-video video')

video.addEventListener('loadeddata', () => {
    console.log(video.duration)
    video.currentTime = (Math.round(Math.random() * 100) / 100) * video.duration
}, {once: true} )