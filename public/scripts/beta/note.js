var apiUrl = '/api/beta.php'

var noteIcon = document.createElement('div')
noteIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 21V8a3 3 0 0 1 3-3h10a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3H8l-4 4m8-13v3m0 3v.01"/></svg>'
noteIcon.className = 'dev-note'

// Move on drag
setDraggable(noteIcon)

// Update icon position property when moves for preventing click event on drag
noteIcon.position = [noteIcon.offsetLeft, noteIcon.offsetTop]
noteIcon.addEventListener('mousedown', () => noteIcon.position = [noteIcon.offsetLeft, noteIcon.offsetTop])

// On icon click
noteIcon.addEventListener('click', async () => {
    // Only open modal if icon position has not changed (if no drag)
    if (noteIcon.position[0] == noteIcon.offsetLeft && noteIcon.position[1] == noteIcon.offsetTop) {
        // Close modal if already opened
        if (document.querySelector('.modal')) document.querySelector('.modal').remove()
        // Else open modal
        else {
            var noteData = await openNotePopup()
            if (noteData != false) {
                console.log(noteData)
                ajaxJsonPostRequest(apiUrl, noteData, (response) => {
                    console.log(response)
                    showResponseMessage(response)
                } )
            }
        }
    }
} )

document.body.appendChild(noteIcon)

async function openNotePopup () {
    return new Promise ((resolve, reject) => {
        var modal = document.createElement('div')
        modal.classList.add('modal', 'd-flex')
        document.querySelector('body').appendChild(modal)
        var confirmationPopup = document.createElement('div')
        confirmationPopup.classList.add('popup', 'medium-popup', 'text-center')
        confirmationPopup.innerHTML = `
            <p>
                このページについて、不具合を発見した場合や、機能等について意見があった場合に、<strong>開発チームに報告して頂くためのフォーム</strong>です。<br>
                送信して頂いた内容は、<a href="/beta/board" target="_blank">ベータテスト管理パネル</a>に開発ノートとして表示されます。出来る限り、開発チームからご回答させて頂きますので、ぜひとも一緒にプラットフォームの機能を磨いていきましょう！
            </p>
            <form id="noteForm" class="d-flex flex-column">
                <label><strong>報告タイプ</strong></label>
                <select class="mb-2" id="noteType">
                    <option value="bug">バグ</option>
                    <option value="opinion">意見</option>
                    <option value="proposal">提案</option>
                    <option value="other">その他</option>
                </select>
                <label><strong>タイトル</strong></label>
                <input class="mb-2" id="noteTitle" type="text" placeholder="ひとことで概要を記入してください。"></input>
                <label><strong>報告内容</strong></label>
                <textarea class="mb-2" id="noteContent" placeholder="こちらに詳細をご記入ください。"></textarea>
            </form>
            <div><strong>URL</strong> : ` + window.location.href + `</div>
            <div><strong>Client</strong> : ` + getClientBrowserName() + `</div>
            <div class="d-flex justify-content-between">
                <div id="back" class="mp-button bg-darkred text-white">
                    戻る
                </div>
                <div id="send" class="mp-button bg-darkgreen text-white">
                    送信
                </div>
            </div>
        `
        modal.appendChild(confirmationPopup)
        // On click on "back" button, close the popup and return true
        document.querySelector('#back').addEventListener('click', () => {
            modal.remove()
            resolve(false)
        } )
        // On click on "send" button, close the popup and return form data
        document.querySelector('#send').addEventListener('click', () => {
            var data = {
                type: document.querySelector('#noteType').value,
                title: document.querySelector('#noteTitle').value,
                content: document.querySelector('#noteContent').value,
                url: window.location.href,
                browser: getClientBrowserName()
            }
            modal.remove()
            resolve(data)
        } )
    } )
}