
// Infinite scroll
if (document.querySelector('#infiniteScrollElement')) var infiniteScrollElement = document.querySelector('#infiniteScrollElement')
else var infiniteScrollElement = document
infiniteScrollElement.addEventListener('scroll', function (event) {

    // Infinite scroll
    var element = event.target
    if (Math.ceil((element.scrollHeight - element.scrollTop) / 10) === Math.ceil(element.clientHeight / 10)) {
        console.log('scrolled')
    }

} )