var elements = document.querySelectorAll('.js-fade-on-scroll')
    elements.forEach((element) => {
    const elementHeight = element.offsetHeight

    // For elements containing an overlay color data property, append a color overlay to the element and set its opacity 
    if (element.dataset.overlayColor) {

        // Append overlay
        var overlay = document.createElement('div')
        overlay.className = 'black-overlay'
        overlay.style.height = elementHeight
        overlay.style.backgroundColor = element.dataset.overlayColor
        element.before(overlay)


        // Calculate opacity
        document.addEventListener('scroll', () => {
            var topPosition = element.getBoundingClientRect().top
            var bottomPosition = elementHeight + topPosition
            if (bottomPosition > 0 && topPosition < 0) {
                var pourcentageDisplayed = bottomPosition * 100 / elementHeight
                overlay.style.opacity = 1 - (pourcentageDisplayed / 100)
            }
        } )

        // For elements containing an second overlay color data property, append another color overlay in the opposite direction
        if (element.dataset.overlayColor2) {

            // Append overlay
            var overlay2 = document.createElement('div')
            overlay2.className = 'black-overlay'
            overlay2.style.height = elementHeight
            overlay2.style.backgroundColor = element.dataset.overlayColor2
            element.before(overlay2)


            // Calculate opacity
            document.addEventListener('scroll', () => {
                var bottomPosition = element.getBoundingClientRect().bottom
                var pourcentageDisplayed = bottomPosition * 100 / elementHeight
                if (pourcentageDisplayed > 100 && pourcentageDisplayed < 200) {
                    overlay2.style.opacity = (pourcentageDisplayed - 100) / 100
                    console.log(pourcentageDisplayed)
                }
            } )

        }

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
        } )
    }

    

} )