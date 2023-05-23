import CircleLoader from "/class/loaders/CircleLoader.js"

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
    modal.addEventListener('click', (e) => {
        if ((e.target == modal)) modal.remove()
    } )

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
    buttons.innerHTML = '<div id="back" class="mp-button bg-darkred text-white">戻る</div><div id="post" class="mp-button bg-darkgreen text-white">投稿</div>'
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
                    <textarea class="tw-content">` + activity.checkpoints[0].story + `</textarea>
                </div>
            </div>
        `
        $content.innerHTML = content

        // Listeners
        modal.querySelector('#back').addEventListener('click', () => modal.remove())
        modal.querySelector('#post').addEventListener('click', () => {

            var tweet = {
                text: document.querySelector('.tw-content').value,
            }
            
            var postLoader = new CircleLoader($content, {compact: true})
            postLoader.start()

            ajaxJsonPostRequest('/api/twitter/post.php', tweet, (response) => {
                console.log(response)
                if (response.errors) showResponseMessage({error: '投稿に失敗しました。（' + response.errors[0].message + '）'})
                else showResponseMessage({success: '投稿しました！'})
                postLoader.stop()
                modal.remove()
            })
        })
    })
}