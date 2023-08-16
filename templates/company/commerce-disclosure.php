
<?php

include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/home.css" />

<body> <?php

    include '../includes/navbar.php'; ?>

    <!-- Main container -->
    <div class="container end">

        <h2>特定商取引法に基づく表記</h2>

        <table class="company-table mt-4">
            <tbody>
                <tr>
                    <th class="text-right">
                        法人名
                    </th>
                    <td>
                        株式会社テラインコグニタ</td>
                </tr>
                <tr>
                    <th class="text-right">
                        所在地
                    </th>
                    <td>
                        〒417-0801<br>
                        静岡県富士市大淵75-13
                    </td>
                </tr>
                <tr>
                    <th class="text-right">
                        電話番号
                    </th>
                    <td>
                        請求があったら遅滞なく開示します
                    </td>
                </tr>
                <tr>
                    <th class="text-right">
                        お問い合わせ
                    </th>
                    <td>
                        <a href="<?= $router->generate('company-contact') ?>" target="_blank">こちら</a>を参照
                    </td>
                </tr>
                <tr>
                    <th class="text-right">
                        メールアドレス
                    </th>
                    <td>
                        contact@cyclingfriends.co
                    </td>
                </tr>
                <tr>
                    <th class="text-right">
                        サイトURL
                    </th>
                    <td>
                        <a href="https://www.cyclingfriends.co">https://www.cyclingfriends.co</a>
                    </td>
                </tr>
                <tr>
                    <th class="text-right">
                        運営統括責任者
                    </th>
                    <td>
                        ボシス トム
                    </td>
                </tr>
                <tr>
                    <th class="text-right">
                        引渡時期
                    </th>
                    <td>
                        なし（サービス業のため）
                    </td>
                </tr>
                <tr>
                    <th class="text-right">
                        受け付け可能な決済手段
                    </th>
                    <td>
                        クレジットカード、Google Pay
                    </td>
                </tr>
                <tr>
                    <th class="text-right">
                        決済期間
                    </th>
                    <td>
                        即時
                    </td>
                </tr>
                <tr>
                    <th class="text-right">
                        販売価格
                    </th>
                    <td>
                        各商品ページに記載の金額
                    </td>
                </tr>
                <tr>
                    <th class="text-right">
                        追加手数料等の追加料金
                    </th>
                    <td>
                        なし（決済画面記載のとおり）
                    </td>
                </tr>
                <tr>
                    <th class="text-right">
                        交換および返品（返金ポリシー）
                    </th>
                    <td>
                        なし（サービス業のため）
                    </td>
                </tr>
                <tr>
                    <th class="text-right">
                        キャンセルについて
                    </th>
                    <td>
                        消費者の都合による、基本的に対応しておりません。<br>
                        ツアーの中止等、主催者の都合によるキャンセルに関しましては、手数料を引いた金額に相当したポイント数に変換とさせて頂きます。ポイントは無条件で他の商品にご使用いただけます。<br>
                        詳細に関しましては、<a href="<?= $router->generate('ride-contract') ?>" target="_blank">ツアー規約</a>をご参照ください。
                    </td>
                </tr>
                <tr>
                    <th class="text-right">
                        ポイント利用について
                    </th>
                    <td>
                        CyclingFriendsでは、「CFポイント」という名の割引ポイント制度を導入しています。<br>
                        あらゆる手段で貯めたCFポイントを、1ポイント＝1円の計算式で、当サイトで販売しているツアーや商品の購入等の際に使用し、割引に変換できます。<br>
                        CFポイントを保有している場合、500円を超える商品価格分に対して自動的に割引に変換されます。<br>
                        但し、商品の購入価格が500円以下になる分に関しましては割引を適応できませんので、CFポイントを利用しきれなかった分は次の購入時に適応されます。<br>
                    </td>
                </tr>
            </tbody>
        </table>

    </div><?php

    include '../includes/foot.php'; ?>

</body>
</html>