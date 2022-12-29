import RideMap from "/map/class/ride/RideMap.js"

var rideMap = new RideMap()
rideMap.edit = true

var defaultSrc = document.querySelector('.summary-checkpoint-image img').src
var photos = document.querySelectorAll('.summary-checkpoint-image img')
var header = document.querySelector('.ride-header')

// Get session infos
ajaxGetRequest ('/api/map.php' + "?get-session=true", async (session) => {
    console.log(session)
    rideMap.session = session
    rideMap.method = session['edit-course'].method

    // Set default header image to the first checkpoint image set, and select it among thumbnails
    if (defaultSrc) {
        console.log('defaultSrc')
        if (session['edit-course'].featuredimage) {
            console.log('featured image')
            console.log(session)
            let src = document.getElementById(session['edit-course'].featuredimage).querySelector('img').src
            header.style.backgroundImage = 'url("' + src + '")'
            photos[session['edit-course'].featuredimage].classList.add('photo-selected')
        } else {
            console.log('no featured image')
            header.style.backgroundImage = 'url("' + defaultSrc + '")'
        }
    }

    // Change header image on clicking on any checkpoint image
    photos.forEach(photo => photo.addEventListener('click',
        (e) => {
            let src = e.target.src
            let id = e.target.closest('.summary-checkpoint').id
            photos.forEach(photo => photo.classList.remove('photo-selected'))
            e.target.classList.add('photo-selected')
            header.style.backgroundImage = 'url("' + src + '")'
            rideMap.updateSession( {
                method: rideMap.method,
                data: {
                    'featuredimage': id
                }
            } )
        } )
    )
} )