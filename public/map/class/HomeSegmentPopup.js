import CFUtils from "/map/class/CFUtils.js"
import SegmentPopup from "/map/class/SegmentPopup.js"

export default class HomeSegmentPopup extends SegmentPopup {

    constructor (options, segment) {
        super(options, segment)
    }

    load () {

        // Define advised
        var advised = ''
        if (this.data.advised) advised = '<div class="popup-advised" title="cyclingfriendsのおススメ">★</div>'

        // Define tag color according to segment rank
        if (this.data.rank == 'local') var tagColor = 'tag-lightblue'
        else if (this.data.rank == 'regional') var tagColor = 'tag-blue'
        else if (this.data.rank == 'national') var tagColor = 'tag-darkblue'

        // Build tagslist
        var tags = ''
        this.data.tags.map( (tag) => {
            tags += `
            <div class="popup-tag tag-dark">#` + CFUtils.getTagString(tag) + `</div>`
        } )

        // Build seasonBox
        var seasonBox = ''
        if (this.data.seasons.length > 0) {
            seasonBox = '<div class="popup-season-box">'
            this.data.seasons.forEach( (season) => {
                seasonBox += `
                <div class="popup-season">
                    <div class="popup-season-period">` +
                        CFUtils.getPeriodString(season.period_start) + ` から ` + CFUtils.getPeriodString(season.period_end) + ` まで
                    </div>
                    <div class="popup-season-description">` +
                        season.description + `
                    </div>
                </div>`
            } )
            seasonBox += '</div>'
        }

        // Build pointBox
        var adviceBox = ''
        if (this.data.advice.description) {
            adviceBox = `
            <div class="popup-advice">
                <div class="popup-advice-name">
                    <iconify-icon icon="el:idea" width="20" height="20"></iconify-icon> ` +
                    this.data.advice.name + `
                </div>
                    <div class="popup-advice-description">` +
                    this.data.advice.description + `
                </div>
            </div>`
        }

        // Set content
        this.popup.setHTML(`
        <div class="popup-img-container">
            <div class="popup-img-background">
                <img id="segmentFeaturedImage` + this.data.id + `" class="popup-img popup-img-with-background" />
            </div>
            <div class="popup-icons"></div>
        </div>
        <div class="popup-content">
            <div class="popup-properties">
                <div class="popup-properties-name">` + this.data.name +
                    advised + `
                    <div class="popup-tag ` + tagColor + `" >`+ capitalizeFirstLetter(this.data.rank) + `</div>
                </div>
                <div>
                    距離 : `+ (Math.round(this.data.route.distance * 10) / 10) + `km - 獲得標高 : ` + this.data.route.elevation + `m
                </div>
                <div class="popup-rating"></div>
                <div id="profileBox" class="mt-2 mb-2" style="height: 100px; background-color: white;">
                    <canvas id="elevationProfile"></canvas>
                </div>
            </div>
            <div class="popup-description">`
                + this.data.description + `
            </div>
            <div>`
                + tags + `
            </div>`
            + adviceBox + ``
            + seasonBox + `
        </div>`)
    }

    rating = () => {
        var ratingDiv = document.querySelector('.popup-rating')
        
        // Display 5 stars with an unique id
        if (ratingDiv.innerText == '') {
            for (let i = 1; i < 6; i++) {
                ratingDiv.innerHTML = ratingDiv.innerHTML + '<div number="' + i + '" class="star">☆</div>'
            }
        }
        
        var stars = document.querySelectorAll('.star')

        // Get current rating infos
        ajaxGetRequest (this.apiUrl + "?get-rating=true&type=" + this.type + "&id=" + this.data.id, (ratingInfos) => {

            var setRating = (ratingInfos) => {
                if (ratingInfos.vote != false) {
                    var number    = parseInt(ratingInfos.vote)
                    var className = 'voted-star'
                } else if (ratingInfos.grades_number > 0) {
                    var number    = parseInt(ratingInfos.rating)
                    var className = 'selected-star'
                } else {
                    var number    = 0
                    var className = ''
                }
                // Fill stars according to number
                for (let i = 1; i < number + 1; i++) {
                    stars[round(i-1)].innerText = '★'
                }
                for (let i = number + 1; i < 6; i++) {
                    stars[round(Math.floor(i)-1)].innerText = '☆'
                }
                // Set classes according to last declared response vote
                stars.forEach( (star) => { star.className = 'star ' + className } )

                // If rating details are already displayed, update them
                if (document.querySelector('.popup-rating-details')) {
                    if (number == 0) { // If number is 0, remove rating details
                        document.querySelector('.popup-rating-details').remove()
                    } else { // Else, update it
                        document.querySelector('.popup-rating-details').innerText = round(ratingInfos.rating, 2).toFixed(2) + ' (' + ratingInfos.grades_number + ')'
                    }
                // Else, display them
                } else if (ratingInfos.grades_number > 0) {
                    var ratingDetails = document.createElement('div')
                    ratingDetails.innerText = round(ratingInfos.rating, 2).toFixed(2) + ' (' + ratingInfos.grades_number + ')'
                    ratingDetails.className = 'popup-rating-details'
                    ratingDiv.after(ratingDetails)
                }
            }
            
            setRating(ratingInfos)

        } )
    }

    // Get relevant photos from the API and display it with a modal behavior
    getMkpoints () {
        return new Promise( (resolve, reject) => {
            
            // Asks server for current photo data
            this.loaderContainer = this.popup.getElement().querySelector('.popup-img-background')
            ajaxGetRequest (this.apiUrl + "?segment-mkpoints=" + this.data.id, (mkpoints) => {

                resolve(mkpoints)
            }, this.loader)
        } )
    }

    prepareModal () {

        // Prepare arrows
        if (this.photos.length > 1) {
            var prevArrow = document.createElement('a')
            prevArrow.className = 'prev lightbox-arrow'
            prevArrow.innerHTML = '&#10094;'
            var nextArrow = document.createElement('a')
            nextArrow.className = 'next lightbox-arrow'
            nextArrow.innerHTML = '&#10095;'
        }
        
        // If first opening, prepare modal window structure
        if (!document.querySelector('#myModal')) {
            var modalBaseContent = document.createElement('div')
            modalBaseContent.id = 'myModal'
            modalBaseContent.className = 'modal'
            var closeButton = document.createElement('span')
            closeButton.className = "close cursor"
            closeButton.setAttribute('onclick', 'closeModal()')
            closeButton.innerHTML = '&times;'
            var modalBlock = document.createElement('div')
            modalBlock.className = "modal-block"
            modalBaseContent.appendChild(closeButton)
            modalBaseContent.appendChild(modalBlock)
            document.querySelector('body').after(modalBaseContent)
            // If more than one photo, display arrows
            if (this.photos.length > 1) {
                modalBlock.appendChild(prevArrow)
                modalBlock.appendChild(nextArrow)
            }
        // Else, clear modal window content
        } else {
            document.querySelector('.modal-block').innerHTML = ''
            if (this.photos.length > 1) {
                document.querySelector('.modal-block').appendChild(prevArrow)
                document.querySelector('.modal-block').appendChild(nextArrow)
            }
        }
        
        // Slides display
        var cursor = 0
        var slides = []
        var imgs = []
        var slidesBox = document.createElement('div')
        slidesBox.className = 'slides-box'
        document.querySelector('.modal-block').appendChild(slidesBox)
        this.mkpoints.forEach( (mkpoint) => {
            mkpoint.photos.forEach( (photo) => {
                const distanceFromStart = this.getDistanceFromStart(mkpoint)
                slides[cursor] = document.createElement('div')
                slides[cursor].className = 'mySlides wider-slide'
                // Create number
                let numberText = document.createElement('div')
                numberText.className = 'numbertext'
                numberText.innerHTML = (cursor + 1) + ' / ' + this.photos.length
                slides[cursor].appendChild(numberText)
                // Create image
                imgs[cursor] = document.createElement('img')
                imgs[cursor].src = 'data:image/jpeg;base64,' + photo.blob
                imgs[cursor].id = 'mkpoint-img-' + photo.id
                imgs[cursor].classList.add('fullwidth')
                slides[cursor].appendChild(imgs[cursor])
                // Create image meta
                var imgMeta = document.createElement('div')
                imgMeta.className = 'mkpoint-img-meta'
                slides[cursor].appendChild(imgMeta)
                var period = document.createElement('div')
                period.className = 'mkpoint-period lightbox-period'
                period.classList.add('period-' + photo.month)
                period.innerText = photo.period
                imgMeta.appendChild(period)
                slidesBox.appendChild(slides[cursor])
                // Caption display
                var caption = document.createElement('div')
                caption.className = 'lightbox-caption'
                var name = document.createElement('div')
                name.innerText = 'km ' + (Math.ceil(distanceFromStart * 10) / 10) + ' - ' + mkpoint.name
                name.className = 'lightbox-name'
                caption.appendChild(name)
                var location = document.createElement('div')
                location.innerText = mkpoint.city + ' (' + mkpoint.prefecture + ') - ' + mkpoint.elevation + 'm'
                location.className = 'lightbox-location'
                caption.appendChild(location)
                var description = document.createElement('div')
                description.className = 'lightbox-description'
                description.innerText = mkpoint.description
                caption.appendChild(description)
                slidesBox.appendChild(caption)
                // Display caption on slide hover
                slides[cursor].addEventListener('mouseover', () => {
                    caption.style.visibility = 'visible'
                    caption.style.opacity = '1'
                } )
                slides[cursor].addEventListener('mouseout', () => {
                    caption.style.visibility = 'hidden'
                    caption.style.opacity = '0'
                } )
                cursor++
            } )
        } )
        // Demos display
        cursor = 0
        var demos = []
        var demosBox = document.createElement('div')
        demosBox.className = 'thumbnails-box'
        document.querySelector('.modal-block').appendChild(demosBox)
        this.photos.forEach( (photo) => {
            let column = document.createElement('div')
            column.className = 'column'
            demos[cursor] = document.createElement('img')
            demos[cursor].className = 'demo cursor fullwidth'
            demos[cursor].setAttribute('demoId', cursor + 1)
            demos[cursor].src = 'data:' + photo.type + ';base64,' + photo.blob
            column.appendChild(demos[cursor])
            demosBox.appendChild(column)
        } )

        // Load lightbox script for this popup
        var script = document.createElement('script');
        script.src = '/assets/js/lightbox-script.js';
        this.popup.getElement().appendChild(script);

    }
}