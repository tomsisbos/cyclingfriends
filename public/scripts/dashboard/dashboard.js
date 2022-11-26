// Infinite scroll

const $cardsContainer = document.querySelector('#threadContainer')
const photosquantity = parseInt($cardsContainer.dataset.photosquantity)
const limit = parseInt($cardsContainer.dataset.limit)
var offset = limit

if (document.querySelector('#infiniteScrollElement')) var infiniteScrollElement = document.querySelector('#infiniteScrollElement')
else var infiniteScrollElement = document
infiniteScrollElement.addEventListener('scroll', function (event) {

    var element = event.target
    // When scroll to the bottom of the dashboard
    if (Math.ceil((element.scrollHeight - element.scrollTop) / 10) === Math.ceil(element.clientHeight / 10)) {
        ajaxGetRequest('/api/dashboard.php?getActivities=' + limit + ',' + offset + ',' + photosquantity, (response) => {
            console.log(response)
            offset += limit
            response.forEach( (activity) => {
                var $card = buildActivityCard(activity)
                $cardsContainer.appendChild($card)
            } )
        } )
    }

} )

function buildActivityCard (activity) {

    // Build main container
    var $mainContainer = document.createElement('div')
    $mainContainer.className = 'ac-main-container'
    $mainContainer.innerHTML = `
        <div class="ac-infos-container">
            <div class="ac-user-details">
                <div class="ac-user-propic">
                    <a href="/rider/` + activity.user.id + `">` + activity.propic + `</a>
                </div>
                <div class="ac-details">
                    <div class="ac-user-name">
                        <a href="/rider/` + activity.user.id + `">` + activity.user.login + `</a>
                    </div>
                    <div class="ac-name">
                        <a href="/activity/` + activity.id + `">
                            ` + activity.title + `
                        </a>
                    </div>
                    <div class="ac-posting-date">
                        ` + activity.datetimeString + ' - from ' + activity.checkpoints[0].geolocation.city + ' to ' + activity.checkpoints[activity.checkpoints.length - 1].geolocation.city + `
                    </div>
                </div>
            </div>
            <div class="ac-specs">
                <div class="ac-spec">
                    <div class="ac-spec-label"><strong>Distance : </strong></div>
                    <div class="ac-spec-value">` + Math.round(activity.route.distance / 10) * 10 + `<span class="ac-spec-unit"> km</span></div>
                </div>
                <div class="ac-spec">
                    <div class="ac-spec-label"><strong>Duration : </strong></div>
                    <div class="ac-spec-value">
                        ` + activity.formattedDuration + `
                    </div>
                </div>
                <div class="ac-spec">
                    <div class="ac-spec-label"><strong>Elevation : </strong></div>
                    <div class="ac-spec-value">` + activity.route.elevation + `<span class="ac-spec-unit"> m</span></div>
                </div>
                <div class="ac-spec">
                    <div class="ac-spec-label"><strong>Avg. Speed : </strong></div>
                    <div class="ac-spec-value">` + activity.averagespeed + `<span class="ac-spec-unit"> km/h</span></div>
                </div>
            </div>
        </div>
        <div class="ac-thumbnail-container">
            <a href="/activity/` + activity.id + `">
                <img class="ac-map-thumbnail" src="` + activity.route.thumbnail + `">
            </a>
        </div>`
        
    // Build photos container
    var $photosContainer = document.createElement('div')
    $photosContainer.className = 'ac-photos-container'
    var i = 1
    activity.photos.forEach( (photo) => {
        $a = document.createElement('a')
        $a.setAttribute('href', '/activity/' + activity.id)
        // Build variables
        if (photo.featured) var featured = ' featured'
        else var featured = ''
        if (i == photosquantity) {
            if (activity.photosNumber > photosquantity) var $photosOthers = `<div class="ac-photos-others"><div> + ` + (activity.photosNumber - photosquantity + 1) + `</div></div>`
            else var $photosOthers = ''
        } else var $photosOthers = ''
        // Build inner html and append
        $a.innerHTML = `
            <div class="ac-photo-container` + featured + `">
                <img class="ac-photo" src="data:` + photo.type + ';base64,' + photo.blob + `">
                ` + $photosOthers + `
            </div>`
        $photosContainer.appendChild($a)
        i++
    } )

    // Build activity card
    var $card = document.createElement('div')
    $card.className = 'ac-card'
    $card.appendChild($mainContainer)
    $card.appendChild($photosContainer)

    return $card
}