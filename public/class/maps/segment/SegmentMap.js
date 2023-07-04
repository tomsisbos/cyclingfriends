import RouteMap from "/class/maps/route/RouteMap.js"

export default class SegmentMap extends RouteMap {
    
    constructor () {
        super()
        this.segmentId = document.querySelector('#routeMap').dataset.segmentid
    }

    getRouteData () {
        return new Promise ( (resolve, reject) => {
            if (this.data && this.routeData) {
                resolve(this.routeData)
            } else if (map.getSource('segment' + this.segmentId)) {
                resolve(map.getSource('segment' + this.segmentId)._data)
            } else {
                this.map.once('sourcedata', 'segment' + this.segmentId, (e) => {
                    if (e.isSourceLoaded == true) {
                        resolve(this.map.getSource('segment' + this.segmentId)._data)
                    }
                } )
            }
        } )
    }

}