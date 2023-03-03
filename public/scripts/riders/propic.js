import Modal from "/map/class/Modal.js"

var propic = document.querySelector('#propic').querySelector('img')
var modal = new Modal(propic.src)
propic.after(modal.element)
propic.addEventListener('click', () => modal.open())