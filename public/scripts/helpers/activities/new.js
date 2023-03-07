// Only start guidance on browser session's first opening
if (!sessionStorage.getItem('helper-activity-new')) {
    
    openGuidancePopup(`
        <p>CyclingFriends上でコミュニティの活動は「アクティビティ」の記録と管理が大半を占めています。(詳細は<a href="/manual/activities" target="_blank">こちら</a>を参照)</p>
        <p>今後、自社アプリや、他サービスとの同期機能の開発を進める予定ですが、現時点では、新規アクティビティの作成方法は<strong>ファイルアップロードのみ</strong>となっております。外部のアプリやサイクルコンピューター等で記録された*.gpx又は*.fit形式のファイルをアップロードしてください。</p>
        <p>アクティビティデータの取得について、<a href="/manual/activities#aboutFiles" target="_blank">こちら</a>をご参照ください。</p>
    `, document.querySelector('.new-ac-upload-container .smallbutton'), {class: 'medium-popup', position: 'bottomright'})
    sessionStorage.setItem('helper-activity-new', true)

}