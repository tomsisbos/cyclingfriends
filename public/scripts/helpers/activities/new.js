// Only start guidance on browser session's first opening
if (!sessionStorage.getItem('helper-activity-new')) {
    
    openGuidancePopup(`
        <p><strong>1/4 走行データのインポート</strong></p>
        <p>CyclingFriends上でコミュニティの活動は<a href="/manual/activities" target="_blank">「アクティビティ」の記録と管理</a>が大半を占めています。</p>
        <p>現時点では、新規アクティビティの作成方法は<strong>ファイルアップロード</strong>または<strong>Garmin Connectと同期</strong>の二つとなっております。</p>
        <p>ログデータの取得について、<a href="/manual/activities#aboutFiles" target="_blank">こちら</a>をご参照ください。</p>
    `, document.querySelector('.new-ac-upload-container .smallbutton'), {class: 'medium-popup', position: 'bottomright'})
    sessionStorage.setItem('helper-activity-new', true)

}