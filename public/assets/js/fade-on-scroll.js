var elements = document.querySelectorAll('.js-fade-on-scroll')
elements.forEach((element) => {
    const elementHeight = element.offsetHeight

    // For elements containing an overlay color data property, append a color overlay to the element and set its opacity 
    if (element.dataset.overlayColor) {

        // Append overlay
        var overlay = document.createElement('div')
        overlay.classList.add('black-overlay')
        if (element.classList.contains('js-overlay-top')) overlay.classList.add('overlay-top') // Add 'overlay-top' class to overlays that need to substract sidebar height
        overlay.style.backgroundColor = element.dataset.overlayColor
        element.before(overlay)


        // Calculate opacity
        document.addEventListener('scroll', () => {
            var topPosition = element.getBoundingClientRect().top
            var bottomPosition = elementHeight + topPosition
            if (bottomPosition > 0 && topPosition < 0) {
                var pourcentageDisplayed = bottomPosition * 100 / elementHeight
                overlay.style.opacity = 1 - (pourcentageDisplayed / 100)
            } else overlay.style.opacity = 0
        } )

        // For elements containing an second overlay color data property, append another color overlay in the opposite direction
        if (element.dataset.overlayColor2) {

            // Append overlay
            var overlay2 = document.createElement('div')
            overlay2.classList.add('black-overlay')
            if (element.classList.contains('js-overlay-top')) overlay2.classList.add('overlay-top') // Add 'overlay-top' class to overlays that need to substract sidebar height
            overlay2.style.backgroundColor = element.dataset.overlayColor2
            element.before(overlay2)


            // Calculate opacity
            document.addEventListener('scroll', () => {
                var bottomPosition = element.getBoundingClientRect().bottom
                var pourcentageDisplayed = bottomPosition * 100 / elementHeight
                if (pourcentageDisplayed > 100 && pourcentageDisplayed < 200) {
                    overlay2.style.opacity = (pourcentageDisplayed - 100) / 100
                } else overlay2.style.opacity = 0
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
            } else element.style.opacity = 1
        } )
    }

    

} )