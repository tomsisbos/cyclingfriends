var $button = document.querySelector('#favoriteButton')

$button.addEventListener('click', () => {
    var id = getIdFromString(location.pathname)
    if (location.pathname.includes('segment')) var type = 'segment'
    if (location.pathname.includes('scenery')) var type = 'scenery'
    ajaxGetRequest ('/api/favorites.php' + '?toggle-' + type + '=' + id, (response) => {
        showResponseMessage(response)
        if ($button.innerText == 'Add to favorites') $button.innerText = 'Remove from favorites'
        else $button.innerText = 'Add to favorites'
    } )
} )