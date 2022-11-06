export default class CFUtils {

    // Find the closest coordinate of another coordinate among a coordinates array
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
            /*console.log('Twin : ' + twinPointDistance + ', Original :' + distance + ', Difference : ' + difference)*/
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
        return [[parseFloat(minlng) - margin, parseFloat(minlat) - margin], [parseFloat(maxlng) + margin, parseFloat(maxlat) + margin]]
    }

    // Get location of a LngLat point
static async getLocation (lngLat) {
	return new Promise ((resolve, reject) => {
		var lng = lngLat.lng
		var lat = lngLat.lat
		ajaxGetRequest ('https://api.mapbox.com/search/v1/reverse/' + lng + ',' + lat + '?language=ja&access_token=' + this.apiKey, callback)
		function callback (response) {
			console.log('MAPBOX GEOCODING API USE +1')
			var geolocation = CFUtils.reverseGeocoding (response)
			resolve (geolocation)
		}
	} )
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

    static compareCoords (coords1, coords2, decimal = 1) {
        const multiplicator = Math.pow(10, decimal)
        var rounded1 = {lng: Math.round(coords1.lng * multiplicator) / multiplicator, lat: Math.round(coords1.lat * multiplicator) / multiplicator}
        var rounded2 = {lng: Math.round(coords2.lng * multiplicator) / multiplicator, lat: Math.round(coords2.lat * multiplicator) / multiplicator}
        if (rounded1.lat == rounded2.lat && rounded1.lng == rounded2.lng) return true
        else return false
    }

    // Test if an array of coordinates has a part inside bounds
    static isInsideBounds (bounds, rideCoords) {

        const precision = 10 // unity : number of coordinates
        const limit     = 100 // unity : number of tests

        // Function for calculating a pair of coordinates are inside bounds
        function coordsInsideBounds(coords, bounds) {
            if ((coords[1] < bounds._ne.lat && coords[1] > bounds._sw.lat) && (coords[0] < bounds._ne.lng && coords[0] > bounds._sw.lng)) {
                return true
            }
        }

        // Return true if any of every [precision] coordinates is inside bounds (max: [limit] number of tests)
        var cursor = Math.floor(rideCoords.length / precision)
        if (cursor < 2) cursor = 2
        if (cursor > limit) cursor = limit
        for (let index = 0; index < cursor; index++) {
            if (coordsInsideBounds(rideCoords[index * precision], bounds)) return true
        }
        return false
    }
    
    // Return GPX file URL from a geojson source
    static loadGpx (geojson) {
        var gpx = togpx(geojson)
        const file = new File ([gpx], 'route', {type: 'application/gpx'})
        return URL.createObjectURL(file)
    }

    // Get amenity name from layer id
    static getAmenityName (amenity) {
        if      (amenity == 'toilets') return 'Toilets'
        else if (amenity == 'drinking-water') return 'Water spot'
        else if (amenity == 'vending-machine-drinks') return 'Vending machine'
        else if (amenity == 'seven-eleven') return 'Seven Eleven'
        else if (amenity == 'family-mart' || amenity == 'mb-family-mart') return 'Family Mart'
        else if (amenity == 'lawson') return 'Lawson'
        else if (amenity == 'mini-stop') return 'Mini Stop'
        else if (amenity == 'daily-yamazaki') return 'Daily Yamazaki'
        else if (amenity == 'michi-no-eki') return 'Michi no Eki'
        else if (amenity == 'onsens') return 'Onsen'
        else if (amenity == 'footbaths') return 'Footbath'
        else if (amenity == 'rindos') return 'Rindo'
        else if (amenity == 'cycle-path' || amenity == 'cycle-path-case') return 'Cycling Road'
        else return 'Amenity'
    }

    static getSurfaceFromvalue (value) {
        switch (value) {
            case 'paved': return 'Asphalt';
            case 'asphalt': return 'Asphalt';
            case 'concrete': return 'Concrete';
            case 'gravel': return 'Gravel';
            case 'unpaved': return 'Gravel';
            case 'dirt': return 'Dirt';
            case 'grass': return 'Unrideable';
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
        switch (period['detail']) {
            case 1: first = 'early '; break;
            case 2: first = 'mid '; break;
            case 3: first = 'late '; break;
        }
        switch (period['month']) {
            case 1: second = 'january'; break;
            case 2: second = 'february'; break;
            case 3: second = 'march'; break;
            case 4: second = 'april'; break;
            case 5: second = 'may'; break;
            case 6: second = 'june'; break;
            case 7: second = 'july'; break;
            case 8: second = 'august'; break;
            case 9: second = 'september'; break;
            case 10: second = 'october'; break;
            case 11: second = 'november'; break;
            case 12: second = 'december'; break;
        }
        return first + second
    }
}