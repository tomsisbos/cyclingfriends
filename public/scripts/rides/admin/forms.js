var addForm = document.querySelector('form#add')
var typeInput = document.querySelector('#type')

// Display content field on field type selection
typeInput.addEventListener('change', (e) => {
    var type = e.target.value
    addForm.querySelectorAll('.js-question').forEach(field => field.classList.add('hidden'))
    document.getElementById(type).classList.remove('hidden')
} )

