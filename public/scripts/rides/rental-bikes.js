import CircleLoader from '/class/loaders/CircleLoader.js'

var select = document.querySelector('.js-br-field')
var container = document.querySelector('.rental-bikes-preview')

select.addEventListener('change', async (e) => {
    var rentalBike = await getBikeData(select.value)
    container.innerHTML = updateContainer(rentalBike)
})

// Set container on first loading
getBikeData(select.value).then((rentalBike) => container.innerHTML = updateContainer(rentalBike))

// Fetch bike data from database
async function getBikeData (id) {
    return new Promise((resolve, reject) => {
        var loader = new CircleLoader(container, {compact: true})
        loader.start()
        ajaxGetRequest('/api/rental-bikes.php?rental_bike_id=' + id, async (rentalBike) => {
            loader.stop()
            resolve(rentalBike)
        })
    })
}

// Get container html
function updateContainer (rentalBike) {
    console.log(rentalBike)
    if (rentalBike) {
        document.querySelector('#bikesContract').style.display = 'block' // Display contract
        if (rentalBike.ebike) var ebike = '<p style="color: red">電動アシスト付き自転車</p>'
        else var ebike = ''
        return `
            <div class="rental-bike-image-container">
                <div class="rental-bike-name"><p>` + rentalBike.name + `</p></div>
                <img src="` + rentalBike.photo_url + `" />
            </div>
            <div class="rental-bike-details">
                <p>` + rentalBike.description + `</p>`
                + ebike + `
                <p><strong>車種：</strong>` + rentalBike.type + `</p>
                <p><strong>モデル：</strong>` + rentalBike.frame_model + `</p>
                <p><strong>サイズ：</strong>` + rentalBike.size + ` (` + rentalBike.allowed_height + `)</p>
                <p><strong>変速機：</strong>` + rentalBike.gears_number + `速</p>
                <p><strong>料金（運搬込み）：</strong>¥` + rentalBike.price_ride + `</p>
                <p>ヘルメット／フラットペダル付き</p>
            </div>
        `
    } else {
        document.querySelector('#bikesContract').style.display = 'none' // Hide contract
        return ''
    }
}