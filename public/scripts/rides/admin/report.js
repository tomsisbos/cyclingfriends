import CircleLoader from "/class/loaders/CircleLoader"

var selectGuide = document.querySelector('#selectGuide')
var selectActivitiesContainer = document.querySelector('#selectActivitiesContainer')

selectGuide.addEventListener('change', () => {
    console.log('changed')

    var selectActivity = document.createElement('select')
    selectActivity.className = "form-select"
    selectActivity.name = "id"
    var submit = document.createElement('input')
    submit.setAttribute('type', 'submit')
    submit.setAttribute('name', 'activityReport')
    submit.className = 'btn smallbutton'
    submit.value = '確定'
    
    // Reset container
    selectActivitiesContainer.innerHTML = ''

    var loader = new CircleLoader(selectActivitiesContainer)

    ajaxGetRequest("/api/riders/activities.php?user_id=" + selectGuide.value, async (activityData) => {

        selectActivitiesContainer.appendChild(selectActivity)
        selectActivitiesContainer.appendChild(submit)

        activityData.forEach(activity => {
            var option = document.createElement('option')
            option.value = activity.id
            var optionText = (new Date(activity.datetime.date)).toLocaleDateString() + ' - ' + activity.title
            if (optionText.length > 30) option.innerText = optionText.slice(0, 27) + '...'
            else option.innerText = optionText
            selectActivity.appendChild(option)
        })

    }, loader)
})