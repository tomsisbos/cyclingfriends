var noteIcon = document.createElement('div')
noteIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 21V8a3 3 0 0 1 3-3h10a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3H8l-4 4m8-13v3m0 3v.01"/></svg>'
noteIcon.className = 'dev-note'

// Move on drag
setDraggable(noteIcon)

// On icon click
noteIcon.addEventListener('click',async () => {
    // Close modal if already opened
    if (document.querySelector('.modal')) document.querySelector('.modal').remove()
    // Else open modal
    else {
        var noteData = await openNotePopup()
        if (noteData != false) {
            console.log(noteData)
        }
    }
} )

document.body.appendChild(noteIcon)

async function openNotePopup () {
    return new Promise ((resolve, reject) => {
        var modal = document.createElement('div')
        modal.classList.add('modal', 'd-flex')
        document.querySelector('body').appendChild(modal)
        modal.addEventListener('click', (e) => {
            var eTarget = e ? e.target : event.srcElement
            if ((eTarget != confirmationPopup && eTarget != confirmationPopup.firstElementChild) && (eTarget === modal)) modal.remove()
        } )
        var confirmationPopup = document.createElement('div')
        confirmationPopup.classList.add('popup', 'fullscreen-popup')
        confirmationPopup.innerHTML = `
            <p>
                このページについて、不具合を発見した場合や、機能等について意見があった場合に、開発チームに報告して頂くためのフォームです。<br>
                下記の通り、送信して頂けると幸いです。
            </p>
            <form id="noteForm" class="d-flex flex-column">
                <label><strong>報告タイプ</strong></label>
                <select id="noteType">
                    <option value="bug">バグ</option>
                    <option value="opinion">意見</option>
                    <option value="proposal">提案</option>
                    <option value="other">その他</option>
                </select>
                <label><strong>報告内容</strong></label>
                <textarea id="noteContent" placeholder="こちらに詳細をご記入ください。"></textarea>
            </form>
            <div><strong>URL</strong> : ` + window.location.href + `</div>
            <div><strong>Client</strong> : ` + navigator.userAgent + `</div>
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
                content: document.querySelector('#noteContent').value,
                url: window.location.href,
                browser: navigator.userAgent
            }
            modal.remove()
            resolve(data)
        } )
    } )
}