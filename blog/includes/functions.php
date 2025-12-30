<?php
function sanitize($str){ return htmlspecialchars($str); }
function redirect($url){ header("Location:$url"); exit; }
function slugify($text){
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8','us-ascii//TRANSLIT',$text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}
