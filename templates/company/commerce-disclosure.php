
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
                        販売業者
                    </th>
                    <td>
                        株式会社テラインコグニタ</td>
                </tr>
                <tr>
                    <th class="text-right">
                        代表責任者
                    </th>
                    <td>
                        代表取締役CEO ボシス トム
                    </td>
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
                        連絡情報
                    </th>
                    <td>
                        <a href="<?= $router->generate('company-contact') ?>" target="_blank">こちら</a>を参照
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
                        販売価格
                    </th>
                    <td>
                        各商品の紹介ページに記載している価格とします。
                    </td>
                </tr>
                <tr>
                    <th class="text-right">
                        商品代金以外に必要な料金
                    </th>
                    <td>
                        消費税込みの表示価格となります
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