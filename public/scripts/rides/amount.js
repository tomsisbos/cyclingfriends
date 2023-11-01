import CircleLoader from '/class/loaders/CircleLoader.js'

const $amount = document.querySelector("#amount")
const $amountDetails = document.querySelector("#amount-details")
const $additionalFields = document.querySelectorAll(".js-a-field")
const $bikeRentalField = document.querySelector(".js-br-field")
const rideId = (new URL(window.location).pathname.match(/[^\/]+/g)).find(part => !isNaN(parseFloat(part)))

// Set amount on first display
additionalFieldsAmountUpdate().then((amount) => updateAmountElement(amount))

// Recalculate amount on every additional field change
$additionalFields.forEach($aField => {
    $aField.addEventListener('change', async () => {
        if ($aField.dataset.type == 'product') updateAmountElement(await additionalFieldsAmountUpdate($aField.dataset.fieldId, $aField.value))
    })
})

// Recalculate amount on bike rental field change
if ($bikeRentalField) $bikeRentalField.addEventListener('change', async () => updateAmountElement(await bikeRentalAmountUpdate($bikeRentalField.value)))

/**
 * Update products if necessary and query current provisional amount for this ride & user
 * @param {Number} fieldId 
 * @param {Number} optionId 
 * @returns {any} Amount object
 */
async function additionalFieldsAmountUpdate (fieldId = null, optionId = null) {
    return new Promise((resolve, reject) => {
        let queryString = '/api/rides/amount.php' + "?ride=" + rideId
        if (fieldId) queryString += '&fieldId=' + fieldId + '&optionId=' + optionId
        
        var loader = new CircleLoader($amount, {compact: true})
        loader.start()
        ajaxGetRequest(queryString, async (amount) => {
            loader.stop()
            resolve(amount)
        })
    })
}

/**
 * Update products if necessary and query current provisional amount for this ride & user
 * @param {Number} bikeId 
 * @returns {any} Amount object
 */
async function bikeRentalAmountUpdate (rentalBikeId) {
    return new Promise((resolve, reject) => {
        let queryString = '/api/rides/amount.php' + "?ride=" + rideId
        if (rentalBikeId) queryString += '&rentalBikeId=' + rentalBikeId
        else queryString += '&rentalBikeId=none' 
        
        var loader = new CircleLoader($amount, {compact: true})
        loader.start()
        ajaxGetRequest(queryString, async (amount) => {
            loader.stop()
            resolve(amount)
        })
    })
}

function updateAmountElement (amount) {
    $amount.innerText = amount.currency_symbol + amount.total
    $amountDetails.innerText = ''
    amount.products.forEach(product => $amountDetails.appendChild(buildProductElement(product, amount.currency_symbol)))
    amount.discounts.forEach(discount => $amountDetails.appendChild(buildDiscountElement(discount, amount.currency_symbol)))
}

function buildProductElement (product, currency_symbol) {
    var $productBlock = document.createElement('div')
    $productBlock.className = 'product-block'
    var $product = document.createElement('div')
    $product.className = 'product'
    var $productPrice = document.createElement('div')
    $productPrice.className = 'product-price'
    $productPrice.innerText = currency_symbol + (product.price * product.quantity)
    $product.appendChild($productPrice)
    var $productName = document.createElement('div')
    $productName.className = 'product-name'
    $productName.innerText = product.name
    $product.appendChild($productPrice)
    $product.appendChild($productName)
    $productBlock.appendChild($product)
    return $productBlock
}

function buildDiscountElement (discount, currency_symbol) {
    var $discountBlock = document.createElement('div')
    $discountBlock.className = 'product-block'
    var $discount = document.createElement('div')
    $discount.className = 'product bg-darkgreen'
    var $discountPrice = document.createElement('div')
    $discountPrice.className = 'product-price'
    $discountPrice.innerText = currency_symbol + discount.price
    $discount.appendChild($discountPrice)
    var $discountName = document.createElement('div')
    $discountName.className = 'product-name'
    $discountName.innerText = discount.name
    $discount.appendChild($discountPrice)
    $discount.appendChild($discountName)
    $discountBlock.appendChild($discount)
    return $discountBlock
}