import LoaderCircle from '/map/class/loaders/LoaderCircle.js'

// Infinite scroll

const toleranceZone = 100 // Zone for infinite scroll to react in pixels
const $cardsContainer = document.querySelector('#threadContainer')
const photosquantity = parseInt($cardsContainer.dataset.photosquantity)
const limit = parseInt($cardsContainer.dataset.limit) / 2
var isInside = false
var isCurrentlyLoading = false
var offset = 0
if (document.querySelector('#infiniteScrollElement')) var infiniteScrollElement = document.querySelector('#infiniteScrollElement')
else var infiniteScrollElement = document
var loader = new LoaderCircle(document.querySelector('.js-loader-container'))

infiniteScrollElement.addEventListener('scroll', function (e) {

    var element = e.target

    // When scroll to the bottom zone of the dashboard, get next elements from dashboard api and display cards
    if (!isInside && !isCurrentlyLoading && ((element.scrollHeight - element.scrollTop) <= (element.clientHeight + toleranceZone))) {
        isInside = true
        offset += limit
        isCurrentlyLoading = true
        ajaxGetRequest('/api/dashboard.php?getThread=' + limit + ',' + offset + ',' + photosquantity, (response) => {
            response.forEach( (entry) => $cardsContainer.appendChild(buildDashboardCard(entry)))
            isCurrentlyLoading = false
        }, loader)
    } else isInside = false

} )

function buildDashboardCard (entry) {
    var $dashboardCard = document.createElement('div')
    $dashboardCard.className = 'dashboard-card'
    $dashboardCard.classList.add(entry.type)
    var $link = buildLink(entry)
    var $card = buildCard(entry)
    $dashboardCard.appendChild($link)
    $dashboardCard.appendChild($card)
    return $dashboardCard
}

function buildLink (entry) {
    if (entry.type === 'activity') {
        var title = 'アクティビティ'
        var url = 'activities'
    } else if (entry.type === 'mkpoint') {
        var title = '絶景スポット'
        var url = 'world'
    }
    var $link = document.createElement('div')
    $link.className = 'top-link'
    $link.innerHTML = '<a href="/' + url + '">' + title + '</a>'
    return $link
}

function buildCard (entry) {

    if (entry.type === 'activity') {

        var activity = entry

        // Build main container
        var $mainContainer = document.createElement('div')
        $mainContainer.className = 'ac-main-container'
        $mainContainer.innerHTML = `
            <div class="ac-infos-container">
                <div class="ac-user-details">
                    <div class="ac-user-propic">
                        <a href="/rider/` + activity.user_id + `">` + activity.propic + `</a>
                    </div>
                    <div class="ac-details">
                        <div class="ac-user-name">
                            <a href="/rider/` + activity.user_id + `">` + activity.user_login + `</a>
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
                        <div class="ac-spec-label"><strong>距離 : </strong></div>
                        <div class="ac-spec-value">` + Math.round(activity.route.distance / 10) * 10 + `<span class="ac-spec-unit"> km</span></div>
                    </div>
                    <div class="ac-spec">
                        <div class="ac-spec-label"><strong>時間 : </strong></div>
                        <div class="ac-spec-value">
                            ` + activity.formattedDuration + `
                        </div>
                    </div>
                    <div class="ac-spec">
                        <div class="ac-spec-label"><strong>獲得標高 : </strong></div>
                        <div class="ac-spec-value">` + activity.route.elevation + `<span class="ac-spec-unit"> m</span></div>
                    </div>
                    <div class="ac-spec">
                        <div class="ac-spec-label"><strong>平均速度 : </strong></div>
                        <div class="ac-spec-value">` + activity.averagespeed + `<span class="ac-spec-unit"> km/h</span></div>
                    </div>
                </div>
            </div>
            <div class="ac-thumbnail-container">
                <a href="/activity/` + activity.id + `">
                    <img class="ac-map-thumbnail" src="` + activity.routeThumbnail + `">
                </a>
            </div>`
            
        // Build photos container
        var $photosContainer = document.createElement('div')
        $photosContainer.className = 'ac-photos-container'
        var i = 1
        activity.photos.forEach( (photo) => {
            var $a = document.createElement('a')
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
                    <img class="ac-photo" src="` + photo.url + `">
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

    } else if (entry.type === 'mkpoint') {

        var mkpoint = entry

        // Build cleared icon
        if (entry.cleared) {
            var clearedIcon = `
            <div id="visited-icon" style="display: inline;" title="この絶景スポットを訪れました。">
                <a href="/activity/` + entry.cleared + `" target="_blank">
                    <span class="iconify" data-icon="akar-icons:circle-check-fill" data-width="20" data-height="20"></span>
                </a>
            </div>`
        } else var clearedIcon = ''

        // Build main container
        var $card = document.createElement('div')
        $card.className = 'mk-card'
        $card.innerHTML = `
        <div class="mk-photo"><img src="` + mkpoint.featuredimageUrl + `"></div>
        <div class="mk-data">
            <div class="mk-top">
                <a href="/rider/` + mkpoint.user_id + `">` +
                    mkpoint.propic + `
                </a>
                <div class="mk-top-text">
                    <a href="/scenery/` + mkpoint.id + `">
                        <div class="mk-title">` + mkpoint.name + `</div>
                    </a>` + clearedIcon + `
                    <div class="mk-place-elevation">` + mkpoint.city + `(` + mkpoint.prefecture + `) - ` + mkpoint.elevation + `m</div>
                </div>
            </div>
            <div class="mk-description">` + mkpoint.description + `</div>
        </div>`
    }

    return $card
}