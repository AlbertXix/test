<?php
class glb
{
    static public function set($name, $value)
    {
        $GLOBALS[$name] = $value;
    }

    static public function get($name)
    {
        return $GLOBALS[$name];
    }

}

$myglobalvar = 'Hello, World !';

function myfunction()
{
    $val = glb::get('myglobalvar');
    echo "$val<br />";
    glb::set('myglobalvar', 'hi, again :)');
    $val = glb::get('myglobalvar');
    echo "$val<br />";
}

myfunction();

$name = 'xlb';
echo $GLOBALS['name'];

?>
