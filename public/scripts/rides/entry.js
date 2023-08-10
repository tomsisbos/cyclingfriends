const form = document.querySelector('#entry-form')
const button = document.querySelector('#next')
const fields = document.querySelectorAll('.js-field')

button.addEventListener('click', (e) => {
    e.preventDefault()
    
    // Only sumbit if all fields are filled
    let pass = true
    fields.forEach($field => {
        if ($field.value == null || $field.value == '' || $field.value == 'default') {
            if (!$field.parentElement.classList.contains('missing-field')) $field.parentElement.classList.add('missing-field')
            pass = false
        } else {
            if ($field.parentElement.classList.contains('missing-field')) $field.parentElement.classList.remove('missing-field')
        }
    })
    if (pass) form.submit()
})