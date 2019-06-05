<?php
header('Content-type: application/xml; charset="GB2312"',true);
?>
<!--
@author Tobias Kluge, enarion.net
@version 0.2, 2008-03-05 17:40 PT
@status working
@update Aditya Naik, gongyi101459@163.com
@Licence: LGPL

editor:        gongyi101459@163.com

-->
<?php
$website = "http://www.ha223.com"; /* change this */
$page_root = "/";        /* change this */

/* maybe change this: */
$changefreq = "daily"; //"always", "hourly", "daily", "weekly", "monthly", "yearly" and "never".
$priority = 0.8;
/* this sets the last modification date of all pages to the current date */
$last_modification = date("Y-m-d\TH:i:s") . substr(date("O"),0,3) . ":" . substr(date("O"),3);

/* list of allowed directories */
$allow_dir[] = "html";

/* list of disallowed directories */
$disallow_dir[] = "admin";
$disallow_dir[] = "_notes";

/* list of disallowed file types */
$disallow_file[] = ".inc";
$disallow_file[] = ".old";
$disallow_file[] = ".save";
$disallow_file[] = ".txt";
$disallow_file[] = ".js";
$disallow_file[] = "~";
$disallow_file[] = ".LCK";
$disallow_file[] = ".zip";
$disallow_file[] = ".ZIP";
$disallow_file[] = ".CSV";
$disallow_file[] = ".csv";
$disallow_file[] = ".css";
$disallow_file[] = ".class";
$disallow_file[] = ".jar";
$disallow_file[] = ".mno";
$disallow_file[] = ".bak";
$disallow_file[] = ".lck";
$disallow_file[] = ".BAK";

/* simple compare function: equals */
function ar_contains($key, $array) {
  foreach ($array as $val) {
    if ($key == $val) {
        return true;
    }
  }
  return false;
}

/* better compare function: contains */
function fl_contains($key, $array) {
  foreach ($array as $val) {
    $pos = strpos($key, $val);
    if ($pos === FALSE) continue;
    return true;
  }

  return false;
}

/* this function changes a substring($old_offset) of each array element to $offset */
function changeOffset($array, $old_offset, $offset) {
  $res = array();
  foreach ($array as $val) {
    $res[] = str_replace($old_offset, $offset, $val);
  }
  return $res;
}

/* this walks recursivly through all directories starting at page_root and
   adds all files that fits the filter criterias */
// taken from Lasse Dalegaard, http://php.net/opendir
function getFiles($directory, $directory_orig = "", $directory_offset="") {
   global $disallow_dir, $disallow_file, $allow_dir;

   if ($directory_orig == "") $directory_orig = $directory;

   if($dir = opendir($directory)) {
       // Create an array for all files found
       $tmp = Array();

       // Add the files
       while($file = readdir($dir)) {
               // Make sure the file exists
               if($file != "." && $file != ".." && $file[0] != '.' ) {
               // If it's a directiry, list all files within it
               //echo "point1<br>";
             if(is_dir($directory . "/" . $file)) {
                  //echo "point2<br>";
                 $disallowed_abs = fl_contains($directory."/".$file, $disallow_dir); // handle directories with pathes
                $disallowed = ar_contains($file, $disallow_dir); // handle directories only without pathes
                $allowed_abs = fl_contains($directory."/".$file, $allow_dir);
                $allowed = ar_contains($file, $allow_dir);
                if ($disallowed || $disallowed_abs) continue;
                if ($allowed_abs || $allowed){
                   $tmp2 = changeOffset(getFiles($directory . "/" . $file, $directory_orig, $directory_offset), $directory_orig, $directory_offset);
                   if(is_array($tmp2)) {
                       $tmp = array_merge($tmp, $tmp2);
                   }
                }
             } else {  // files
                if (fl_contains($file, $disallow_file)) continue;
                    array_push($tmp, str_replace($directory_orig, $directory_offset, $directory."/".$file));
                   }
               }
       }

       // Finish off the function
       closedir($dir);
       return $tmp;
   }
}


$a = getFiles($page_root);


echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<urlset xmlns="http://www.google.com/schemas/sitemap/0.84">
<?
foreach ($a as $file) {
?>
   <url>
      <loc><? echo utf8_encode($website.$file); ?></loc>
      <lastmod><? echo utf8_encode(date("Y-m-d\TH:i:s", filectime($page_root.$file)). substr(date("O"),0,3) . ":" . substr(date("O"),3));?></lastmod>
      <changefreq><? echo utf8_encode($changefreq); ?></changefreq>
      <priority><? echo utf8_encode($priority); ?></priority>
   </url>
<?
}
?>
</urlset>

