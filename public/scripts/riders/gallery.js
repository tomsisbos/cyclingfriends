var apiUrl = '/api/riders/profile.php'
var captionContainer = document.getElementById('caption')
var slides = document.querySelectorAll('.mySlides img')

ajaxGetRequest (apiUrl + '?get_gallery_infos=true&id=' + getParam('id'), (response) => {
    console.log(response)
    slides.forEach( (slide) => {
        slide.addEventListener('change', (e) => {
            var id = e.target.id
            console.log(id)
            captionContainer.innerText = response[id-1].caption
        } )
    } )
} )