<?php

$intro = [
    'CyclingFriends上でコミュニティの活動は「アクティビティ」の記録と管理が大半を占めています。',
    'アクティビティとは、ユーザーの走行記録のことです。実際の走行データをもとに、地図、プロファイル、写真やストーリー（文章）等で構成されるアクティビティ紹介ページや、過去の走行データを見やすくまとめた「走行日記」など、様々な形でデータを管理し、共有や発信することができます。',
    'CyclingFriendsでは、走行活動を「練習」の観点ではなく、「体験」の観点で取り扱っています。つまり、走行データの読み取りや分析ではなく、それに付随しているストーリーを引き出し、アクティビティを一つの「物語」として捉えています。',
    'プラットフォーム上はもちろんのこと、SNS等の他サービスとの連携を通じて、外部にも幅広く発信できるツールを備えています。'
];

$content = [
    [
        'title' => 'アクティビティの記録／編集',
        'path' => [
            'activity/new',
            'activity/[activity_id]/edit'
        ],
        'content' => [
            [
                'title' => '走行データのインポート',
                'id' => 'aboutFiles',
                'content' => [
                    [
                        'title' => '（１）Garminコネクトとの同期',
                        'text' => [
                            '<a href="https://www.garmin.co.jp/" target="_blank">Garmin社</a>のサイクルコンピューターをお持ちの方は、「Garmin Connect」と同期することができます。接続方法に関しましては' .Manual::ref('user-settings-garmin', 'こちらをご参照ください'). '。',
                            '新規のログがGarmin Connect上で保存されたら、ログファイルがCyclingFriendsのサーバーに通知され、解析を経て、新規のアクティビティが自動作成されるようになっています。デフォルトでは、「非公開」設定で作成されるので、通知にクリックして頂くと、写真やストーリーを追加したり、公開設定を変更したり、好きなようにストーリーページを作り上げて頂きます。'
                        ]
                    ],
                    [
                        'title' => '（２）*.gpx / *.fit ファイルのアップロード',
                        'text' => [
                            '走行データを記録元のアプリ／サイクルコンピューターからダウンロードするか、第三者のデータ管理サービス（Strava等）でエクスポートする必要があります。'
                        ]
                    ],
                    [
                        'title' => '',
                        'text' => [
                            'point' => '今後は、サイクルコンピューター各社との連携を強化しながら、自社開発のモバイルアプリから記録する方法を取り入れていく予定です。',
                            '走行データをインポートすると、地図、設定パネルとタイムラインが表示されます。'
                        ]
                    ]
                ]
            ],
            [
                'title' => 'アクティビティの設定',
                'content' => [
                    [
                        'title' => 'プライバシー',
                        'text' => [
                            'このアクティビティに関するすべてのデータを公開、非公開か限定公開（友達のみ）に設定することができます。',
                            '公開になっている場合、このアクティビティに関するすべてのデータ（走行データと写真を含む）がコミュニティに共有される（このアクティビティの概要が他ユーザーのダッシュボードに自動表示される等）ほか、自動作成されるアクティビティ紹介ページがCyclingFriends非登録者（サインインされていない状態）にも閲覧可能です。',
                            'point' => [
                                'アクティビティのプライバシー設定の他に、写真ごとにも設定できます（' .Manual::ref('activity-photos', 'こちらを参照'). '）。'
                            ]
                        ]
                    ],
                    [
                        'title' => 'バイク',
                        'text' => [
                            Manual::ref('user-bikes', 'プロフィールページでご登録いただいている自転車'). 'の中から、このアクティビティで利用した自転車を選択できます。デフォルトでは、バイク①に登録している自転車が選択されます。'
                        ]
                    ]
                ]
            ],
            [
                'title' => '写真のインポート',
                'id' => 'activityphotos',
                'text' => [
                    '走行中に撮影した写真をアップロードすると、走行データの時間データと写真の時間データをすり合わせて、タイムラインに順番に表示されます。また、コースデータの座標データとすり合わせて、写真の位置データが自動計算され、地図にも表示されます。'
                ],
                'content' => [
                    [
                        'title' => 'インポート可能な写真',
                        'text' => [
                            '写真をインポートできるには、様々な条件があります。この条件を満たさない写真をインポートすることができません。',
                            '1.	アクティビティのスタート30分前からゴール30分後までに撮影された写真であること',
                            '2.	写真に撮影時間のデータが付随されていること',
                            '3.	編集／加工されていないこと（編集されると、撮影時間を含む付随のメタデータが破棄されてしまいます）',
                            '4.	対応可能なファイル形式：*.jpg、*.png、*.HEIC'
                        ]
                    ],
                    [
                        'title' => '写真の設定',
                        'text' => [
                            '写真ごとに、４つの設定項目が選択できます。',
                            '1.	ハイライト写真の選定：クリックすると、この写真がハイライト写真に選定され、読者に優先的に表示されます。',
                            '2.	プライバシーの設定：この写真のプライバシー設定を指定できます。「公開」（だれにでも閲覧可能）、「限定公開」（アクティビティ紹介ページ上では誰にでも閲覧できるが、データがコミュニティに共有されない）または「非公開」（自分だけしか閲覧できない）に設定することができます。',
                            '3.	絶景スポットの作成：この写真をもとに、' .Manual::ref('sceneries', '絶景スポット'). 'を作成することができます。絶景スポットの作成は、アクティビティ保存時に行われます。絶景スポットの作成に関しては、' .Manual::ref('activity-scenerymaking', 'こちら'). 'のご参照ください。',
                            '4.	写真の削除：写真を削除することができます。'
                        ]
                    ],
                    [
                        'title' => 'アクティビティの写真データの活用について',
                        'text' => [
                            '「公開」に設定されているアクティビティの写真データは、' .Manual::ref('privacypolicy', 'プライバシーポリシー'). 'に基づいて、他の機能やサービスに活用することがあります。例えば、「セグメント」や「ルート」の座標データと、写真の位置情報が一致している場合は、そちらのページで表示されたりします。従って、自動的に取り組まれてほしくない写真は、写真自体を「限定公開」または「非公開」に設定してください。',
                            'point' => [
                                'アクティビティ自体を「非公開」に設定した場合であっても、写真単体を「公開」に設定することができます。アクティビティ自体を非公開にしたいけど、絶景スポットを作成したり、写真を絶景スポットに追加したい場合には、写真が公開に設定されている必要があります。',
                                'アクティビティ自体を「非公開」に設定した場合、写真の設定はデフォルトで「非公開」に切り替わります。'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'title' => 'ストーリーの作成',
                'content' => [
                    [
                        'title' => 'チェックポイントの作成',
                        'text' => [
                            '地図上に表示されているコースにクリックした場所に「チェックポイント」が作成されます。チェックポイントはストーリーを構成する中で、ユーザーが指定した中間ポイントを意味します。例えば、景色が綺麗だった場所や、食事休憩をした場所、越えた峠等を指定することができます。チェックポイントを作成すると、タイムラインの中にも、適当の場所にアイコン、タイトルと文章（ストーリー）を記入できるフォームが表示されるので、それらの情報を記入し、ストーリーを作成していきます。'
                        ]
                    ],
                    [
                        'title' => 'タイムラインについて',
                        'text' => [
                            'アップロードした写真と、作成したチェックポイントが時間順でタイムラインに表示されていきます。ストーリーの作成はタイムラインで行われます。最終的には、アクティビティ紹介ページで表示されるタイムラインを構成します。'
                        ]
                    ]
                ]
            ],
            [
                'title' => 'アクティビティの保存',
                'text' => [
                    'ストーリーが完成すれば、保存ボタンをクリックします。',
                    '保存ボタンをクリックすると、様々なアルゴリズムが走行データを分析し、下記の順番でデータを処理します。'
                ],
                'content' => [
                    [
                        'title' => '1.	必要に応じて、写真データを既存の絶景スポットに追加',
                        'id' => 'sceneryphotoadding',
                        'text' => [
                            '公開設定となっている写真データの中で、座標データが既存の絶景スポットと一致する写真がないかを確認します。一致する写真がある場合のみ、該当する絶景スポットに追加するかどうかを選択できるウィンドウが表示されます。'
                        ]
                    ],
                    [
                        'title' => '2.	必要に応じて、絶景スポットの新規作成',
                        'id' => 'scenerymaking',
                        'text' => [
                            'タイムラインに掲載されている写真の中で、絶景スポットを作成するボタンをクリックした場合のみ、新規の絶景スポットの情報入力を記入するウィンドウが表示されます。タイトル、紹介文とタグを入力し、そして他に座標データが一致する写真がアップロードされている場合はその写真を追加するかどうかを選択したら、「確定」ボタンをクリックします。',
                            'point' => '絶景スポットは、サイクリングマップを通じて、コミュニティに共有されるのみならず、該当地域の活性化に活用されるなど、様々な用途に使われるので、新規作成にはいくつかの' .Manual::ref('scenery-guidelines', 'ルール'). 'が存在しています。ルールを守った絶景スポット作成にご協力ください。'
                        ]
                    ],
                    [
                        'title' => '3.	コース上にあるセグメント＆絶景スポットを訪問済みリストに追加'
                    ],
                    [
                        'title' => '4.	アクティビティ関連データの保存'
                    ]
                ]
            ]
        ]
    ],
    [
        'title' => 'アクティビティ紹介ページ',
        'path' => [
            'activity/[activity_id]'
        ],
        'text' => [
            '新規のアクティビティを作成し保存すると、アクティビティ紹介ページが自動生成されます。アクティビティ設定が「公開」または「限定公開」となっている場合、このページは誰にでも閲覧できます（サインインされていないユーザーを含む）。このページをTWITTER、' .Manual::temp('FACEBOOK、INSTAGRAM'). 'に共有できます。但し、作成者がプレミアムユーザーである場合の除き、サインインされていないユーザーに表示される地図は静的地図になります。'
        ],
        'content' => [
            [
                'title' => 'ヘッダー',
                'text' => [
                    'ページの上部には、ハイライト写真（指定がない場合は時間順で一枚目の写真）を背景に、アクティビティのタイトル、日付、ユーザー、そして関連ボタンが表示されます。',
                    'アクティビティの持ち主には、編集ボタンと削除ボタンが表示されます。'
                ]
            ],
            [
                'title' => 'SNSでアクティビティを発信する',
                'text' => [
                    'タイトルの下に、SNS各社のボタンが表示されています。ソーシャルネットワーク各社との連携を通じて、アクティビティのデータをもとにツイートしたり、シェアしたりすることができます。'
                ],
                'content' => [
                    [
                        'title' => 'Twitter',
                        'text' => [
                            'ツイッターボタンをクリックすると、ツイートの編集画面が表示されます。',
                            'デフォルトでは、ストーリーの内容をもとに、140文字以内で文言を自動生成していますが、内容を自由に編集できます。',
                            '下部では、添付したい写真を4枚まで選択できます。オプションで選択したデータ（タイトル、ユーザーネーム、写真の撮影時間とスタートからの距離）を写真の上に表示することができます。'
                        ]
                    ]
                ]
            ],
            [
                'title' => 'データ集計セクション',
                'text' => [
                    'ヘッダーの下に、アクティビティの集計データが記載されます。クリックすると、より詳細のデータが表示されます。標準値と比較して、高い数値は色の濃い背景で表示されています。'
                ]
            ],
            [
                'title' => '地図',
                'text' => [
                    Manual::ref('user-rights', 'ユーザーの権限'). 'によって、表示される地図が静的地図か動的地図かのどちらかになります。'
                ],
                'content' => [
                    [
                        'title' => '動的地図',
                        'text' => [
                            'コースのデータの上に、写真とチェックポイントが表示されます。',
                            '写真をクリックすると、拡大されます。チェックポイントをクリックすると、詳細（タイトル、距離、時間、ストーリー）が表示されます。',
                            'コースにカーソルを乗せると（モバイル端末の場合はタッチすると）、その位置の細かいデータ（距離、勾配、標高と時間）が表示されます。',
                            '通常の地図設定に加え、「ルート設定」が表示されます。距離マーク、3次元や絶景スポットを表示または非表示に設定できるほか、「全体表示」ボタンでコース全体が表示されるズームに自動調整したり、「走行再現ボタン」で走行再現モードに切り替えたりすることができます。「走行再現モード」に関しては、' .Manual::ref('routes-fly', 'こちら'). 'をご確認ください。',
                            'また、動的地図の場合に限り、標高図も表示され、標高図にカーソルを乗せることで同様のデータが表示されるのに加え、該当する地点が地図上にも勾配を反映した色分けで表示されます。'
                        ]
                    ],
                    [
                        'title' => '静的地図',
                        'text' => [
                            '静的地図の場合、写真と標高図は表示されませんが、コース図、写真とチェックポイントの位置は表示されます。',
                            '名の通り、ユーザーは地図を操作できません。地図は自動出力された単なる画像です。'
                        ]
                    ]
                ]
            ],
            [
                'title' => 'タイムライン',
                'text' => [
                    'タイムラインの左側には、時間を表す線にスタート、チェックポイントとゴールが点で表示されます。点にはアイコン、距離、時間、タイトルとストーリーが表示され、その間には該当する写真が時間順で表示されます。',
                    '動的地図の場合、写真やチェックポイントをクリックすると、該当する地点のポップアップが表示された状態で地図に移動し、どの場所に該当するかを確認することができます。',
                    '静的の場合、写真をクリックすると、画像を拡大できます。'
                ]
            ]
        ]
    ],
    [
        'title' => 'マイアクティビティページ',
        'path' => [
            '[user_login]/activities'
        ],
        'text' => [
            'このページでは、サインインしているユーザーの記録されているアクティビティ概要が時間順のカード標識で表示されます。カードをクリックすると、紹介ページに移行します。また、カードの下には「編集」と「削除」の管理ボタンが表示されており、ワンクリックで操作を行うことができます。'
        ]
    ],
    [
        'title' => '活動日記',
        'path' => [
            '/journal/[user_id]'
        ],
        'text' => [
            'このページでは、サインインしているユーザーの記録されているアクティビティ概要が時間順のカード標識で表示されます。カードをクリックすると、紹介ページに移行します。また、カードの下には「編集」と「削除」の管理ボタンが表示されており、ワンクリックで操作を行うことができます。'
        ]
    ],
    [
        'title' => 'パブリックアクティビティページ',
        'path' => [
            'activities'
        ],
        'text' => [
            'このページがサインインしているユーザーに関連性の高いアクティビティを表示します。友達になっているユーザーと、フォローしているユーザーのアクティビティが優先的に表示されます。数が少ない場合は、公開されているアクティビティの中で、人気度の高いアクティビティが表示されます。'
        ]
    ]
];