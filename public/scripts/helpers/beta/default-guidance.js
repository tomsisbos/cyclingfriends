// Only start guidance on browser session's first opening
if (!sessionStorage.getItem('helper-beta-default')) {
    
    openGuidancePopup(`
        <p>現在、本サービスの<strong>プライベートベータ版</strong>をご利用頂いております。</p>
        <p>テスト環境であるため、本来外部公開されるページ（ルート、アクティビティやライド紹介ページ等）も含め、プライベートベータプログラム登録者のみ観覧できる状況です。</p>
        <p>不具合を発見したり、意見やコメントがある場合は、該当のページで画面の右下にあるボタンをクリックし、ご報告頂けますと幸いです。</p>
        <p>報告内容送信後、<a href="/beta/board" target="_blank">ベータテスト管理パネル</a>に「開発ノート」として表示されます。出来る限り、開発チームからご回答させて頂きますので、ぜひとも一緒にプラットフォームの機能を磨いていきましょう！</p>
        <p>必要に応じて、<a href="/manual" target="_blank">利用マニュアル</a>をご参照ください。</p>
        <p class="text-center">それでは、良い旅を！</p>
    `, document.querySelector('.dev-note'), {class: 'medium-popup'})
    sessionStorage.setItem('helper-beta-default', true)

}