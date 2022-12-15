<?php

class Tag {

    function __construct($name) {
        $this->name = $name;
    }

    function getString () {
        switch ($this->name) {
            case 'hanami': return '花見';
            case 'kouyou': return '紅葉';
            case 'ajisai': return 'アジサイ';
            case 'culture': return '文化';
            case 'machinami': return '街並み';
            case 'shrines': return '宗教';
            case 'teafields': return '茶畑';
            case 'sea': return '海';
            case 'mountains': return '山';
            case 'forest': return '森';
            case 'rivers': return '川';
            case 'lakes': return '湖';
            default: return ucfirst($this->name);
        }
    }

}