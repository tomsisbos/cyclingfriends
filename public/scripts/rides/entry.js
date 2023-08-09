const form = document.querySelector('#entry-form')
const button = document.querySelector('#next')
const fields = document.querySelectorAll('.js-field')

button.addEventListener('click', (e) => {
    e.preventDefault()
    
    // Only sumbit if all fields are filled
    let pass = true
    fields.forEach($field => {
        console.log($field.value)
        if ($field.value == null || $field.value == '' || $field.value == 'default') {
            if (!$field.parentElement.classList.contains('missing-field')) $field.parentElement.classList.add('missing-field')
            pass = false
        } else {
            if ($field.parentElement.classList.contains('missing-field')) $field.parentElement.classList.remove('missing-field')
        }
    })
    console.log(form)
    if (pass) form.submit()
})