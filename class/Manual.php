<?php

class Manual {
    
    public static function title ($h, $title) { // h = number
        echo '<h' .$h. '>' .$title. '</h' .$h. '>';
    }

    public static function path ($path) {
        echo '<a class="m-path" href="' .$path. '">' .$path. '</a>';
    }

    public static function intro ($paragraphs) { // paragraphs = array
        echo '<div class="m-intro">';
        foreach ($paragraphs as $class => $text) {
            if (is_numeric($class)) echo '<p>' .$text. '</p>';
            else echo '<div class="m-' .$class. '">' .$text. '</div>';
        }
        echo '</div>';
    }

    public static function text ($paragraphs) { // paragraphs = array
        foreach ($paragraphs as $class => $text) {
            if (is_numeric($class)) echo '<p>' .$text. '</p>';
            else echo '<div class="m-' .$class. '">' .$text. '</div>';
        };
    }
    
    public static function point ($point) {
        echo '<div class="m-point">' .$point. '</div>';
    }

}