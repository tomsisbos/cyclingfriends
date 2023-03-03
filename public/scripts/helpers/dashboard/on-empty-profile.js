// Only start guidance on browser session's first opening
if (!sessionStorage.getItem('helper-dashboard')) {
    
    openGuidancePopup('CyclingFriendsは、様々な情報を設定しておくと、より楽しくなります。はじめに、<a href="/profile/edit">ユーザープロフィール</a>の中から設定しておきましょう！', document.querySelector('.navbar .free-propic-img'))
    sessionStorage.setItem('helper-dashboard', true)

}