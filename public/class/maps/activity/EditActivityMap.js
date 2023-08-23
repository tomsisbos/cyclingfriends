import NewActivityMap from "/class/maps/activity/NewActivityMap.js"
import FadeLoader from "/class/loaders/FadeLoader.js"

export default class EditActivityMap extends NewActivityMap {

    constructor () {
        super()
    }

    pageType = 'edit'
    apiUrl = '/api/activities/edit.php'

    populateForm () {
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
        $title.addEventListener('change', () => this.data.title = $title.value)
        $start.innerHTML = '<strong>スタート : </strong>' + this.data.checkpoints[0].geolocation.city + ' (' + this.data.checkpoints[0].geolocation.prefecture + ')'
        $goal.innerHTML = '<strong>ゴール : </strong>' + this.data.checkpoints[this.data.checkpoints.length - 1].geolocation.city + ' (' + this.data.checkpoints[this.data.checkpoints.length - 1].geolocation.prefecture + ')'
        $distance.innerHTML = '<strong>距離 : </strong>' + (Math.round(this.data.route.distance * 10) / 10) + 'km'
        $duration.innerHTML = '<strong>時間 : </strong>' + this.data.duration.h + 'h' + this.data.duration.i
        $elevation.innerHTML = '<strong>獲得標高 : </strong>' + this.data.route.elevation + 'm'
        $minTemperature.innerHTML = '<strong>最低気温 : </strong>' + this.data.temperature_min + '°C'
        $avgTemperature.innerHTML = '<strong>平均気温 : </strong>' + (Math.round(this.data.temperature_avg * 10) / 10) + '°C'
        $maxTemperature.innerHTML = '<strong>最高気温 : </strong>' + this.data.temperature_max + '°C'
        this.updateCheckpointForms()
    }

    async displayCheckpointMarkers () {
        for (let i = 1; i < this.data.checkpoints.length - 1; i++) {
            this.data.checkpoints[i].marker = await this.addMarkerOnRoute(this.data.checkpoints[i].lngLat, 'default')
        }
    }

    async saveActivity (sceneryPhotos = null, sceneriesToCreate = null) {
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

            // If photos need to be added to a scenery, append info data
            if (sceneryPhotos) cleanData.sceneryPhotos = sceneryPhotos
            
            // If sceneries need to be created, append data
            if (sceneriesToCreate) cleanData.sceneriesToCreate = sceneriesToCreate

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
                    if (photos.length > 0) {
                        photos.forEach(async (photo) => {
                            if (photo.blob instanceof Blob) {
                                await (async () => {
                                    return new Promise(async (resolve, reject) => {
                                        cleanData.photos.push( {
                                            blob: await blobToBase64(photo.blob),
                                            size: photo.size,
                                            name: photo.name,
                                            type: photo.type,
                                            lng: this.getPhotoLocation(photo)[0],
                                            lat: this.getPhotoLocation(photo)[1],
                                            datetime: photo.datetime,
                                            featured: photo.featured,
                                            privacy: photo.privacy
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
                                    lng: this.getPhotoLocation(photo)[0],
                                    lat: this.getPhotoLocation(photo)[1],
                                    filename: photo.filename,
                                    datetime: photo.datetime,
                                    featured: photo.featured,
                                    privacy: photo.privacy
                                } )
                            }
                            if (numberOfBlobs == 0) resolve()
                        } )
                    } else resolve()
                } )
            } ) ().then(
                () => {
                    // Send data to server
                    var loader = new FadeLoader('保存中...')
                    loader.start()
                    ajaxJsonPostRequest (this.apiUrl, cleanData, (response) => {
                        loader.setText('完了✓')
                        resolve(response)
                        window.location.replace('/activity/' + this.data.id)
                    })
                }
            )

            

        } )
    }

}