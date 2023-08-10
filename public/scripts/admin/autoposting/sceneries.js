import CircleLoader from "/class/loaders/CircleLoader.js"

var prefectureSelect = document.querySelector('#prefecture')
var scheduleContainer = document.querySelector('#scheduleContainer')
var filteringContainer = document.querySelector('#filteringContainer')
var selectingContainer = document.querySelector('#selectingContainer')

// Load posting schedule
var scheduleLoader = new CircleLoader(scheduleContainer)
ajaxGetRequest ("/api/admin/autoposting.php" + "?type=schedule&entry_type=scenery", async (entries) => {

    // If no entry has been found, display message
    if (entries.length == 0) scheduleContainer.innerText = 'スケジュールされている投稿はありません。'

    // For each entry
    else entries.forEach(entry => {

        var row = buildScheduleRow(entry)
        scheduleContainer.appendChild(row)
    })

}, scheduleLoader)

// On prefecture selection
prefectureSelect.addEventListener('change', (e) => {
    var prefecture = e.target.value

    // Clear selecting container
    selectingContainer.innerHTML = ''

    // Get sceneries data for this prefecture
    var filteringLoader = new CircleLoader(selectingContainer)
    ajaxGetRequest ("/api/sceneries.php" + "?prefecture=" + prefecture, async (sceneries) => {

        // For each scenery
        sceneries.forEach(scenery => {

            // Build table
            var row = buildSelectingRow(scenery)
            selectingContainer.appendChild(row)
        })

    }, filteringLoader)
})

function buildScheduleRow (entry) {
    
    var row = document.createElement('div')
    row.dataset.id = entry.id
    row.className = "autoposting-row"
    if (entry.history.length > 0) row.classList.add('bg-darkred')

    var medias = document.createElement('div')
    medias.className = 'autoposting-thumb'
    var name = document.createElement('div')
    name.innerText = entry.instance.name
    name.className = 'autoposting-name'
    const length = entry.medias.filter(element => element !== null).length
    for (let i = 0; i < length; i++) {
        let media = document.createElement('img')
        media.src = entry.medias[i]
        media.dataset.number = i
        medias.appendChild(media)
    }
    medias.appendChild(name)
    row.appendChild(medias)

    var text = document.createElement('div')
    text.className = 'autoposting-text'
    text.innerText = entry.text
    var removeButton = document.createElement('button')
    removeButton.className = 'push btn smallbutton bg-darkred m-1'
    removeButton.innerText = '削除'
    row.appendChild(text)
    row.appendChild(removeButton)

    // On entry removing
    removeButton.addEventListener('click', () => {
        
        // Remove row from schedule
        row.remove()
        ajaxGetRequest("/api/admin/autoposting.php?type=remove&id=" + entry.id, (response) => {})
        
    })

    return row
}

function buildSelectingRow (scenery) {

    var row = document.createElement('div')
    row.className = "d-flex gap-20 mb-1 py-1 px-3 bg-white align-items-center"
    row.dataset.id = scenery.id
    var name = document.createElement('div')
    name.className = "bold"
    name.innerText = scenery.name
    var city = document.createElement('div')
    city.innerText = scenery.city
    var showButton = document.createElement('a')
    showButton.href = '/scenery/' + scenery.id
    showButton.setAttribute('target', '_blank')
    showButton.className = 'push btn smallbutton'
    showButton.innerText = '詳細'
    var addButton = document.createElement('button')
    addButton.className = 'btn smallbutton bg-darkgreen'
    addButton.innerText = '追加'
    row.appendChild(name)
    row.appendChild(city)
    row.appendChild(showButton)
    row.appendChild(addButton)

    // On entry adding
    addButton.addEventListener('click', () => {
        var data = {
            entry_id: scenery.id,
            entry_type: 'scenery'
        }
        
        // Append a new row to schedule
        ajaxJsonPostRequest("/api/admin/autoposting.php", data, (response) => {
            scheduleContainer.appendChild(buildScheduleRow(response))
        })
        
    })

    return row
}