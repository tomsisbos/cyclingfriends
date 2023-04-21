document.querySelectorAll('.js-favorite-button').forEach( ($button) => {

    $button.addEventListener('click', () => {
        // Get scenery
        if (document.querySelector('.mk-card')) {
            var id = getIdFromString($button.closest('.fav-card').querySelector('.mk-card').dataset.id)
            var type = 'scenery'
        } else if (document.querySelector('.sg-card')) {
            var id = getIdFromString($button.closest('.fav-card').querySelector('.sg-card').dataset.id)
            var type = 'segment'
        } else {
            var id = getIdFromString(location.pathname)
            if (location.pathname.includes('segment')) var type = 'segment'
            if (location.pathname.includes('scenery')) var type = 'scenery'
        }
        ajaxGetRequest ('/api/favorites.php' + '?toggle-' + type + '=' + id, (response) => {
            showResponseMessage(response)
            if ($button.innerText == '追加') $button.innerText = '除外'
            else $button.innerText = '追加'
            if ($button.classList.contains('bg-darkred')) {
                $button.classList.remove('bg-darkred')
                $button.classList.add('bg-darkgreen')
            } else if ($button.classList.contains('bg-darkgreen')) {
                $button.classList.remove('bg-darkgreen')
                $button.classList.add('bg-darkred')
            }
        } )
    } )

} )