import React from 'react'
import ReactDOM from 'react-dom/client'
import Tours from '/react/tours/Tours.jsx'

function App () {
  
    return (
        <div className="container home-container">

            <div className="company-quote">日帰りツアーカレンダー</div>

            <div className="text-center">
                <p>年間を通じて、地方の魅力を探索するサイクリングツアーを開催しております。</p>
                <p>CyclingFriendsのロゴマークの四色が意味している、「<strong>景色</strong>、<strong>食</strong>、<strong>文化</strong>、<strong>仲間</strong>」の４柱を主軸に、思い出に深く残るコンテンツづくりにこだわっています。</p>
                <p>お好きなツアー名をクリックすると、詳細ページにアクセスできます。参加するには、アカウントを新規作成／ログインの上、詳細ページに表示されている「参加」ボタンをクリックし、必要情報を入力して頂くだけです。</p>
            </div>
            
            <Tours />

        </div>
    )

}

const root = ReactDOM.createRoot(document.querySelector('#root'))
root.render(<App />)