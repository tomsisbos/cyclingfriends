export default class CFUtils {

    /**
     * Find the closest coordinate of another coordinate among a coordinates array
     *
     */
    static closestLocation (targetLocation, locationData) {

        function vectorDistance(dx, dy) {
            return Math.sqrt(dx * dx + dy * dy)
        }

        function locationDistance(location1, location2) {
            var dx = location1[0] - location2[0],
                dy = location1[1] - location2[1]

            return vectorDistance(dx, dy)
        }

        return locationData.reduce(function(prev, curr) {
            var prevDistance = locationDistance(targetLocation , prev),
                currDistance = locationDistance(targetLocation , curr)
            return (prevDistance < currDistance) ? prev : curr
        } )
    }

    // Same as closestLocation, but works with geojson
    static replaceOnRoute (coordinates, routeData, options = {lngLat: false}) {
        var routeCoordinates = routeData.geometry.coordinates
        if (options.lngLat) {
            var closestLocation = this.closestLocation(coordinates, routeCoordinates, options)
            return {
                lng: closestLocation[0],
                lat: closestLocation[1]
            }
        }

        else return this.closestLocation(coordinates, routeCoordinates, options)
    }

    // Find index of a specified coordinate among a coordinates array
    static getCoordIndex (lngLat, coordinates) {
        for (let i = 0; i < coordinates.length; i++) {
            if (coordinates[i] == lngLat) return i
        }
    }

    // Find the other distance number corresponding to a point where route passes twice
    static findDistanceWithTwins (routeData, lngLat) {

        // Distance
        var routeCoords = routeData.geometry.coordinates
        var pointCoords = [lngLat.lng, lngLat.lat]
        var subline = turf.lineSlice(routeCoords[0], turf.point(pointCoords), turf.lineString(routeCoords))
        var distance = Math.floor(turf.length(subline) * 10) / 10

        // Calculate difference with other point on the course (ex: case of passing multiple times on the same route)
        var routeCoords = routeData.geometry.coordinates
        var closestRouteCoords = CFUtils.closestLocation(pointCoords, routeCoords)
        var closestRouteCoordsKey = parseInt(getKeyByValue(routeCoords, closestRouteCoords))
        var sublineDifference = turf.lineSlice(turf.point(pointCoords), turf.point(routeCoords[closestRouteCoordsKey]), turf.lineString(routeCoords))
        var difference = Math.floor(turf.length(sublineDifference) * 10) / 10

        // Calculate twin distance
        var twinDistance
        // Check if there is another point less from 0.5km to the same point when adding and substracting the difference to original distance calculation
        // in order to define if negative or positive difference
        if (difference > 0.5) {
            const routeDistance = turf.length(routeData)
            var pointAddedDifference = turf.along(routeData, distance + difference)
            if (distance - difference > 0) var pointSubstractedDifference = turf.along(routeData, distance - difference)
            if (distance + difference < routeDistance && Math.abs(turf.distance(turf.point(pointCoords), pointAddedDifference)) < 0.5) var twinDistance = Math.floor((distance + difference) * 10) / 10
            else if (distance - difference > 0 && Math.abs(turf.distance(turf.point(pointCoords), pointSubstractedDifference)) < 0.5) var twinDistance = Math.floor((distance - difference) * 10) / 10
        }
        return {distance, twinDistance}
    }

    // Get a smooth version of a path
    static smoothLine (routeData) {
        if (routeData.geometry.coordinates > 1000) var simplifiedLine = turf.simplify(routeData, {tolerance: 0.001, highQuality: false, mutate: false})
        else simplifiedLine = routeData
        return turf.bezierSpline(simplifiedLine, {resolution: 10000, sharpness: 1.5})
    }

    // Define bounds according to existing markers latLng
    static defineRouteBounds (coordinates) { 
        const margin = 0.02
        var maxlng = coordinates[0][0]
        var minlng = coordinates[0][0]
        var maxlat = coordinates[0][1]
        var minlat = coordinates[0][1]
        for (let i = 0; i < coordinates.length; i++) {
            if (coordinates[i][0] > maxlng) {
                maxlng = parseFloat(coordinates[i][0])
            }
            if (coordinates[i][0] < minlng) {
                minlng = parseFloat(coordinates[i][0])
            }
            if (coordinates[i][1] > maxlat) {
                maxlat = parseFloat(coordinates[i][1])
            }
            if (coordinates[i][1] < minlat) {
                minlat = parseFloat(coordinates[i][1])
            }
        }
        return [[minlng - margin, minlat - margin], [maxlng + margin, maxlat + margin]]
    }

    static getRouteBbox (routeData) {
        const coordinates = routeData.geometry.coordinates
        const margin = 0.02
        var maxlng = coordinates[0][0]
        var minlng = coordinates[0][0]
        var maxlat = coordinates[0][1]
        var minlat = coordinates[0][1]
        for (let i = 0; i < coordinates.length; i++) {
            if (coordinates[i][0] > maxlng) {
                maxlng = parseFloat(coordinates[i][0])
            }
            if (coordinates[i][0] < minlng) {
                minlng = parseFloat(coordinates[i][0])
            }
            if (coordinates[i][1] > maxlat) {
                maxlat = parseFloat(coordinates[i][1])
            }
            if (coordinates[i][1] < minlat) {
                minlat = parseFloat(coordinates[i][1])
            }
        }
        const southWest = new mapboxgl.LngLat(minlng, minlat)
        const northEast = new mapboxgl.LngLat(maxlng, maxlat)
        const boundingBox = new mapboxgl.LngLatBounds(southWest, northEast)
        return boundingBox
    }

    static getWiderBounds (bbox, margin) {
        var m = margin / 10
        return [[bbox[0].lng - m, bbox[0].lat + m], [bbox[1].lng + m, bbox[1].lat - m]]
    }

    // Getting city and prefecture data from mapbox reverse geocoding API request response (used as a callback function)
    static reverseGeocoding (response) {
        var city, prefecture, skip = false
        // Look for prefecture data
        for (var i = 0; i < response.features[0].properties.context.length; i++) {
            if (response.features[0].properties.context[i].layer.includes('region') || response.features[0].properties.context[i].layer.includes('prefecture')) {
                var prefecture = response.features[0].properties.context[i].name
            }
        }
        // Look for city data
        for (var i = 0; i < response.features[0].properties.context.length; i++) {
            if (response.features[0].properties.context[i].layer.includes('locality')) {
                if (response.features[0].properties.context[i].name.includes('区')) {
                    if (prefecture === '東京都'){
                        city = response.features[0].properties.context[i].name
                        break
                    } else {
                        skip = true
                    }
                } else if (!skip) {
                    var city = response.features[0].properties.context[i].name
                break
                }
            }
            if (response.features[0].properties.context[i].layer.includes('place')) {
                var city = response.features[0].properties.context[i].name
                break
            }
            if (response.features[0].properties.context[i].layer.includes('city')) {
                var city = response.features[0].properties.context[i].name.match('^[^\(]+')[0] // Everything before parenthesis
                break
            }
        }
        return ({"city": city, "prefecture": prefecture})
    }

    static buildGeolocationFromString (string) {
        var regex1     = /[^\s]+/
	    var city       = string.match(regex1)[0]
        var regex2     = /\(([^)]+)\)/
        var prefecture = string.match(regex2)[1]
        return { city, prefecture }
    }

    /**
     * Check if two coordinates are similar or not at a certain decimal extent
     * @param {mapboxgl.lngLat} coords1 
     * @param {mapboxgl.lngLat} coords2 
     * @param {int} decimal number of decimals to round at before comparing
     * @returns {boolean}
     */
    static compareCoords (coords1, coords2, decimal = 4) {
        const multiplicator = Math.pow(10, decimal)
        var rounded1 = {lng: Math.round(coords1.lng * multiplicator) / multiplicator, lat: Math.round(coords1.lat * multiplicator) / multiplicator}
        var rounded2 = {lng: Math.round(coords2.lng * multiplicator) / multiplicator, lat: Math.round(coords2.lat * multiplicator) / multiplicator}
        if (rounded1.lng == rounded2.lng && rounded1.lat == rounded2.lat) return true
        else return false
    }

    /**
     * Test if an array of coordinates has a part inside bounds
     * @param {array[]} coordinates array of coordinate arrays
     * @param {array[]} bounds array of two coordinates
     * @returns {boolean}
     */
    static lineCoordsInsideBounds (coordinates, bounds) {        
        var inside = false
        for (let i = 0; i < coordinates.length && !inside; i++) {
            if (CFUtils.coordInsideBounds(coordinates[i], bounds)) inside = true
        }
        return inside
    }

    /**
     * Function for calculating a pair of coordinates are inside bounds
     * @param {float[]} coords
     * @param {array[]} bounds array of two coordinates
     * @returns {boolean}
     */
    static coordInsideBounds (coords, bounds) {
        if ((coords[0] > bounds[0][0] && coords[0] < bounds[1][0]) && (coords[1] > bounds[0][1] && coords[1] < bounds[1][1])) {
            return true
        }
    }
    
    // Return GPX file URL from a geojson source
    static loadGpx (geojson) {
        var gpx = togpx(geojson)
        const file = new File ([gpx], 'route', {type: 'application/gpx'})
        return URL.createObjectURL(file)
    }

    // Get amenity name from layer id
    static getAmenityName (amenity) {
        if      (amenity == 'toilets') return 'トイレ'
        else if (amenity == 'drinking-water') return '水補給'
        else if (amenity == 'vending-machine-drinks') return '自販機'
        else if (amenity == 'bicycle-rentals') return 'レンタルサイクル'
        else if (amenity == 'seven-eleven') return 'Seven Eleven'
        else if (amenity == 'family-mart' || amenity == 'mb-family-mart') return 'Family Mart'
        else if (amenity == 'lawson') return 'Lawson'
        else if (amenity == 'mini-stop') return 'Mini Stop'
        else if (amenity == 'daily-yamazaki') return 'Daily Yamazaki'
        else if (amenity == 'michi-no-eki') return '道の駅'
        else if (amenity == 'onsens') return '温泉'
        else if (amenity == 'footbaths') return '足湯'
        else if (amenity == 'rindos') return '林道'
        else if (amenity == 'cycle-path' || amenity == 'cycle-path-case') return 'サイクリングロード'
        else return 'Amenity'
    }

    static getSurfaceFromvalue (value) {
        switch (value) {
            case 'paved': return '舗装';
            case 'asphalt': return '舗装';
            case 'concrete': return 'コンクリート';
            case 'gravel': return 'グラベル';
            case 'unpaved': return 'グラベル';
            case 'dirt': return 'ダート';
            case 'grass': return '走行不能';
            default: return value 
        }
    }

    static rasterize (image) {
        let rasterizedImage = new Image(20, 20)
        rasterizedImage.src = image
        return rasterizedImage
    }

    static roundCoords (lngLat, decimalsNumber) {
        const multiplicator = Math.pow(10, decimalsNumber)
        if (lngLat.lng) {
            var lng = Math.round(lngLat.lng * multiplicator) / multiplicator
            var lat = Math.round(lngLat.lat * multiplicator) / multiplicator
            return {lng, lat}
        } else {
            var lng = Math.round(lngLat[0] * multiplicator) / multiplicator
            var lat = Math.round(lngLat[1] * multiplicator) / multiplicator
            return [lng, lat]
        }
    }

    static getPeriodString (period) {
        var first, second
        first = this.getMonth(period['month'])
        switch (period['detail']) {
            case 1: second = '上旬'; break;
            case 2: second = '中旬'; break;
            case 3: second = '下旬'; break;
        }
        return first + second
    }

    static getMonth (number) {
        switch (number) {
            case 1: return '1月'
            case 2: return '2月'
            case 3: return '3月'
            case 4: return '4月'
            case 5: return '5月'
            case 6: return '6月'
            case 7: return '7月'
            case 8: return '8月'
            case 9: return '9月'
            case 10: return '10月'
            case 11: return '11月'
            case 12: return '12月'
        }
    }

    static getTagString (tag) {
        switch (tag) {
            case 'hanami-sakura': return '桜';
            case 'hanami-ume': return '梅';
            case 'hanami-nanohana': return '菜の花';
            case 'hanami-ajisai': return '紫陽花';
            case 'hanami-himawari': return 'ひまわり';

            case 'nature-forest': return '森';
            case 'nature-kouyou': return '紅葉';
            case 'nature-ricefield': return '田んぼ';
            case 'nature-riceterraces': return '棚田';
            case 'nature-teafield': return '茶畑';
            
            case 'water-sea': return '海';
            case 'water-river': return '川';
            case 'water-lake': return '湖';
            case 'water-dam': return 'ダム';
            case 'water-waterfall': return '滝';

            case 'culture-culture': return '文化';
            case 'culture-history': return '歴史';
            case 'culture-machinami': return '街並み';
            case 'culture-shrines': return '寺・神社';
            case 'culture-hamlet': return '集落';

            case 'terrain-pass': return '峠';
            case 'terrain-mountains': return '山';
            case 'terrain-viewpoint': return '見晴らし';
            case 'terrain-tunnel': return 'トンネル';
            case 'terrain-bridge': return '橋';

            default: 'その他'
        }
    }

    static getMapIcon (filename) {
        return new Promise ((resolve, reject) => {
            ajaxGetRequest('/api/map.php?get-icon=' + filename, (response) => resolve(response))
        } )
    }

    /**
     * Check if a month is inside a period in months
     * @param {int} month month to check
     * @param {int[]} period array of two months representing a period 
     */
    static monthInsidePeriod (month, period) {
        if (period[0] < period[1]) {
            if ((month >= period[0] && month <= period[1])) return true
            else return false
        // In case end of period corresponds to following year (ex: november to january)
        } else {
            if (month >= period[0]) return true
            else return false
        }
    }

    /**
     * Get privacy string from privacy value
     * @param {String} value
     */
    static getPrivacyString (value) {
        switch (value) {
            case 'public': return '公開';
            case 'friends_only': return '友達のみ';
            case 'limited': return '限定公開';
            case 'private': return '非公開';
        }
    }
}