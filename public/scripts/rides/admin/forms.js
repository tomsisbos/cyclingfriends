var addForm = document.querySelector('form#add')

// Display content field on field type selection
var typeInput = document.querySelector('#type')
typeInput.addEventListener('change', (e) => {
    var type = e.target.value
    addForm.querySelectorAll('.js-question').forEach(field => field.classList.add('hidden'))
    document.getElementById(type).classList.remove('hidden')
} )

// Add an option input on click on js-add-option-field for select type fields
var addOptionButtons = document.querySelectorAll('.js-add-option-field')
addOptionButtons.forEach(addOptionButton => {
    addOptionButton.addEventListener('click', () => {
        const type = addOptionButton.closest('.js-question').id
        var optionsNumber = updateOptionsNumber(addOptionButton.closest('form'), type)
        var optionBlock = document.createElement('div')
        optionBlock.className = 'd-flex gap align-items-center rd-ad-options-block'
        var optionLabel = document.createElement('div')
        optionLabel.className = 'rd-ad-options-label'
        optionLabel.innerText = optionsNumber + '. '
        var nameFloating = document.createElement('div')
        nameFloating.className = 'form-floating'
        var optionInput = document.createElement('input')
        optionInput.type = 'text'
        optionInput.className = 'form-control rd-ad-options-input'
        optionInput.name = 'select_option_' + optionsNumber
        optionInput.id = type + 'Name'
        var nameLabel = document.createElement('label')
        nameLabel.setAttribute('for', type + 'Name')
        if (type == 'product') nameLabel.innerText = '商品名'
        else nameLabel.innerText = '選択肢'
        nameFloating.appendChild(optionInput)
        nameFloating.appendChild(nameLabel)
        // Add price input if type is product
        if (type == 'product') {
            var priceFloating = document.createElement('div')
            priceFloating.className = 'form-floating'
            var priceInput = document.createElement('input')
            priceInput.type = 'number'
            priceInput.className = 'form-control rd-ad-price-input'
            priceInput.name = 'select_price_' + optionsNumber
            priceInput.id = type + 'Price'
            var priceLabel = document.createElement('label')
            priceLabel.setAttribute('for', type + 'Price')
            priceLabel.innerText = '価格'
            priceFloating.appendChild(priceInput)
            priceFloating.appendChild(priceLabel)
        }
        var optionRemoveButton = document.createElement('div')
        optionRemoveButton.className = 'btn smallButton rd-ad-options-remove'
        optionRemoveButton.innerText = 'x'
        optionRemoveButton.id = 'removeOption' + optionsNumber
        setRemoveEventListener(optionRemoveButton)
        optionBlock.appendChild(optionLabel)
        optionBlock.appendChild(nameFloating)
        if (type == 'product') optionBlock.appendChild(priceFloating)
        optionBlock.appendChild(optionRemoveButton)
        addOptionButton.before(optionBlock)
    } )
})

// Remove an option input on remove button click
var optionRemoveButtons = document.querySelectorAll('.rd-ad-options-remove')
optionRemoveButtons.forEach(optionRemoveButton => {
    setRemoveEventListener(optionRemoveButton)
} )

// Open a confirmation popup before deleting an entry
var deleteEntryButton = document.querySelector('#deleteEntry')
if (deleteEntryButton) deleteEntryButton.addEventListener('click', async (e) => {
    e.preventDefault()
    var deleteForm = e.target.closest('form')
    var answer = await openConfirmationPopup('この質問と共に、参加者の回答データも削除されます。宜しいですか？')
    if (answer) {
        var id = getIdFromString(deleteForm.id)
        deleteForm.querySelector('#onSubmit').name = 'delete'
        deleteForm.querySelector('#onSubmit').value = id
        deleteForm.submit()
    }
} )

// Prevent from saving a select field if less than two options filled

// Open a confirmation popup before deleting an entry
var saveEntryButton = document.querySelector('#editSave')
if (saveEntryButton) saveEntryButton.addEventListener('click', async (e) => {
    if (saveEntryButton.closest('form').querySelector('.rd-ad-options-container').querySelectorAll('input').length < 2) {
        e.preventDefault()
        showResponseMessage({error: '2つ以上の選択肢を設定してください。'}, {element: saveEntryButton.closest('form')})
    }
} )

// Display content field on field type selection for edition form
var typeInput = document.querySelector('#editType')
if (typeInput) typeInput.addEventListener('change', (e) => {
    var type = e.target.value
    addForm.querySelectorAll('.js-question').forEach(field => field.classList.add('hidden'))
    document.getElementById(type).classList.remove('hidden')
} )



// Set listener on option remove button
function setRemoveEventListener (optionRemoveButton) {
    optionRemoveButton.addEventListener('click', () => {
        var optionBlock = optionRemoveButton.closest('.rd-ad-options-block')
        var type = optionRemoveButton.closest('.js-question').id
        var form = optionRemoveButton.closest('form')
        optionBlock.remove()
        updateOptionBlocks(form, type)
    } )
}

// Count inputs for updating select options number
function updateOptionsNumber (form, type) {
    return form.querySelectorAll('#' + type + ' .rd-ad-options-input').length + 1
}

// Function for updating select options numbers
function updateOptionBlocks (form, type) {
    var blocks = form.querySelectorAll('#' + type + ' .rd-ad-options-block')
    console.log(blocks)
    for (let i = 2; i < blocks.length; i++) {
        blocks[i].querySelector('.rd-ad-options-label').innerText = (i + 1) + '. '
        blocks[i].querySelector('.rd-ad-options-input').name = 'select_option_' + (i + 1)
        blocks[i].querySelector('.rd-ad-options-remove').id = 'removeOption' + (i + 1)
    }
    return blocks.length + 1
}