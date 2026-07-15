<?php
namespace GameSpider\Util;

class GameUtils 
{
    public static function uniformTitle($title): string 
    {
        $title = trim($title);
        
        if (str_starts_with($title, "《"))
            $title = str_replace(['》——', '》 ——', '》—'], '》', $title);
        elseif (str_contains(strtolower($title), "——v"))
            $title = '《' . str_ireplace('——v', '》v', $title);
        elseif (str_contains(strtolower($title), "—build"))
            $title = '《' . str_ireplace('—build', '》build', $title);
        elseif (str_contains($title, "——"))
            $title = '《' . str_replace('——', '》', $title);

        if (!str_contains($title, "《") && !str_contains($title, "》"))
            $title = "《{$title}》";

        return $title;
    }
}