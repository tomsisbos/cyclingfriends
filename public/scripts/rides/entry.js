const form = document.querySelector('#entry-form')
const button = document.querySelector('#next')
const fields = document.querySelectorAll('.js-field')

button.addEventListener('click', (e) => {
    e.preventDefault()

    // Only submit if contract agreement checkbox is checked
    const $agreement = document.querySelector('#agreement')
    if (!$agreement.checked) showResponseMessage({'error': 'エントリーを頂くには、ツアー規約に賛同して頂く必要があります。'})
    else {
        
        // Only sumbit if all fields are filled
        let pass = true
        fields.forEach($field => {
            if ($field.value == null || $field.value == '' || $field.value == 'off' || $field.value == 'default') {
                if (!$field.parentElement.classList.contains('missing-field')) $field.parentElement.classList.add('missing-field')
                pass = false
            } else {
                if ($field.parentElement.classList.contains('missing-field')) $field.parentElement.classList.remove('missing-field')
            }
        })
        if (pass) form.submit()
    }
})