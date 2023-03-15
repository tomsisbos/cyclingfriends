import Modal from '/map/class/Modal.js'

// If show bikes button displayed (= if more than one bike registered)
if (document.getElementById('showBike')) {
    var showButton = document.getElementById('showBike')
    var bikes = document.querySelectorAll('.pf-bike-container.hidden')
    var showText = showButton.innerText

    // Display other bikes and change button text on click
    showButton.addEventListener('click', () => {
        bikes.forEach( (bike) => bike.classList.toggle('bike-displayed'))
        if (showButton.innerText === '非表示') showButton.innerText = showText
        else showButton.innerText = '非表示'
    } )
}

// Open modal on bike image click
document.querySelectorAll('.pf-bike-image').forEach( (bikeImage) => {
    var modal = new Modal(bikeImage.src)
    bikeImage.after(modal.element)
    bikeImage.addEventListener('click', () => modal.open())
} )