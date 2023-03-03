// Only start guidance on browser session's first opening
if (!sessionStorage.getItem('helper-profile')) {

    // Profile picture
    openGuidancePopup(`
        まず、<strong>プロフィール画像</strong>を設定しましょう！<br>
        真っ先に他人の目に入るものなので、慎重に選びましょう。<br>
        （詳細は<a href="/manual/user#profilePicture" target="_blank">こちら</a>を参照）
    `, document.getElementById('propic-form'), {position: 'bottomleft'}).then( () => {

        // Name
        openGuidancePopup(`
            CyclingFriendsを利用するには、ユーザーネームとプロフィール画像で十分ですが、<strong>実名</strong>も記載しておくことで、他ユーザーにとってはより分かりやすくなります。<br>
            ※ ライドに募集するには、実名（姓名共に）を設定する必要があります。<br>
            （詳細は<a href="/manual/user#name" target="_blank">こちら</a>を参照）
        `, document.getElementById('name'), {position: 'topleft'}).then( () => {

            // Level
            openGuidancePopup(`
                他ユーザーと繋がるためや、アルゴリズムを介して紹介されるセグメント、ライドや絶景スポットやその距離情報など、様々な機能に反映されるので、できる限り<strong>活動拠点</strong>も設定しましょう。<br>
                （詳細は<a href="/manual/user#location" target="_blank">こちら</a>を参照）
            `, document.getElementById('userLocationButton'), {position: 'topleft'}).then( () => {

                // Location
                openGuidancePopup(`
                    <strong>レベル</strong>は、ルート、ライドやセグメントの表示など、様々な機能のアルゴリズムに反映されるので、できるだけ正確に設定しておきましょう。<br>
                    （詳細は<a href="/manual/user#level" target="_blank">こちら</a>を参照）
                `, document.getElementById('level'), {position: 'topright'}).then( () => {
                
                    openAlertPopup('それ以外にも様々な設定があるので、必要に応じて<a href="/manual/user" target="_blank">ユーザーマニュアル</a>を確認しましょう！')
                    sessionStorage.setItem('helper-profile', true)

                } )

            } )

        } )

    } )
}