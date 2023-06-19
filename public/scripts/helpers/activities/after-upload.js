// Helpers
if (!sessionStorage.getItem('helper-activity-new')) {
    openGuidancePopup(`
    <p><strong>2/4 アクティビティの設定</strong></p>
    <p>はじめに、タイトル、プライバシー設定、使用した自転車を入力します。</p>
    `, document.querySelector('#inputTitle'), {class: 'medium-popup', position: 'bottomleft'}).then(() => {

        document.querySelector('#uploadPhotos').scrollIntoView()
        openGuidancePopup(`
        <p><strong>3/4 写真のインポート</strong></p>
        <p>次に、走行中に撮影した写真を取り組みましょう。</p>
        <p>ハイライト写真や絶景スポットの作成元に使う写真を指定します。必要に応じて、写真単体のプライバシー設定も確認します。</p>
        `, document.querySelector('#uploadPhotos').parentElement, {class: 'medium-popup', position: 'topright'}).then(() => {

            openGuidancePopup(`
            <p><strong>4/4 ストーリーの作成</strong></p>
            <p>どこで何が起きたかなど、コース上をクリックすることで、タイムラインにストーリーの記入欄が追加されるので、走行データとストーリーを結び付けることができます。</p>
            `, document.querySelector('#divCheckpoints'), {class: 'medium-popup', position: 'topright'})
            sessionStorage.setItem('helper-activity-new', true)
        })

    })
}

// Legend

var legendContainer = document.createElement('div')
legendContainer.className = 'ac-photos-legend'
var legendData = [
    {
        text: '写真を削除する',
        icon: 'iconamoon:close-bold'
    },
    {
        text: 'ハイライト写真に選定する',
        icon: 'mdi:feature-highlight'
    },
    {
        text: 'この写真を元に絶景スポットを新規作成する',
        icon: 'material-symbols:add-location-alt'
    },
    {
        text: 'この写真の公開設定を変更する',
        icon: 'material-symbols:public'
    }
]
for (let i = 0; i < 4; i++) {
    var line = document.createElement('div')
    line.className = "line"
    var icon = document.createElement('span')
    icon.className = "iconify icon"
    icon.dataset.icon = legendData[i].icon
    var text = document.createElement('div')
    text.innerText = legendData[i].text
    text.className = 'text'
    line.appendChild(icon)
    line.appendChild(text)
    legendContainer.appendChild(line)
}

document.querySelector('#uploadPhotos').closest('.container').appendChild(legendContainer)