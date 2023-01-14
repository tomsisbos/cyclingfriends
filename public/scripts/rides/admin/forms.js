var addForm = document.querySelector('form#add')

// Display content field on field type selection
var typeInput = document.querySelector('#type')
typeInput.addEventListener('change', (e) => {
    var type = e.target.value
    addForm.querySelectorAll('.js-question').forEach(field => field.classList.add('hidden'))
    document.getElementById(type).classList.remove('hidden')
} )

// Add an option input on click on addOptionField for select type fields
var addOptionButtons = document.querySelectorAll('#addOptionField')
addOptionButtons.forEach(addOptionButton => {
    addOptionButton.addEventListener('click', () => {
        var optionsNumber = updateOptionsNumber(addOptionButton.closest('form'))
        var optionBlock = document.createElement('div')
        optionBlock.className = 'd-flex align-items-center rd-ad-options-block'
        var optionLabel = document.createElement('div')
        optionLabel.className = 'rd-ad-options-label'
        optionLabel.innerText = optionsNumber + '. '
        var optionInput = document.createElement('input')
        optionInput.type = 'text'
        optionInput.className = 'form-control rd-ad-options-input'
        optionInput.name = 'select_option_' + optionsNumber
        var optionRemoveButton = document.createElement('div')
        optionRemoveButton.className = 'btn smallButton rd-ad-options-remove'
        optionRemoveButton.innerText = 'x'
        optionRemoveButton.id = 'removeOption' + optionsNumber
        setRemoveEventListener(optionRemoveButton)
        optionBlock.appendChild(optionLabel)
        optionBlock.appendChild(optionInput)
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
    console.log(deleteForm)
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
console.log(saveEntryButton)
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
        var form = optionRemoveButton.closest('form')
        optionBlock.remove()
        updateOptionBlocks(form)
    } )
}

// Count inputs for updating select options number
function updateOptionsNumber (form) {
    return form.querySelectorAll('.rd-ad-options-input').length + 1
}

// Function for updating select options numbers
function updateOptionBlocks (form) {
    var blocks = form.querySelectorAll('.rd-ad-options-block')
    for (let i = 2; i < blocks.length; i++) {
        blocks[i].querySelector('.rd-ad-options-label').innerText = (i + 1) + '. '
        blocks[i].querySelector('.rd-ad-options-input').name = 'select_option_' + (i + 1)
        blocks[i].querySelector('.rd-ad-options-remove').id = 'removeOption' + (i + 1)
    }
    return blocks.length + 1
}