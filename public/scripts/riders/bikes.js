var showButton = document.getElementById('showBike')
var bikes = document.getElementById('bikes')
showButton.addEventListener('click', () => {
    bikes.classList.toggle('show')
    if (showButton.innerText === 'Show') {
        showButton.innerText = 'Hide'
    } else {
        showButton.innerText = 'Show'
    }
} )

// Open modal on bike image click
document.querySelectorAll('.bike-image-img').forEach( (bikeImage) => {
    bikeImage.addEventListener('click', (e) => {
        openSingleModal(e.target.src)
    } )
} )