<?php

$intro = [
    'CyclingFriendsは、ユーザープロファイルで様々な情報を記入すればするほど機能性が高まります。とはいえ、流出する情報はユーザーご自身で選ぶことができるようになっています。CyclingFriendsをご利用頂く上で、情報やデータの入力方法、そしてそのデータ活用について、必ず把握するようにしましょう。',
    'CyclingFriendsに掲載されるデータの所有と活動については、' .Manual::ref('data', 'こちら') .'をご確認ください。'
];

$content = [
    [
        'title' => 'ユーザープロフィール',
        'path' => [
            'rider/[user_id]',
            'profile/edit'
        ],
        'text' => [
            'プラットフォーム上の様々な場所で表示されるユーザーのプロフィール画像をクリックすると、ユーザープロフィールが表示されます。ユーザープロフィールのページでは、ユーザーに関する基本情報やサイクリング活動情報を確認したり、繋がったりすることができます。',
            '自分のプロフィールページは、メニューの右側にあるプロフィール画像をクリックすることで、表示されているページにかかわらず、いつでもアクセスすることができます。',
            '原則として、入力のない設定項目はプロフィールページに表示されませんが、一部の項目に関しては、' .Manual::ref('user-settings', 'ユーザー設定ページ'). 'で公開設定を指定できるので、入力したとしても非表示にすることができます。'
        ],
        'content' => [
            [
                'title' => 'ヘッダー',
                'text' => [
                    'ヘッダーでは、ユーザー名、各SNS情報、そして操作ボタンが表示されます。',
                    '自分のプロフィールページを表示している場合、編集ボタンが表示され、クリックすると、プロフィール関連情報の入力／編集ページに移動します。',
                    '他ユーザーのプロフィールページを表示している場合、フォローと友達申請ボタンが表示されます。',
                    'ヘッダーの下部には、フレンズリスト（ユーザーの友達リスト）が表示され、クリックすると、その友達のプロフィールページにもアクセスできます。'
                ]
            ],
            [
                'title' => '基本情報',
                'content' => [
                    [
                        'title' => 'プロフィール画像',
                        'id' => 'profilePicture',
                        'text' => [
                            'プロフィール画像は、プラットフォーム全体でユーザーを象徴する代替アイコンです。プラットフォーム全体で表示され、真っ先に他人の目に入るものなので、慎重に選びましょう。',
                            'point' => '対応ファイル形式は*.JPGになります。高画質のものをアップロードする場合、ファイルが自動的に圧縮されることがあります。'
                        ]
                    ],
                    [
                        'title' => '姓名',
                        'id' => 'name',
                        'text' => [
                            'ユーザー名とは別に、実名情報を設定していただけます。表示設定にしている場合のみ他ユーザーに表示されます。',
                            'point' => 'ライドに応募する際に設定が必要ですが、入力するかどうかは任意ですし、入力したとしても、公開されるかどうかは' .Manual::ref('user-settings-realname', '設定ページ'). 'で指定できます。'
                        ]
                    ],
                    [
                        'title' => '性別',
                        'text' => [
                            '「男」「女」「特定なし」の中から選択できます。'
                        ]
                    ],
                    [
                        'title' => '生年月日',
                        'text' => [
                            '生年月日を設定すると、プロフィールページで年齢情報が表示されます。',
                            'point' => 'ライドに応募する際に設定が必要です。'
                        ]
                    ],
                    [
                        'title' => '活動拠点',
                        'id' => 'location',
                        'text' => [
                            '主な活動拠点の位置情報です。原則として居住地に該当しますが、その限りではありません。他ユーザーと繋がるためや、アルゴリズムを介して紹介されるセグメント、ライドや絶景スポットやその距離情報など、様々な機能に反映されるので、できる限り設定しましょう。'
                        ],
                        'content' => [
                            [
                                'title' => '設定方法',
                                'text' => [
                                    'プロフィールページで「地図で選択」ボタンをクリックすると、活動拠点を設定できる地図が表示されます。表示されるマーカーポイントを動かしたり、地図上にクリックしたりすることで設定することができます。',
                                    'point' => Manual::ref('community-neighbours', 'お隣機能'). 'を利用するには設定が必要です。利用するには、合わせて設定ページの' .Manual::ref('user-settings-neighbours', '「プライバシー」セクションで公開を認める'). '必要もあります。'
                                ]
                            ]
                        ]
                    ],
                    [
                        'title' => 'レベル',
                        'id' => 'level',
                        'text' => [
                            '「初心者」、「中級者」、「上級者」の中から自分のレベルに該当するものを選べます。',
                            'ルート、ライドやセグメントの表示など、様々な機能のアルゴリズムに反映されるので、できるだけ設定しましょう。',
                            'point' => 'デフォルトでは「初心者」に設定されます。'
                        ]
                    ],
                    [
                        'title' => '登録日時',
                        'text' => [
                            'CyclingFriendsのアカウント作成日が表示されます。',
                            'この項目は変更したり、非公開にしたりすることができません。'
                        ]
                    ],
                    [
                        'title' => '最近の写真一覧',
                        'text' => [
                            'ユーザーが投稿したアクティビティで公開設定になっている写真の中から、直近のものが数枚表示されます。クリックすると、そのアクティビティの詳細（「…」URL）を確認することができます。'
                        ]
                    ],
                    [
                        'title' => 'バイク情報',
                        'id' => 'bikes',
                        'text' => [
                            'ユーザーが登録しているバイクの写真と詳細情報が表示されます。',
                            'このバイクを利用して登録したアクティビティの合計距離も表示されます。'
                        ],
                        'content' => [
                            [
                                'title' => '設定方法',
                                'text' => [
                                    'バイク情報はプロフィール編集ページで設定することができます。
                                    新規のバイクを追加するには、「自転車を追加」をクリックし、車種、車種（モデル）、ホイール、コンポネント、紹介文を記入します。削除するには、画像にある×ボタンをクリックします。
                                    '
                                ]
                            ]
                        ]
                    ],
                    [
                        'title' => '活動情報',
                        'text' => [
                            '総距離、アクティビティ数、過去28日間の合計距離とアクティビティ数、そして直近のアクティビティの概要が表示されます。'
                        ]
                    ],
                    [
                        'title' => '権限',
                        'id' => 'rights',
                        'text' => [
                            'point' => 'スタンダートユーザーとプレミアムユーザーの２カテゴリーのユーザーが存在します。ベータ版では、プレミアム権限はご寄付頂いた方に与えさせて頂く予定です。公式版が公開されたタイミングで、プレミアムプランをサブスクリプション型で展開していく予定です。'
                        ]
                    ]
                ]
            ]
        ]
    ],
    [
        'title' => 'ユーザー設定',
        'id' => 'settings',
        'path' => [
            'settings'
        ],
        'text' => [
            'CyclingFriendsを使う上では、アカウントの管理やプライバシー設定など、様々な項目を設定する必要があります。この設定は、ユーザー設定ページで行うことができます。'
        ],
        'content' => [
            [
                'title' => 'アカウント',
                'content' => [
                    [
                        'title' => 'パスワードを変更する',
                        'text' => [
                            '現在のパスワードと新しいパスワードを入力することで、パスワードを変更することができます。'
                        ]
                    ],
                    [
                        'title' => 'メールアドレスを変更する',
                        'text' => [
                            '現在のパスワードと新しいメールアドレスを入力すれば、メールアドレスを変更することができます。'
                        ]
                    ],
                    [
                        'title' => 'パスワードの再発行',
                        'text' => [
                            'パスワードを忘れた場合、こちらでパスワードの再発行を行うことができます。',
                            '再発行を依頼した場合、新しいパスワードが登録済みのメールアドレスに送付されます。'
                        ]
                    ]
                ]
            ],
            [
                'title' => '設定',
                'content' => [
                    [
                        'title' => 'プライバシー',
                        'content' => [
                            [
                                'title' => 'Neighboursページでアカウント情報を表示しない',
                                'id' => 'settingneighbours',
                                'text' => [
                                    '自分のアカウント情報がNeighboursページで表示されてほしくない方は、こちらで非表示に設定できます。'
                                ]
                            ],
                            [
                                'title' => '実名を公開しない',
                                'id' => 'settingrealname',
                                'text' => [
                                    '実名が公開されてほしくない方は、こちらで非公開に設定することができます。',
                                    'point' => 'ただし、ライドに参加する場合は主催者に公開されます。'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];