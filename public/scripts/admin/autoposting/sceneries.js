import CircleLoader from "/class/loaders/CircleLoader.js"

var prefectureSelect = document.querySelector('#prefecture')
var scheduleContainer = document.querySelector('#scheduleContainer')
var filteringContainer = document.querySelector('#filteringContainer')
var selectingContainer = document.querySelector('#selectingContainer')

// Load posting schedule
var scheduleLoader = new CircleLoader(scheduleContainer)
ajaxGetRequest ("/api/admin/autoposting.php" + "?type=schedule&entry_type=scenery", async (entries) => {

    // If no entry has been found, display message
    if (entries.length == 0) selectingContainer.innerText = 'スケジュールされている投稿はありません。'

    // For each entry
    else entries.forEach(entry => {

        // Build table
        var row = document.createElement('div')
        row.className = "d-flex gap-20 mb-1"
        row.dataset.id = entry.id
        var name = document.createElement('div')
        name.innerText = entry.instance.name
        var datetime = document.createElement('div')
        datetime.innerText = entry.datetime.toLocaleString()
        var removeButton = document.createElement('button')
        removeButton.className = 'push btn smallbutton bg-darkred'
        removeButton.innerText = '削除'
        row.appendChild(name)
        row.appendChild(datetime)
        row.appendChild(removeButton)
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
            var row = buildRow(scenery)
            selectingContainer.appendChild(row)
        })

    }, filteringLoader)
})

function buildRow (scenery) {

    var row = document.createElement('div')
    row.className = "d-flex gap-20 mb-1"
    row.dataset.id = scenery.id
    var name = document.createElement('div')
    name.innerText = scenery.name
    var city = document.createElement('div')
    city.innerText = scenery.city
    var addButton = document.createElement('button')
    addButton.className = 'push btn smallbutton bg-darkgreen'
    addButton.innerText = '追加'
    row.appendChild(name)
    row.appendChild(city)
    row.appendChild(addButton)

    // On entry adding
    addButton.addEventListener('click', () => {
        var data = {
            entry_id: scenery.id,
            entry_type: 'scenery'
        }
        
        // Append a new row to schedule
        ajaxJsonPostRequest("/api/admin/autoposting.php", data, (response) => {
            scheduleContainer.appendChild(buildRow(scenery))
        })
        
    })

    return row
}