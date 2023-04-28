<?php

class Manual extends Model {

    private static $chapters = [
        'world' => [
            'title' => 'サイクリングマップ',
            'subtitle' => 'World'
        ],
        'activities' => [
            'title' => 'アクティビティ',
            'subtitle' => 'Activities'
        ],
        'routes' => [
            'title' => 'ルート',
            'subtitle' => 'Routes'
        ],
        'rides' => [
            'title' => 'ライド',
            'subtitle' => 'Rides'
        ],
        'community' => [
            'title' => 'コミュニティ',
            'subtitle' => 'Community'
        ],
        'user' => [
            'title' => 'ユーザー情報',
            'subtitle' => 'User information'
        ],
        'sceneryguidelines' => [
            'title' => '絶景スポットの作成ガイドライン',
            'subtitle' => 'Scenery spots creation guidelines'
        ],
        'data' => [
            'title' => 'データ活用について',
            'subtitle' => 'Data usage'
        ],
    ];

    // Ordered by chapter. 'chapter' and 'id' values are used to build URL.
    private static $refs = [
        'world' => [
            'chapter' => 'world'
        ],
        'world-staticdata' => [
            'chapter' => 'world',
            'id' => 'staticdata'
        ],
        'sceneries' => [
            'chapter' => 'world',
            'id' => 'sceneries'
        ],
        'scenery-guidelines' => [
            'chapter' => 'sceneryguidelines'
        ],
        'tags' => [
            'chapter' => 'world',
            'id' => 'tags'
        ],
        'segments' => [
            'chapter' => 'world',
            'id' => 'segments'
        ],
        'activity-scenerymaking' => [
            'chapter' => 'activities',
            'id' => 'scenerymaking'
        ],
        'activity-sceneryphotoadding' => [
            'chapter' => 'activities',
            'id' => 'sceneryphotoadding'
        ],
        'routes-single' => [
            'chapter' => 'routes',
            'id' => 'single'
        ],
        'routes-buildmode' => [
            'chapter' => 'routes',
            'id' => 'buildmode'
        ],
        'routes-fly' => [
            'chapter' => 'routes',
            'id' => 'fly'
        ],
        'rides' => [
            'chapter' => 'rides'
        ],
        'rides-pickmode' => [
            'chapter' => 'rides',
            'id' => 'pickmode'
        ],
        'rides-drawmode' => [
            'chapter' => 'rides',
            'id' => 'drawmode'
        ],
        'rides-highlightphoto' => [
            'chapter' => 'rides',
            'id' => 'highlightphoto'
        ],
        'rides-privacy-settings' => [
            'chapter' => 'rides',
            'id' => 'privacysettings'
        ],
        'rides-admin' => [
            'chapter' => 'rides',
            'id' => 'admin'
        ],
        'rides-admin-panel' => [
            'chapter' => 'rides',
            'id' => 'adminpanel'
        ],
        'community-neighbours' => [
            'chapter' => 'community',
            'id' => 'neighbours'
        ],
        'user-level' => [
            'chapter' => 'user',
            'id' => 'level'
        ],
        'user-bikes' => [
            'chapter' => 'user',
            'id' => 'bikes'
        ],
        'user-location' => [
            'chapter' => 'user',
            'id' => 'location'
        ],
        'user-settings' => [
            'chapter' => 'user',
            'id' => 'settings'
        ],
        'user-settings-neighbours' => [
            'chapter' => 'user',
            'id' => 'settingneighbours'
        ],
        'user-settings-realname' => [
            'chapter' => 'user',
            'id' => 'settingrealname'
        ],
        'user-rights' => [
            'chapter' => 'user',
            'id' => 'rights'
        ],
        'scenery-guidelines-title' => [
            'chapter' => 'sceneryguidelines',
            'id' => 'title'
        ],
        'data' => [
            'chapter' => 'data'
        ]
    ];

    public static function baseUri () {
        return '/manual';
    }
    
    public static function currentChapter () {
        $url_parts = explode('/manual/', $_SERVER['REQUEST_URI']);
        if (isset($url_parts[1])) return $url_parts[1];
        else return false;
        
    }

    public static function summary () {
        echo '<ul class="m-summary">';
        foreach (self::$chapters as $path => $chapter) {
            echo '
            <a href="' .self::baseUri(). '/' .$path. '">
                <li>'
                    .$chapter['title'].
                    '<div class="m-summary-subtitle"> (' .$chapter['subtitle']. ')</div>
                </li>
            </a>';
            if ($path == self::currentChapter()) echo'<div class="m-summary-details"></div>';
        }
        echo '</ol>';
    }

    public static function chapterTitle ($chapter_name) {
        if (isset(self::$chapters[$chapter_name])) {
            self::title(1, self::$chapters[$chapter_name]['title']);
            echo '<div class="m-subtitle">' .self::$chapters[$chapter_name]['subtitle']. '</div>';
        }
    }
    
    public static function title ($h, $title, $id = false) { // h = number
        if ($id) echo '<h' .$h. ' id="' .$id. '">' .$title. '</h' .$h. '>';
        else echo '<h' .$h. '>' .$title. '</h' .$h. '>';
    }

    public static function path ($path_data) {
        echo '<div class="m-path">';
        foreach ($path_data as $path_string) {
            
            // If path in slug
            $start = strpos($path_string, '[');
            if ($start !== false) {
                // Extract from path and get corresponding random slug number
                $end = strpos($path_string, ']') - $start + 1;
                $slug_string = substr($path_string, $start, $end);
                $random_slug = self::getRandomSlug($slug_string);
                // Replace slug string by slug random number
                $path_url = str_replace($slug_string, $random_slug, $path_string);
            } else $path_url = $path_string;

            echo '<div><a href="../' .$path_url. '" target="_blank">/' .$path_string. '</a></div>';
        }
        echo '</div>';
    }

    private static function getRandomSlug ($slug) {
        switch ($slug) {
            // Get database table name depending on slug string
            case '[user_id]': 
                // If user is connected, return connected user id
                if (isset($_SESSION['auth'])) $query_string = "SELECT id FROM users WHERE id = {$_SESSION['id']} ORDER BY RAND() LIMIT 1";
                // Else return a random user
                else $query_string = "SELECT id FROM users ORDER BY RAND() LIMIT 1";
                break;
            case '[user_login]':
                // If user is connected, return connected user login
                if (isset($_SESSION['auth'])) $query_string = "SELECT login FROM users WHERE id = {$_SESSION['id']}";
                // Else return a random user
                else $query_string = "SELECT login FROM users ORDER BY RAND() LIMIT 1";
                break;
            case '[activity_id]':
                // If user is connected, return a random activity from connected user
                if (isset($_SESSION['auth'])) $query_string = "SELECT id FROM activities WHERE user_id = {$_SESSION['id']} ORDER BY RAND() LIMIT 1";
                // Else return a random activity
                else $query_string = "SELECT id FROM activities WHERE privacy = 'public' ORDER BY RAND() LIMIT 1";
                break;
            case '[ride_id]': 
                // If user is connected, return a random ride from connected user
                if (isset($_SESSION['auth'])) $query_string = "SELECT id FROM rides WHERE author_id = {$_SESSION['id']} ORDER BY RAND() LIMIT 1";
                // Else return a random activity
                else $query_string = "SELECT id FROM rides WHERE privacy = 'public' ORDER BY RAND() LIMIT 1";
                break;
            case '[route_id]': 
                // If user is connected, return a route among his routes if exists, else return a random public route
                if (isset($_SESSION['auth'])) {
                    $query_string = "SELECT id FROM routes WHERE
                    IF (EXISTS (SELECT id FROM routes WHERE author_id = {$_SESSION['id']}), 
                    author_id = {$_SESSION['id']},
                    privacy = 'public')
                    ORDER BY RAND() LIMIT 1";
                // Else return a random public route
                } else $query_string = "SELECT id FROM routes WHERE privacy = 'public' ORDER BY RAND() LIMIT 1";
                break;
            case '[scenery_id]': $query_string = "SELECT id FROM sceneries ORDER BY RAND() LIMIT 1"; break;
            case '[segment_id]': $query_string = "SELECT id FROM segments ORDER BY RAND() LIMIT 1"; break;
        }
        // Get number of entries and return a random number among it
        $getEntries = self::getPdo()->prepare($query_string);
        $getEntries->execute();
        $slug = $getEntries->fetch(PDO::FETCH_COLUMN);
        return $slug;
    }

    public static function intro ($paragraphs) { // paragraphs = array
        echo '<div class="m-intro">';
        foreach ($paragraphs as $class => $text) {
            if (is_numeric($class)) echo '<p>' .$text. '</p>';
            else if ($class == 'list') self::list($text['style'], $text['content']);
            else if ($class == 'table') self::table($text);
            else echo '<div class="m-' .$class. '">' .$text. '</div>';
        }
        echo '</div>';
    }

    public static function text ($paragraphs) { // paragraphs = array
        foreach ($paragraphs as $class => $text) {
            if (is_numeric($class)) echo '<p>' .$text. '</p>';
            else if ($class == 'list') self::list($text['style'], $text['content']);
            else if ($class == 'table') self::table($text);
            else echo '<div class="m-' .$class. '">' .$text. '</div>';
        };
    }

    public static function list ($style, $content) {
        if ($style == 'number') echo '<ol>';
        else echo '<ul>';
        foreach ($content as $item) echo '<li>' .$item. '</li>';
        if ($style == 'number') echo '</ol>';
        else echo '</ul>';
    }

    public static function table ($content) {
        echo '<table><tbody>';
        foreach ($content as $tr) {
            echo '<tr>';
            foreach ($tr as $td) {
                echo '<' .$td['type']. '>' .$td['text']. '</' .$td['type']. '>';
            }
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    public static function ref ($ref_name, $text) {
        $ref_data = self::$refs[$ref_name];
        if (isset($ref_data['id'])) return '<a href="' .self::baseUri(). '/' .$ref_data['chapter']. '#' .$ref_data['id']. '">' .$text. '</a>';
        else return '<a href="' .self::baseUri(). '/' .$ref_data['chapter']. '">' .$text. '</a>';
    }

    public static function temp (string $text) {
        return '<span class="m-temp" title="現時点では、この機能はまだ開発中です。">' .$text. '</span>';
    }
    
    public static function point ($point) {
        echo '<div class="m-point">' .$point. '</div>';
    }

}