var elements = document.querySelectorAll('.js-fade-on-scroll')
    elements.forEach((element) => {
    const elementHeight = element.offsetHeight

    // For elements containing an overlay color data property, append a black overlay to the element and set its opacity 
    if (element.dataset.overlayColor) {

        // Append overlay
        var overlay = document.createElement('div')
        overlay.className = 'black-overlay'
        overlay.style.height = elementHeight
        if (element.dataset.overlayColor) overlay.style.backgroundColor = element.dataset.overlayColor
        element.before(overlay)


        // Calculate opacity
        document.addEventListener('scroll', () => {
            var topPosition = element.getBoundingClientRect().top
            var bottomPosition = elementHeight + topPosition
            if (bottomPosition > 0 && topPosition < 0) {
                var pourcentageDisplayed = bottomPosition * 100 / elementHeight
                overlay.style.opacity = 1 - (pourcentageDisplayed / 100)
            }
        })

    // Default, set element opacity
    } else {

        // Calculate opacity
        document.addEventListener('scroll', () => {
            var topPosition = element.getBoundingClientRect().top
            var bottomPosition = elementHeight + topPosition
            if (bottomPosition > 0 && topPosition < 0) {
                var pourcentageDisplayed = bottomPosition * 100 / elementHeight
                element.style.opacity = pourcentageDisplayed / 100
            }
        })
    }
} )