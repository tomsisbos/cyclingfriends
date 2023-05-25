import CircleLoader from "/class/loaders/CircleLoader.js"

export default class Twitter {

    constructor (activity) {
        this.activity = activity
    }

    charsLimit = 280
    urlLength = 25
    activity
    modal
    $previewPhotos
    previewPhotos = []
    $selectedNumber

    openTwitterModal () {
        
        // Build modal
        this.modal = document.createElement('div')
        this.modal.classList.add('modal', 'd-flex')
        document.querySelector('body').appendChild(this.modal)

        // Popup content
        var popup = document.createElement('div')
        popup.classList.add('popup')
        this.modal.appendChild(popup)
        var $content = document.createElement('div')
        popup.appendChild($content)
        var dataLoader = new CircleLoader($content, {compact: true})
        dataLoader.start()
        var buttons = document.createElement('div')
        buttons.className = 'd-flex justify-content-evenly'
        buttons.innerHTML = '<button id="back" class="mp-button bg-darkred text-white">戻る</button><div class="tw-counter"></div><button id="post" class="mp-button bg-darkgreen text-white">投稿</button>'
        popup.appendChild(buttons)
        this.$previewPhotos = document.createElement('div')
        this.$previewPhotos.className = 'tw-preview-photos'
        popup.appendChild(this.$previewPhotos)
        
        console.log(this.activity)
        dataLoader.stop()

        var name = buttonTwitter.dataset.username
        var username = buttonTwitter.dataset.username
        var profileImage = buttonTwitter.dataset.profileImage
        var date = new Date()
        var text = this.activity.checkpoints.map(checkpoint => checkpoint.story).join('\r\n\r\n')
        if (this.lengthInUtf8Bytes(text) > this.charsLimit - this.urlLength - 3) text = this.sliceInUtf8Bytes(text, this.charsLimit - this.urlLength - 3) + '...'

        var content = `
            <div class="tw-container">
                <div class="tw-profile">
                    <img src="` + profileImage +  `">
                </div>
                <div class="tw-body">
                    <div class="tw-top">
                        <div class="tw-name">` + name + `</div>
                        <div class="tw-username">@` + username + `</div>
                        <div class="tw-date">・` + date.toLocaleTimeString('ja-JP') + `</div>
                    </div>
                    <textarea class="tw-content">` + text + `</textarea>
                    <div class="tw-link">` + window.location + `</div>
                </div>
            </div>
        `
        $content.innerHTML = content

        // Options
        var $title = document.createElement('div')
        $title.className = "tw-title"
        $title.innerText = '写真表示設定'
        var $options = document.createElement('div')
        $options.className = "tw-options"
        $options.innerHTML = `
            <div class="tw-option"><input type="checkbox" id="time" checked></input><label for="time">時間</label></div>
            <div class="tw-option"><input type="checkbox" id="distance" checked></input><label for="distance">距離</label></div>
            <div class="tw-option"><input type="checkbox" id="username" checked></input><label for="username">ユーザーネーム</label></div>
            <div class="tw-option"><input type="checkbox" id="title" checked></input><label for="title">タイトル</label></div>
        `
        this.$selectedNumber = document.createElement('div')
        this.$selectedNumber.className = "tw-selected-number"
        buttons.after(this.$selectedNumber)
        buttons.after($options)
        buttons.after($title)

        // Listeners
        const $text    = this.modal.querySelector('textarea.tw-content')
        const $counter = this.modal.querySelector('.tw-counter')
        const $back    = this.modal.querySelector('#back')
        const $post    = this.modal.querySelector('#post')
        const optionsList = this.modal.querySelectorAll('.tw-options input')

        this.displayTweetCharsCount($text.value, $counter)

        // Preview photos
        this.loadPreviewPhotos()

        $text.addEventListener('keyup', () => {
            var number = this.displayTweetCharsCount($text.value, $counter)
            if (number < 0) $post.setAttribute('disabled', 'disabled')
            else $post.removeAttribute('disabled')
        })
        optionsList.forEach($option => $option.addEventListener('change', () => this.loadPreviewPhotos()))
        $back.addEventListener('click', () => this.modal.remove())
        $post.addEventListener('click', async () => {

            // Disable post button
            $post.setAttribute('disabled', 'disabled')

            // Prepare media
            const mediaMaxLength = 4
            var photos = []
            for (let i = 0; i < mediaMaxLength && i < this.previewPhotos.length; i++) photos.push(this.previewPhotos[i])

            // Prepare text
            let text = document.querySelector('.tw-content').value + '\r\n\r\n' + window.location

            // Prepare tweet data
            console.log(photos)
            var tweet = {text, photos}

            var postLoader = new CircleLoader($content, {compact: true})
            postLoader.start()

            ajaxJsonPostRequest('/api/twitter/post.php', tweet, (response) => {
                console.log(response)
                if (response.errors) showResponseMessage({error: '投稿に失敗しました。（' + response.errors[0].message + '）'}, {absolute: true})
                else if (!response.data) showResponseMessage({error: '投稿に失敗しました。（' + response.detail + '）'}, {absolute: true})
                else showResponseMessage({success: '投稿しました！'}, {absolute: true, scrollIntoView: true})
                postLoader.stop()
                this.modal.remove()
            })
        })
    }

    /**
     * Display preview photos taking options into account
     */
    async loadPreviewPhotos () {
        // Clear previous photos
        this.$previewPhotos.innerHTML = ''
        this.previewPhotos = []
        // Load new photos in according to options
        for (var nb = 0; nb < this.activity.photos.length; nb++) {
            var $previewPhoto = await this.writeOnImage(this.activity.photos[nb].url, nb)
            this.$previewPhotos.appendChild($previewPhoto)
            $previewPhoto.addEventListener('click', (e) => {
                if (e.target.classList.contains('tw-selected')) this.unselect(e.target)
                else if (this.previewPhotos.length < 4) this.select(e.target)
                console.log(this.previewPhotos)
            })
        }
    }

    select ($previewPhoto) {
        var dataURL = $previewPhoto.toDataURL('image/jpeg')
        this.previewPhotos.push(dataURL)
        $previewPhoto.classList.add('tw-selected')
        this.$selectedNumber.innerText = '添付写真：' + this.previewPhotos.length + '/4枚'
    }

    unselect ($previewPhoto) {
        var dataURL = $previewPhoto.toDataURL('image/jpeg')
        this.previewPhotos.splice(this.previewPhotos.indexOf(dataURL), 1)
        $previewPhoto.classList.remove('tw-selected')
        this.$selectedNumber.innerText = '添付写真：' + this.previewPhotos.length + '/4枚'
    }

    /**
     * Retrieve text to print from option id and activity photo number
     * @param {String} id
     * @param {Number} nb
     * @returns {String} text
     */
    getText (id, nb) {
        switch (id) {
            case 'username': return '@' + this.activity.route.author.login
            case 'title': return this.activity.title
            case 'distance': return 'km ' + Math.round(this.activity.photos[nb].distance * 10) / 10 + ' / ' + this.activity.route.distance
            case 'time': return new Date(this.activity.photos[nb].datetime).toLocaleTimeString('ja-JP').slice(0, -3)
            default : return 'ERROR'
        }
    }

    /**
     * Count characters using a simulation of twitter algorithm
     * @param {String} string
     * @returns {Number}
     */
    lengthInUtf8Bytes (string) {
        // Matches only the 10.. bytes that are non-initial characters in a multi-byte sequence.
        var m = encodeURIComponent(string).match(/%[89ABab]/g);
        return string.length + (m ? Math.floor(m.length / 2) : 0);
    }

    sliceInUtf8Bytes (string, end) {
        while (this.lengthInUtf8Bytes(string) > end) string = string.slice(0, string.length - 1)
        return string
    }

    /**
     * Displays [string] characters count using a simulation of twitter algorithm inside [element]
     * @param {String} string 
     * @param {HTMLElement} element 
     * @returns {Number}
     */
    displayTweetCharsCount (string, element) {
        const textLength = this.lengthInUtf8Bytes(string)
        var number = this.charsLimit - this.urlLength - textLength
        element.innerText = number
        if (number < 0) element.className = 'tw-counter-red'
        else if (number >= 0 && number < 20) element.className = 'tw-counter-orange'
        else element.className = 'tw-counter-green'
        return number
    }

    async writeOnImage (url, nb) {
        return new Promise((resolve, reject) => {
            const canvas = document.createElement('canvas')
            canvas.className = "tw-canvas"
            const ctx = canvas.getContext('2d')
            let img = new Image()
            img.setAttribute('crossorigin', 'anonymous')
            img.addEventListener("load", () => {
                canvas.width = img.width
                canvas.height = img.height
                ctx.drawImage(img, 0, 0)
                ctx.fillStyle = "#fff"
                const optionsList = this.modal.querySelectorAll('.tw-options input')
                var centerLine = 0
                optionsList.forEach($option => {
                    if ($option.checked) {
                        if ($option.id == 'time') {
                            ctx.textAlign = 'left'
                            var posX = 50
                            var fontsize = 28
                        } else if ($option.id == 'distance') {
                            ctx.textAlign = 'right'
                            var posX = img.width - 50
                            var fontsize = 28
                        } else {
                            ctx.textAlign = 'center'
                            var posX = img.width / 2
                            var fontsize = 36
                        }
                        ctx.font = fontsize + 'px monospace'
                        ctx.fillText(this.getText($option.id, nb), posX, img.height - (80 + (centerLine * fontsize * 1.15)))
                        if ($option.id != 'distance' && $option.id != 'time') centerLine++
                    }
                })
                resolve(canvas)
            })
            img.src = url
        })
    }

}