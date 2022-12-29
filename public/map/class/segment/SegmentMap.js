import RoutePageMap from "/map/class/route/RoutePageMap.js"

export default class SegmentMap extends RoutePageMap {
    
    constructor () {
        super()
        this.segmentId = getIdFromString(location.pathname)
    }

}