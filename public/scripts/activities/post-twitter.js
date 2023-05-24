import CircleLoader from "/class/loaders/CircleLoader.js"

const charsLimit = 280
const urlLength = 25
var buttonTwitter = document.querySelector('#buttonTwitter')

// If user is connected to twitter
if (buttonTwitter.dataset.username) buttonTwitter.addEventListener('click', () => {

    // Open modal
    openTwitterModal()

})

function openTwitterModal () {
    
    // Build modal
    var modal = document.createElement('div')
    modal.classList.add('modal', 'd-flex')
    document.querySelector('body').appendChild(modal)

    // Popup content
    var popup = document.createElement('div')
    popup.classList.add('popup')
    modal.appendChild(popup)
    var $content = document.createElement('div')
    popup.appendChild($content)
    var dataLoader = new CircleLoader($content, {compact: true})
    dataLoader.start()
    var buttons = document.createElement('div')
    buttons.className = 'd-flex justify-content-evenly'
    buttons.innerHTML = '<button id="back" class="mp-button bg-darkred text-white">戻る</button><div class="tw-counter"></div><button id="post" class="mp-button bg-darkgreen text-white">投稿</button>'
    popup.appendChild(buttons)

    // Load activity data
    var activity_id = getIdFromString(location.pathname)
    ajaxGetRequest ('/api/activity.php' + "?load=" + activity_id, async (activity) => {
    
        console.log(activity)
        dataLoader.stop()

        var name = buttonTwitter.dataset.username
        var username = buttonTwitter.dataset.username
        var profileImage = buttonTwitter.dataset.profileImage
        var date = new Date()
        var text = activity.checkpoints.map(checkpoint => checkpoint.story).join('\r\n\r\n')
        if (lengthInUtf8Bytes(text) > charsLimit - urlLength - 3) text = sliceInUtf8Bytes(text, charsLimit - urlLength - 3) + '...'

        var content = `
            <div class="tw-container">
                <div class="tw-profile">
                    <img src="` + profileImage +  `">
                </div>
                <div class="tw-body">
                    <div class="tw-top">
                        <div class="tw-name">` + name + `</div>
                        <div class="tw-username">@` + username + `</div>
                        <div class="tw-date">・` + date.toLocaleDateString('ja-JP') + `</div>
                    </div>
                    <textarea class="tw-content">` + text + `</textarea>
                    <div class="tw-link">` + window.location + `</div>
                </div>
            </div>
        `
        $content.innerHTML = content

        // Listeners
        const $text    = modal.querySelector('textarea.tw-content')
        const $counter = modal.querySelector('.tw-counter')
        const $back    = modal.querySelector('#back')
        const $post    = modal.querySelector('#post')
        displayTweetCharsCount($text.value, $counter)
        $text.addEventListener('keyup', () => {
            var number = displayTweetCharsCount($text.value, $counter)
            if (number < 0) $post.setAttribute('disabled', 'disabled')
            else $post.removeAttribute('disabled')
        })
        $back.addEventListener('click', () => modal.remove())
        $post.addEventListener('click', () => {

            // Prepare media
            const mediaMaxLength = 4
            var photos = []
            for (let i = 0; i < mediaMaxLength && i < activity.photos.length; i++) photos.push(activity.photos[i].url)

            // Prepare text
            let text = document.querySelector('.tw-content').value + '\r\n\r\n' + window.location

            // Prepare tweet data
            var tweet = {text, photos}
            
            var postLoader = new CircleLoader($content, {compact: true})
            postLoader.start()

            ajaxJsonPostRequest('/api/twitter/post.php', tweet, (response) => {
                console.log(response)
                if (response.errors) showResponseMessage({error: '投稿に失敗しました。（' + response.errors[0].message + '）'}, {absolute: true})
                else if (!response.data) showResponseMessage({error: '投稿に失敗しました。（' + response.detail + '）'}, {absolute: true})
                else showResponseMessage({success: '投稿しました！'}, {absolute: true})
                postLoader.stop()
                modal.remove()
            })
        })
    })
}

/**
 * Count characters using a simulation of twitter algorithm
 * @param {String} string
 * @returns {Number}
 */
function lengthInUtf8Bytes (string) {
    // Matches only the 10.. bytes that are non-initial characters in a multi-byte sequence.
    var m = encodeURIComponent(string).match(/%[89ABab]/g);
    console.log(m)
    return string.length + (m ? Math.floor(m.length / 2) : 0);
}

function sliceInUtf8Bytes (string, end) {
    while (lengthInUtf8Bytes(string) > end) string = string.slice(0, string.length - 1)
    return string
}

/**
 * Displays [string] characters count using a simulation of twitter algorithm inside [element]
 * @param {String} string 
 * @param {HTMLElement} element 
 * @returns {Number}
 */
function displayTweetCharsCount (string, element) {
    const textLength = lengthInUtf8Bytes(string)
    var number = charsLimit - urlLength - textLength
    element.innerText = number
    if (number < 0) element.className = 'tw-counter-red'
    else if (number >= 0 && number < 20) element.className = 'tw-counter-orange'
    else element.className = 'tw-counter-green'
    return number
}