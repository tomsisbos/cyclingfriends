const addGuide = document.querySelector('#addGuide')
const position = document.querySelector('#position')

addGuide.addEventListener('change', (e) => {
    position.classList.remove('hidden')
    var rank = parseInt(addGuide[e.target.selectedIndex].dataset.rank)
    console.log(addGuide[e.target.selectedIndex])
    for (let i = 0; i < position.length; i++) {
        console.log(position[i].value)
        console.log(rank)
        if (parseInt(position[i].value) < rank) position[i].setAttribute('disabled', 'disabled')
        else position[i].removeAttribute('disabled', 'disabled')
    }
})