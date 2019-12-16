<?php
    if ($forbidden_lists) {
        foreach($forbidden_lists as $word) {
            if (strpos($text, $word) !== false) {
                delMsg($telegram, 'text');
                return true;
            }
        }
    }
?>