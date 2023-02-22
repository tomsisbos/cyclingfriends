var detailsSection = document.querySelector('.m-summary-details')

if (document.querySelector('h2')) {
    var ul = appendSection2()
    document.querySelectorAll('h2').forEach(h2 => {
        var section = h2.closest('.m-section')
        var li = appendLi(ul, h2)

        if (section.querySelector('h3')) {
            var ul2 = appendSection3(li)
            section.querySelectorAll('h3').forEach(h3 => {
                var li = appendLi(ul2, h3)
            } )
        }
    } )
}

// If first level, append tags to toggle lower levels)
document.querySelectorAll('.m-summary-h2').forEach(detailsElement => {
    if (detailsElement.nextSibling && detailsElement.nextSibling.tagName == 'UL') { // Only do it if lower levels exist
        var toggleTag = document.createElement('div')
        toggleTag.className = 'm-summary-toggletag'
        toggleTag.innerText = '▾'
        detailsElement.appendChild(toggleTag)
        toggleTag.addEventListener('click', () => toggle(detailsElement))
    }
} )

function appendSection2 () {
    let ul = document.createElement('ul')
    detailsSection.appendChild(ul)
    return ul
}

function appendSection3 (li) {
    let ul = document.createElement('ul')
    ul.style.display = 'none'
    li.after(ul)
    return ul
}

function appendLi (ul, ref) {
    var detailsElement = document.createElement('li')
    detailsElement.className = 'm-summary-' + ref.tagName
    var link = document.createElement('div')
    link.className = 'm-summary-link'
    link.innerText = ref.innerText
    detailsElement.appendChild(link)
    ul.appendChild(detailsElement)
    link.addEventListener('click', () => ref.scrollIntoView({behavior: 'smooth'}))
    return detailsElement
}

function toggle (item) {
    var element = item.nextSibling
    var tag = item.querySelector('.m-summary-toggletag')
    if (element.style.display == 'none') {
        element.style.display = 'block'
        tag.innerText = '▴'
    } else if (element.style.display == 'block') {
        element.style.display = 'none'
        tag.innerText = '▾'
    }
}