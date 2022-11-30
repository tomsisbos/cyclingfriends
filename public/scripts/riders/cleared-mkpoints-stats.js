var prefectureContainers = document.querySelectorAll('.cleared-mkpoint-prefecture-block')
prefectureContainers.forEach( (prefectureContainer) => {
    prefectureContainer.addEventListener('click', () => {
        prefectureContainer.nextElementSibling.classList.toggle('hidden')
        prefectureContainer.classList.toggle('dropup')
    } )
} )