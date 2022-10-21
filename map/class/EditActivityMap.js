import NewActivityMap from "/map/class/NewActivityMap.js"

export default class EditActivityMap extends NewActivityMap {

    constructor () {
        super()
    }

    pageType = 'edit'
    apiUrl = '/actions/activities/editApi.php'

    updateForm () {
        const $form = document.querySelector('#activityForm')
        var $title = $form.querySelector('#inputTitle')
        var $start = $form.querySelector('#divStart')
        var $goal = $form.querySelector('#divGoal')
        var $distance = $form.querySelector('#divDistance')
        var $duration = $form.querySelector('#divDuration')
        var $elevation = $form.querySelector('#divElevation')
        var $minTemperature = $form.querySelector('#divMinTemperature')
        var $avgTemperature = $form.querySelector('#divAvgTemperature')
        var $maxTemperature = $form.querySelector('#divMaxTemperature')
        if (this.data.title != $title.value) $title.value = this.data.title
        $start.innerHTML = '<strong>Start : </strong>' + this.data.checkpoints[0].geolocation.city + ' (' + this.data.checkpoints[0].geolocation.prefecture + ')'
        $goal.innerHTML = '<strong>Goal : </strong>' + this.data.checkpoints[this.data.checkpoints.length - 1].geolocation.city + ' (' + this.data.checkpoints[this.data.checkpoints.length - 1].geolocation.prefecture + ')'
        $distance.innerHTML = '<strong>Distance : </strong>' + this.data.route.distance + 'km'
        $duration.innerHTML = '<strong>Duration : </strong>' + new Date(this.data.duration.date).getHours() + 'h' + new Date(this.data.duration.date).getMinutes()
        $elevation.innerHTML = '<strong>Elevation : </strong>' + this.data.route.elevation + 'm'
        $minTemperature.innerHTML = '<strong>Min. Temperature : </strong>' + this.data.temperature_min + '°C'
        $avgTemperature.innerHTML = '<strong>Avg. Temperature : </strong>' + this.data.temperature_avg + '°C'
        $maxTemperature.innerHTML = '<strong>Max. Temperature : </strong>' + this.data.temperature_max + '°C'
        this.updateCheckpointForms()
    }

    displayCheckpointMarkers () {
        for (let i = 1; i < this.data.checkpoints.length - 1; i++) {
            this.data.checkpoints[i].marker = this.addMarkerOnRoute(this.data.checkpoints[i].lngLat, 'default')
        }
    }

    async saveActivity () {
        return new Promise( async (resolve, reject) => {
            
            // Remove trackpoints and photos data
            var cleanData = {}
            for (var key in this.data) {
                if (key != 'trackpoints' && key != 'photos' && key != 'route' && key != 'routeData') cleanData[key] = this.data[key]
            }
            // Remove marker data
            cleanData.checkpoints.forEach(checkpoint => {
                delete checkpoint.marker
            } )

            // Prepare photo blobs upload
            const photos = this.data.photos
            cleanData.photos = []
            // Count the number of blobs to treat
            var numberOfBlobs = 0
            photos.forEach( (photo) => {
                if (photo.blob instanceof Blob) {
                    numberOfBlobs++
                }
            } )
            var numberOfBlobsTreated = 0;
            (async () => {
                return new Promise(async (resolve, reject) => {
                    photos.forEach(async (photo) => {
                        if (photo.blob instanceof Blob) {
                            await (async () => {
                                return new Promise(async (resolve, reject) => {
                                    cleanData.photos.push( {
                                        blob: await blobToBase64(photo.blob),
                                        size: photo.size,
                                        name: photo.name,
                                        type: photo.type,
                                        datetime: photo.datetime,
                                        featured: photo.featured
                                    } )
                                    numberOfBlobsTreated++
                                    if (numberOfBlobs == numberOfBlobsTreated) resolve()
                                } )
                            } ) ()
                            resolve()
                        } else {
                            cleanData.photos.push( {
                                blob: photo.blob,
                                size: photo.size,
                                name: photo.name,
                                type: photo.type,
                                datetime: photo.datetime,
                                featured: photo.featured
                            } )
                        }
                        if (numberOfBlobs == 0) resolve()
                    } )
                } )
            } ) ().then(
                () => {
                    console.log(cleanData)
                    // Send data to server
                    ajaxJsonPostRequest (this.apiUrl, cleanData, (response) => {
                        resolve(response)
                        window.location.replace('/activities/myactivities.php')
                    } )
                }
            )

            

        } )
    }

}