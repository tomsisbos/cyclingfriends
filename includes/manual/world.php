<?php

$title = 'サイクルガイドマップ';

$intro = [
    'CyclingFriendsの中心になるのは、サイクルガイドマップです。',
    'このサイクルガイドマップは、CyclingFriendsの公式ガイド及びコミュニティといった、サイクリストによる、サイクリストのための地図です。',
    'サイクリストが求める情報が見やすく表示されます。例えば、コンビニ、トイレ、水補給ができる場所が分かりやすいアイコンで表示されたり、サイクリングロードや林道、自転車走行禁止区間等が色分けで表示されたりしています。',
    'ベースレイヤーの上に、静的（スタティック）データ、そして動的（ダイナミック）データという3種類のレイヤーを順番に重ねることで、カスタマイズ性の高い地図を実現できます。',
    'また、「地図」という分かりやすい言葉に要約していますが、実は精度の高い3Dデータも導入しているため、サイクリストの世界観が3次元に表現された「仮想世界」といった方が正確な表現でしょう。',
    'point' => '全体のサイクルガイドマップが自由に確認できるのが「WORLD」ページですが、地図表示が含まれる他の機能でも、同じ地図が活用されています。'
];

$content = [
    [
        'title' => 'ワールド',
        'path' => '/world',
        'content' => [
            [
                'title' => 'ベースレイヤー',
                'text' => [
                    'ベースレイヤーは、標高や地形等、地図の基盤となる情報を表示するレイヤーです。利用者は、このレイヤーで操作を行うことができません。',
                    'ベースレイヤーはMapboxが提供しているMaps APIを利用しており、日本に関しては、データの主な出所はOpenStreetMapとZenrinとなっています。',
                    'CyclingFriendsは「季節」と「航空写真」の二つのレイヤーを用意しています。'
                ],
                'content' => [
                    [
                        'title' => '季節',
                        'text' => [
                            '道路と標高データが協調されているレイヤーです。',
                            'このベースレイヤーを選択すると、選択する時期によって、地図のディスプレイ（色合い等）のみならず、表示されるデータが変わるようになっています。例えば、絶景スポットはその季節の写真が優先的に表示されたり、おススメの季節と一致しているセグメントが色分けで表示されたりします。',
                            '「季節」は、CyclingFriendsのデフォルトレイヤーです。',
                            'point' => '※アクティビティページの場合、デフォルト表示は走行データの日付を反映した時期になります。'
                        ]
                    ],
                    [
                        'title' => '航空写真',
                        'text' => [
                            '航空写真をベースに、実世界を再現する目的でデザインされたレイヤーです。コースや地域の細かい下見を行ったり、実走に近い感覚をバーチャルで味わうのに便利だったりします。'
                        ]
                    ],
                ]
            ]
        ]
    ]
];