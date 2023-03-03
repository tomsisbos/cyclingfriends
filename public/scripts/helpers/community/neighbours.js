// Only start guidance on browser session's first opening
if (!sessionStorage.getItem('helper-neighbours')) {
    
    openGuidancePopup('「お隣機能」を利用するには、<a href="/profile/edit">こちら</a>から活動拠点を設定する必要があります。', document.querySelector('.navbar .free-propic-img'))
    sessionStorage.setItem('helper-neighbours', true)

}