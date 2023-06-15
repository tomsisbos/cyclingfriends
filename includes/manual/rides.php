<?php

$intro = [
    'CyclingFriendsで大切にしている「リモート」と「リアル」の中間にあるのは、「ライド開催機能」です。集合場所、対象レベルや人数等、そしてコースやライド内容を入力さえすれば、告知ページが自動的に作成されます。ユーザーの所在地や特徴を考慮したアルゴリズムを介して、条件を満たすユーザーに表示されるほか、サイクルガイドマップ上にも表示されます。',
    'CyclingFriendsを通じて、同じ地域のサイクリストが繋がったり、知らない土地を地元のサイクリストに案内してもらえたりできるようになるというのが、CyclingFriendsが持つコミュニティ性の極致であり、CyclingFriendsが目指す新しい社会像のひとつです。'
];

$content = [
    [
        'title' => 'ライドの新規作成／編集',
        'path' => [
            'ride/new',
            'ride/[ride_id]/edit'
        ],
        'text' => [
            '新規ライドの作成と既存ライドの編集は、①基本情報の設定、②コースの設定、③入力情報の確認という3つのステップを経て行われます。作成されたライドの管理に関しては、' .Manual::ref('rides-admin', 'ライドの管理'). 'をご確認ください。',
            'point' => '次のステップに進むまでは、入力した情報は保存されませんので、ご注意ください。'
        ],
        'content' => [
            [
                'title' => '① 基本情報の設定',
                'content' => [
                    [
                        'title' => 'タイトル',
                        'text' => [
                            'ライドの名称を最初に入力します。掲示板等で最初に目に入るものなので、少ない文字数でライドの内容や魅力が分かりやすく伝わるタイトルが好ましいです。'
                        ]
                    ],
                    [
                        'title' => '開催時間',
                        'text' => [
                            '開催日、集合時間、出発時間と解散時間を入力します。',
                            '複数名の参加を想定する場合は、集合時間は、出発時間より20分以上前に設定することをおススメします。',
                            '解散時間が正確に分からない場合は、余裕をもって遅めに設定しておくことをおススメします。'
                        ]
                    ],
                    [
                        'title' => '参加人数',
                        'text' => [
                            '最低催行人数（1名から）と定員（7名まで）を指定します。',
                            'point' => '公道で行われる関係で、一つの集団で安全に走行できる人数を８名と考え、募集可能最大人数を原則として7名を上限とさせていただいております。' .Manual::temp('しかし、「CyclingFriends公認ガイド」申請をして頂ければ、最大30名までに拡大することができます。')
                        ]
                    ],
                    [
                        'title' => '参加対象',
                        'text' => [
                            '歓迎する参加者のレベルと、認める車種を限定することができます。該当する情報を登録していない参加者が応募する場合、応募希望者に注意の通知連絡が表示され、その旨承諾をして頂くことになっています。'
                        ]
                    ],
                    [
                        'title' => '紹介文の作成',
                        'text' => [
                            'ライドの全体紹介や、ライドに関する総合的な案内を詳細に記載します。',
                            'point' => [
                                'コースに関する詳細案内は、②コースの設定にある紹介文の記入欄で記入することになっています。',
                                '上記の項目にない特定の確認事項や、参加者から特定の情報を記入していただきたい場合は、ライド作成後の' .Manual::ref('rides-admin', 'ライドの管理'). 'にて指定していただけます。'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'title' => '② コースの設定',
                'text' => [
                    'コースの設定は、Ⓐ' .Manual::ref('rides-pickmode', 'チェックポイントのみ設定する方法'). 'とⒷ' .Manual::ref('rides-drawmode', '自分のルートの中から選ぶ方法'). 'という２つの方法があります。'
                ],
                'content' => [
                    [
                        'title' => 'コースの設定',
                        'content' => [
                            [
                                'title' => 'Ⓐチェックポイントのみ設定する方法',
                                'id' => 'pickmode',
                                'content' => [
                                    [
                                        'title' => '利用用途',
                                        'text' => [
                                            'チェックポイントのみ設定する方法は、コース情報をざっくりと定めるだけなので、特定のルート情報がライドに付随されません。集合と解散場所と大まかな内容以外、詳しいコースが定まっていないケースに適しています。'
                                        ]
                                    ],
                                    [
                                        'title' => '基本操作',
                                        'text' => [
                                            '表示される地図にクリックすると、クリックした位置にチェックポイントが順番に作成されていきます。最初に作成したチェックポイントがスタート（集合場所）、最後に作成したチェックポイントがゴール（解散場所）に指定されます。',
                                            'チェックポイントを表現するマーカーをクリックすると、チェックポイントのタイトル、詳細文と画像を定められるポップアップが表示されます。',
                                            '右クリックすると、クリックしたチェックポイントが削除されます。',
                                            'クリアボタンをクリックすると、白紙状態に戻すことができます。',
                                            'スタート（集合場所）とゴール（解散場所）を同一地点にしたい場合は、「スタートとゴールを同一地点にする」チェックボックスにレ点を入れます。',
                                            'point' => '集合場所と解散場所の指定にもなるため、ライドの作成に進むには、最低でもスタートとゴールのチェックポイントを設定しなければなりません。'
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'title' => 'Ⓑ自分のルートの中から選ぶ方法',
                                'id' => 'drawmode',
                                'content' => [
                                    [
                                        'title' => '利用用途',
                                        'text' => [
                                            '自分のルートの中から選ぶ方法は、ライド情報に正確なルート情報も付随する方法です。集合場所、解散場所、距離や起伏等の情報は自動的に算出されます。また、チェックポイントの設定はコース上で行い、チェックポイントには距離の情報も自動的に付随されます。正確なコース情報を事前に把握できると、安全性と利便性の側面では主催者にとっても参加者にとってももっとも利便性が高いので、できるだけこの方法を優先しましょう。'
                                        ]
                                    ],
                                    [
                                        'title' => '基本操作',
                                        'text' => [
                                            '最初に、地図下にあるセレクトボックスを使い、自分のルートの中からルートを選択します。選択すると、スタートとゴールの固定的チェックポイントと共に、地図上に表示されます。',
                                            'point' => 'スタートとゴールが同一地点になっている場合、「SF」の表記で一つのチェックポイントにまとまります。',
                                            '表示されたルートにクリックすると、スリックした位置にチェックポイントが作成されます。チェックポイントは自動的にコース順に整理されます。',
                                            'チェックポイントを表現するマーカーをクリックすると、チェックポイントのタイトル、詳細文と画像を定められるポップアップが表示されます。',
                                            '右クリックすると、クリックしたチェックポイントが削除されます。'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'title' => 'コース情報の入力',
                        'content' => [
                            [
                                'title' => '距離',
                                'text' => [
                                    '詳細なコースが定められないため、コースの距離を手動で定める必要があります。「約」と表示されているチェックボックスにレ点を入れると、距離の数字の前に「約」と記されますが、応募する参加者が大まかな難易度とスケジュール感をイメージできるように、大きく外れることがないように注意しましょう。',
                                    'point' => 'ルート情報を付随する場合（Ⓐを除く方法）は自動的に算出されます。'
                                ]
                            ],
                            [
                                'title' => '起伏',
                                'text' => [
                                    '「平坦」「多少の坂あり」「坂あり」「山岳」の中から指定することができます。' .Manual::temp('距離情報と合わせて、対象レベルのユーザーに対して優先的に表示するために活用されるので、できるだけコースの特性を反映する起伏情報を指定しましょう。'),
                                    'point' => 'ルート情報を付随する場合（Ⓐを除く方法）は自動的に算出されます。'
                                ]
                            ],
                            [
                                'title' => 'コース紹介',
                                'text' => [
                                    'チェックポイントやルートに関連する情報では語り切れない詳細をこちらで記入します。文章での説明は、ルート情報を付随しない場合には特に欠かせないので、コースの特徴や魅力をできるだけ詳細に、分かりやすく記載しましょう。'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                'title' => '➂ コース情報の入力',
                'text' => [
                    'ライドを作成する前には、サマリーページにて、記入した詳細が正しいかどうか、記入漏れがないかどうかを確認します。記入漏れがある場合は、記入漏れがあった情報欄が赤で強調されます。',
                ],
                'content' => [
                    [
                        'title' => 'ハイライト画像の設定',
                        'id' => 'highlightphoto',
                        'text' => [
                            'チェックポイントにクリックすると、チェックポイントに付随される写真がハイライト写真に選定されます。',
                            'point' => 'ハイライト写真を選定しない場合は、画像情報がある最初の一枚がハイライト写真に用いられます。'
                        ]
                    ],
                    [
                        'title' => 'ライドの作成',
                        'text' => [
                            '入力情報の確認が終われば「作成する」ボタンをクリックします。特に問題なければ、ライドが作成されます。',
                            'ライドを作成するだけで、他のユーザーからの応募を受け付けられるわけではありません。デフォルトでは、新規作成されたライドは「非公開」設定になっており、エントリーが開始されていない状態になっているため、最初にエントリー期間を定め、プライバシーを「公開」または「友達のみ」に設定する必要があります。',
                            '自分が管理者になっているライドを管理するには、「マイライドページ」から「ライド紹介ページ」にアクセスする必要があります。'
                        ]
                    ]
                ]
            ]
        ]
    ],
    [
        'title' => 'マイライドの管理',
        'path' => [
            substr($router->generate('ride-organizations'), 1)
        ],
        'text' => [
            'マイライドページでは、管理者権限のあるライド概要情報一覧が作成日の近い順で表示されます。ハイライト画像又は「詳細」ボタンをクリックすると、ライド紹介ページにアクセスします。「管理」、「編集」や「削除」ボタンでワンクリックの管理操作も可能です。'
        ]
    ],
    [
        'title' => 'ライド紹介ページ',
        'path' => [
            'ride/[ride_id]'
        ],
        'text' => [
            '紹介ページは、ライドの概要を表示します。観覧できるかどうかは、' .Manual::ref('rides-privacy-settings', 'ライドのプライバシー設定'). 'によります。'
        ],
        'content' => [
            [
                'title' => 'ヘッダー',
                'text' => [
                    Manual::ref('rides-highlightphoto', 'ハイライト写真'). 'を背景に、プライバシー設定や募集状況、タイトル、主催者情報と操作ボタンが表示されます。操作ボタンは、管理者には「管理」、「編集」と「削除」ボタンが表示され、それ以外のユーザーには「参加」ボタンが表示されます。',
                    '参加ボタンにクリックする際、主催者から求められる情報を入力して頂き、初めてエントリーが完了します。',
                    'point' => 'ライドに参加するには、実名情報と生年月日情報（' .Manual::ref('user-settings-realname', '非公開に設定することは可能'). '）の入力が必要になります。プロフィールページで設定していない場合、入力できるモーダルウィンドウが表示されます。'
                ]
            ],
            [
                'title' => '参加者パネル',
                'text' => [
                    'ライドに参加する予定のユーザーが表示されます。'
                ],
                'content' => [
                    [
                        'title' => 'プライバシー設定',
                        'id' => 'privacysettings',
                        'text' => [
                            '「公開」、「非公開」と「友達のみ」の中からライドのプライバシーモードを設定できます。',
                            '「非公開」の場合、だれにも表示されず、参加者を募集することができません。',
                            '「友達のみ」の場合、管理者の友達リストに入っているユーザーのみライド情報を観覧し、応募できます。',
                            '「公開」の場合、どのユーザーでも応募することができます。',
                            'point' => 'プライバシー設定が「公開」となっているライドのみがサイクルガイドマップに表示されます。'
                        ]
                    ],
                    [
                        'title' => '募集期間',
                        'text' => [
                            'ライド紹介ページ観覧可能のユーザーが応募できる期間を設定できます。設定した募集期間の前後には、参加ボタンが表示されません。'
                        ]
                    ]
                ]
            ],
            [
                'title' => 'ライド情報',
                'text' => [
                    '開催日、集合／出発時間と集合場所、対象レベルと車種、そして主催者による詳細文章が表示されます。'
                ]
            ],
            [
                'title' => 'コース情報',
                'content' => [
                    [
                        'title' => 'チェックポイント',
                        'text' => [
                            'スタートからゴールまで、立ち寄る予定の主なスポット情報が距離と共に「チェックポイント」形式で順番に表示されるので、ライド内容をイメージできます。クリックすると、画像がモーダルウィンドウで拡大されます。',
                            'point' => '管理者権限のあるユーザーには、モーダルウィンドウでチェックポイントのタイトルと紹介文を編集できる記入欄も表示されます。'
                        ]
                    ],
                    [
                        'title' => 'コース詳細',
                        'text' => [
                            '距離と起伏情報、そして主催者によるコースの紹介文が表示されます。',
                            '正確なルート情報が定められている場合、ルート図のサムネイルも表示され、クリックすると、ルートの詳細ページにアクセスし細かく確認できます。',
                            'point' => 'ライド紹介ページからルート紹介ページにアクセスする場合、そのライドのルートとして表示されるので、通常のルート情報に加え、チェックポイント情報も表示されます。'
                        ]
                    ]
                ]
            ],
            [
                'title' => 'チャット',
                'text' => [
                    'ライドについて、質問やメッセージを残すことができます。',
                    Manual::temp('新規のメッセージが送信されると、管理者が通知されます。')
                ]
            ]
        ]
    ],
    [
        'title' => 'ライド管理ページ',
        'id' => 'admin',
        'path' => [
            'ride/[ride_id]/admin'
        ],
        'text' => [
            'ライド管理ページでは、エントリーリストを確認したり、ユーザーが応募する際に主催者から徴収する情報を設定したり、' .Manual::temp('その他ライドの細かい設定を調整したりすることができます。')
        ],
        'content' => [
            [
                'title' => 'エントリーリスト',
                'text' => [
                    '応募があったユーザーの情報が一覧で確認できます。姓名、性別、年齢' .Manual::temp('とメールアドレス'). 'の基本情報に加え、「質問の設定」で指定された質問への回答も表示されます。'
                ]
            ],
            [
                'title' => '質問の設定',
                'text' => [
                    'この部門では、ユーザーが応募する際に回答が求められる質問を設定できます。回答方法は「記入式」と「選択式」の2種類から選択できます。「選択式」を選択した場合、ユーザーに表示される選択肢の入力も必要です。',
                    '入力が終われば、「質問を追加」を選択し、質問がリストに追加されます。設定された質問は、「編集」と「削除」ボタンを使い、操作することができます。',
                    'point' => [
                        '「選択式」の場合、最低でも二つ以上の選択肢の設定が必要です。',
                        '質問を設定する前に応募したユーザーから、遡って情回答を求めることができないので、ユーザーから特定の情報を徴収したい場合は、プライバシー設定を「公開」にする前に慎重に設定しましょう。'
                    ]
                ]
            ]
        ]
    ],
    [
        'title' => 'ライド掲示板',
        'path' => [
            'rides'
        ],
        'text' => [
            'このページには、観覧可能なライドが、プライバシー設定が「友達のみ」となっているライドから、開催時間順で表示されます。'
        ],
        'content' => [
            [
                'title' => 'フィルター',
                'text' => [
                    '開催日、' .Manual::temp('開催地域'). '、参加可能車種、レベル、タイトル、募集状況、ユーザーと管理者の関係性から表示されるライドにフィルターをかけることができます。'
                ]
            ],
            [
                'title' => 'ライド一覧',
                'text' => [
                    'かけたフィルターに該当するライドはその下に表示されます。'
                ]
            ]
        ]
    ]
];