import CircleLoader from '/class/loaders/CircleLoader.js'

const $amount = document.querySelector("#amount")
const $amountDetails = document.querySelector("#amount-details")
const $additionalFields = document.querySelectorAll(".js-a-field")
const rideId = (new URL(window.location).pathname.match(/[^\/]+/g)).find(part => !isNaN(parseFloat(part)))

// Set amount on first display
updateAmount().then((amount) => updateAmountElement(amount))

// Recalculate amount on every additional field change
$additionalFields.forEach($aField => {
    $aField.addEventListener('change', async () => {
        if ($aField.dataset.type == 'product') updateAmountElement(await updateAmount($aField.dataset.fieldId, $aField.value))
    })
})

/**
 * Update products if necessary and query current provisional amount for this ride & user
 * @param {Number} fieldId 
 * @param {Number} optionId 
 * @returns {any} Amount object
 */
async function updateAmount (fieldId = null, optionId = null) {
    return new Promise((resolve, reject) => {
        let queryString = '/api/rides/amount.php' + "?ride=" + rideId
        console.log(fieldId, optionId)
        if (fieldId) queryString += '&fieldId=' + fieldId + '&optionId=' + optionId
        
        var loader = new CircleLoader($amount, {compact: true})
        loader.start()
        ajaxGetRequest(queryString, async (amount) => {
            console.log(amount)
            loader.stop()
            resolve(amount)
        })
    })
}

function updateAmountElement (amount) {
    $amount.innerText = amount.currency_symbol + amount.total
    $amountDetails.innerText = ''
    amount.products.forEach(product => $amountDetails.appendChild(buildProductElement(product, amount.currency_symbol)))
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