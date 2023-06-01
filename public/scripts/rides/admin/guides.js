const addGuide = document.querySelector('#addGuide')
const position = document.querySelector('#position')

addGuide.addEventListener('change', (e) => {
    position.classList.remove('hidden')
    var rank = parseInt(addGuide[e.target.selectedIndex].dataset.rank)
    for (let i = 0; i < position.length; i++) {
        if (parseInt(position[i].value) < rank) position[i].setAttribute('disabled', 'disabled')
        else position[i].removeAttribute('disabled', 'disabled')
    }
})