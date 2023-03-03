export default class Helper {

    constructor () {

    }

    static async openWindow (string, options = {className: null}) {
        return new Promise ((resolve, reject) => {
            var modal = document.createElement('div')
            modal.classList.add('modal', 'd-flex')
            document.querySelector('body').appendChild(modal)
            modal.addEventListener('click', (e) => {
                var eTarget = e ? e.target : event.srcElement
                if ((eTarget != confirmationPopup && eTarget != confirmationPopup.firstElementChild) && (eTarget === modal)) modal.remove()
            } )
            var confirmationPopup = document.createElement('div')
            confirmationPopup.classList.add('popup', 'medium-popup')
            if (options.className) confirmationPopup.classList.add(options.className)
            confirmationPopup.innerHTML = string + '<div class="d-flex justify-content-between"><div id="ok" class="mp-button bg-darkgreen text-white push">確認しました</div></div>'
            modal.appendChild(confirmationPopup)
            // On click on button, close the popup and return true
            document.querySelector('#ok').addEventListener('click', () => {
                modal.remove()
                resolve(true)
            } )
        } )
    }

}