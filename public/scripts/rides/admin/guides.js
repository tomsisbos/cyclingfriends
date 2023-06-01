const addGuide = document.querySelector('#addGuide')
const position = document.querySelector('#position')

// On each add guide change, update position select
addGuide.addEventListener('change', (e) => {
    position.classList.remove('hidden')
    var rank = parseInt(addGuide[e.target.selectedIndex].dataset.rank)
    for (let i = 0; i < position.length; i++) {
        // Disable all positions higher from rank
        if (parseInt(position[i].value) < rank) position[i].setAttribute('disabled', 'disabled')
        else position[i].removeAttribute('disabled', 'disabled')
        // Select highest position by default
        if (parseInt(position[i].value) == rank) position[i].setAttribute('selected', 'selected')
        else position[i].removeAttribute('selected', 'selected')
    }
})