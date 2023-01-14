var myModal    = document.getElementById("myModal")
var modalBlock = document.querySelector(".modal-block")

// Open modal on click on a thumbnail
var thumbnails = document.querySelectorAll('.js-clickable-thumbnail')
thumbnails.forEach(thumbnail => thumbnail.addEventListener('click', () => {
  let id = parseInt(thumbnail.dataset.number)
  console.log(thumbnails)
  openModal()
  currentSlide(id)
} ) )

// Display slide on click on a demo
var demos = document.querySelectorAll('.demo')
demos.forEach(demo => demo.addEventListener('click', (e) => {
  let id = parseInt(e.target.getAttribute('demoId'))
  currentSlide(id)
} ) )

function openModal() {
	myModal.style.display = "block"
	
	// Close on clicking outside modal-block
	myModal.onclick = function(e){
		var eTarget = e ? e.target : event.srcElement
		if ((eTarget !== modalBlock) && (eTarget !== myModal)){
			// Nothing
		}else{
			closeModal()
		}
	}
}

function closeModal() {
  myModal.style.display = "none"
}

var slideIndex = 1
// showSlides(slideIndex)

function plusSlides(n) {
  showSlides(slideIndex += n)
}

function currentSlide(n) {
  showSlides(slideIndex = n)
}

function showSlides(n) {
  var i;
  var demos = document.querySelectorAll('.demo')
  var slides = document.getElementsByClassName("mySlides")
  if (myModal.querySelector('.js-name')) {
    var names = myModal.querySelectorAll('.js-name')
  }
  if (myModal.querySelector('.js-caption')) {
    var captions = myModal.querySelectorAll('.js-caption')
  }
  // var captionText = document.getElementById("caption")
  if (n > slides.length) {
    slideIndex = 1
  }
  if (n < 1) {
    slideIndex = slides.length
  }
  for (i = 0; i < slides.length; i++) {
      slides[i].style.display = "none"
  }
  for (i = 0; i < demos.length; i++) {
    demos[i].className = demos[i].className.replace(" active", "")
  }
  if (myModal.querySelector('.js-name')) {
    for (i = 0; i < names.length; i++) {
      names[i].style.display = "none"
    }
  }
  if (myModal.querySelector('.js-caption')) {
    for (i = 0; i < captions.length; i++) {
      captions[i].style.display = "none"
    }
  }
  slides[slideIndex-1].style.display = "block"
  demos[slideIndex-1].className += " active"
  if (myModal.querySelector('.js-name')) {
    names[slideIndex-1].style.display = "block"
  }
  if (myModal.querySelector('.js-caption')) {
    captions[slideIndex-1].style.display = "block"
  }
}

// Set keyboard navigation
var prev = document.querySelector('.prev.lightbox-arrow')
if (prev) {
  prev.addEventListener('click', function () { plusSlides(-1) } )
}
var next = document.querySelector('.next.lightbox-arrow')
if (next) {
  next.addEventListener('click', function () { plusSlides(1) } )
}
var nav = document.querySelector('.lightbox-arrow')
if (nav) {
  document.onkeydown = changeOnArrows // Using onkeydown property rather than addEventListener prevents from adding a new listener on document each time a popup is opened.
}
function changeOnArrows (e) {
  if (myModal && myModal.style.display !== 'none') { // If myModal is currently displayed
    if (e.composedPath()[0].localName !== 'input') { // If focus is not on input
      if (e.code == 'ArrowLeft') {
        e.preventDefault()
        plusSlides(-1)
      } else if (e.code == 'ArrowRight') {
        e.preventDefault()
        plusSlides(1)
      } else if (e.code == 'Escape') {
        e.preventDefault()
        closeModal()
      }
    }
  }
}