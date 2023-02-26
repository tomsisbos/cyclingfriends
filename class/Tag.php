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
            case 'hanami': return '花見';
            case 'kouyou': return '紅葉';
            case 'ajisai': return 'アジサイ';
            case 'culture': return '文化';
            case 'machinami': return '街並み';
            case 'shrines': return '宗教';
            case 'ricefields': return '田んぼ';
            case 'teafields': return '茶畑';
            case 'sea': return '海';
            case 'mountains': return '山';
            case 'forest': return '森';
            case 'rivers': return '川';
            case 'lakes': return '湖';
            default: return ucfirst($this->name);
        }
    }

    function getEntries ($offset = 0, $limit = 20) {
        $getEntries = $this->getPdo()->prepare("SELECT object_id, object_type FROM tags WHERE tag = ? LIMIT " .$offset. ", " .$limit);
        $getEntries->execute(array($this->name));
        $results = $getEntries->fetchAll(PDO::FETCH_ASSOC);
        $entries = [];
        foreach($results as $result) {
            if ($result['object_type'] == 'scenery') array_push($entries, new Mkpoint($result['object_id']));
            if ($result['object_type'] == 'segment') array_push($entries, new Segment($result['object_id']));
        }
        usort($entries, function ($a, $b) {
            return ($b->popularity <=> $a->popularity);
        } );
        return $entries;
    }

}