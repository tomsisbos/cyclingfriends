var addForm = document.querySelector('form#add')

// Display content field on field type selection
var typeInput = document.querySelector('#type')
typeInput.addEventListener('change', (e) => {
    var type = e.target.value
    addForm.querySelectorAll('.js-question').forEach(field => field.classList.add('hidden'))
    document.getElementById(type).classList.remove('hidden')
} )

// Add an option input on click on addOptionField for select type fields
var addOptionButton = document.querySelector('#addOptionField')
var optionsNumber = 3
addOptionButton.addEventListener('click', () => {
    var optionBlock = document.createElement('div')
    optionBlock.className = 'd-flex align-items-center'
    var optionLabel = document.createElement('div')
    optionLabel.className = 'rd-ad-options-label'
    optionLabel.innerText = optionsNumber + '. '
    var optionInput = document.createElement('input')
    optionInput.type = 'text'
    optionInput.className = 'form-control rd-ad-options-input'
    optionInput.name = 'select_option_' + optionsNumber
    optionBlock.appendChild(optionLabel)
    optionBlock.appendChild(optionInput)
    addOptionButton.before(optionBlock)
    optionsNumber++
} )

