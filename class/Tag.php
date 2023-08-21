<?php

class Tag extends Model {

    function __construct($name) {
        $this->name = $name;
    }

    function exists () {
        $checkIfExists = $this->getPdo()->prepare('SELECT id FROM tags WHERE tag = ?');
        $checkIfExists->execute(array($this->name));
        if ($checkIfExists->rowCount() > 0) return true;
        else return false;
    }

    function getString () {
        switch ($this->name) {
            
            case 'hanami-sakura': return '桜';
            case 'hanami-ume': return '梅';
            case 'hanami-nanohana': return '菜の花';
            case 'hanami-ajisai': return '紫陽花';
            case 'hanami-himawari': return 'ひまわり';

            case 'nature-forest': return '森';
            case 'nature-kouyou': return '紅葉';
            case 'nature-ricefield': return '田んぼ';
            case 'nature-riceterraces': return '棚田';
            case 'nature-teafield': return '茶畑';
            
            case 'water-sea': return '海';
            case 'water-river': return '川';
            case 'water-lake': return '湖';
            case 'water-dam': return 'ダム';
            case 'water-waterfall': return '滝';

            case 'culture-culture': return '文化';
            case 'culture-history': return '歴史';
            case 'culture-machinami': return '街並み';
            case 'culture-shrines': return '寺・神社';
            case 'culture-hamlet': return '集落';

            case 'terrain-pass': return '峠';
            case 'terrain-mountains': return '山';
            case 'terrain-viewpoint': return '見晴らし';
            case 'terrain-tunnel': return 'トンネル';
            case 'terrain-bridge': return '橋';
            
            default: return ucfirst($this->name);
        }
    }

    function getEntries ($offset = 0, $limit = 20) {
        $getEntries = $this->getPdo()->prepare("SELECT object_id, object_type FROM tags WHERE tag = ? LIMIT " .$limit. " OFFSET " .$offset);
        $getEntries->execute(array($this->name));
        $results = $getEntries->fetchAll(PDO::FETCH_ASSOC);
        $entries = [];
        foreach($results as $result) {
            if ($result['object_type'] == 'scenery') array_push($entries, new Scenery($result['object_id']));
            if ($result['object_type'] == 'segment') array_push($entries, new Segment($result['object_id']));
        }
        usort($entries, function ($a, $b) {
            return ($b->popularity <=> $a->popularity);
        } );
        return $entries;
    }

    /**
     * Get total number of entries for this tag
     */
    function getEntriesNumber () {
        $getEntriesNumber = $this->getPdo()->prepare("SELECT id FROM tags WHERE tag = ?");
        $getEntriesNumber->execute(array($this->name));
        return $getEntriesNumber->rowCount();
    }

}