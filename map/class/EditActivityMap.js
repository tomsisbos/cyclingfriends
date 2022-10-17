import NewActivityMap from "/map/class/NewActivityMap.js"
import GPX from '/node_modules/gpx-parser-builder/src/gpx.js';

export default class EditActivityMap extends NewActivityMap {

    constructor () {
        super()
    }

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

}