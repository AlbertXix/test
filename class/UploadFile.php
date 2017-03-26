<?php
/*
 * @(#)UploadFile.php
 * 
 * 可同时处理用户多个上传文件。效验文件有效性后存储至指定目录。
 * 可返回上传文件的相关有用信息供其它程序使用。（如文件名、类型、大小、保存路径）
 * 使用方法请见本类底部（UploadFile类使用注释）信息。
 *
 */
class UploadFile {
 
 var $user_post_file = array();  //用户上传的文件
 var $save_file_path;    //存放用户上传文件的路径
 var $max_file_size;     //文件最大尺寸
 var $last_error;     //记录最后一次出错信息
 //默认允许用户上传的文件类型
 var $allow_type = array('gif', 'jpg', 'png', 'zip', 'rar', 'txt', 'doc', 'pdf');
 var $final_file_path;  //最终保存的文件名
 
 var $save_info = array(); //返回一组有用信息，用于提示用户。
 
 /**
  * 构造函数，用与初始化相关信息，用户待上传文件、存储路径等
  *
  * @param Array $file  用户上传的文件
  * @param String $path  存储用户上传文件的路径
  * @param Integer $size 允许用户上传文件的大小(字节)
  * @param Array $type   此数组中存放允计用户上传的文件类型
  */
 function UploadFile($file, $path, $size = 2097152, $type = '') {
  $this->user_post_file = $file;
  $this->save_file_path = $path;
  $this->max_file_size = $size;  //如果用户不填写文件大小，则默认为2M.
  if ($type != '')
   $this->allow_type = $type;
 }
 
 /**
  * 存储用户上传文件，检验合法性通过后，存储至指定位置。
  * @access public
  * @return int    值为0时上传失败，非0表示上传成功的个数。
  */
 function upload() {
  
  for ($i = 0; $i < count($this->user_post_file['name']); $i++) {
   //如果当前文件上传功能，则执行下一步。
   if ($this->user_post_file['error'][$i] == 0) {
    //取当前文件名、临时文件名、大小、扩展名，后面将用到。
    $name = $this->user_post_file['name'][$i];
    $tmpname = $this->user_post_file['tmp_name'][$i];
    $size = $this->user_post_file['size'][$i];
    $mime_type = $this->user_post_file['type'][$i];
    $type = $this->getFileExt($this->user_post_file['name'][$i]);
    //检测当前上传文件大小是否合法。
    if (!$this->checkSize($size)) {
     $this->last_error = "The file size is too big. File name is: ".$name;
     $this->halt($this->last_error);
     continue;
    }
    //检测当前上传文件扩展名是否合法。
    if (!$this->checkType($type)) {
     $this->last_error = "Unallowable file type: .".$type." File name is: ".$name;
     $this->halt($this->last_error);
     continue;
    }
    //检测当前上传文件是否非法提交。
    if(!is_uploaded_file($tmpname)) {
     $this->last_error = "Invalid post file method. File name is: ".$name;
     $this->halt($this->last_error);
     continue;
    }
    //移动文件后，重命名文件用。
    $basename = $this->getBaseName($name, ".".$type);
    //移动后的文件名
    $saveas = $basename."-".time().".".$type;
    //组合新文件名再存到指定目录下，格式：存储路径 + 文件名 + 时间 + 扩展名
    $this->final_file_path = $this->save_file_path."/".$saveas;
    if(!move_uploaded_file($tmpname, $this->final_file_path)) {
     $this->last_error = $this->user_post_file['error'][$i];
     $this->halt($this->last_error);
     continue;
    }
    //存储当前文件的有关信息，以便其它程序调用。
    $this->save_info[] =  array("name" => $name, "type" => $type,
           "mime_type" => $mime_type,
                             "size" => $size, "saveas" => $saveas,
                             "path" => $this->final_file_path);
   }
  }
  return count($this->save_info); //返回上传成功的文件数目
 }
 
 /**
  * 返回一些有用的信息，以便用于其它地方。
  * @access public
  * @return Array 返回最终保存的路径
  */
 function getSaveInfo() {
  return $this->save_info;
 }
 
 /**
  * 检测用户提交文件大小是否合法
  * @param Integer $size 用户上传文件的大小
  * @access private
  * @return boolean 如果为true说明大小合法，反之不合法
  */
 function checkSize($size) {
  if ($size > $this->max_file_size) {
   return false;
  }
  else {
   return true;
  }
 }
 
 /**
  * 检测用户提交文件类型是否合法
  * @access private
  * @return boolean 如果为true说明类型合法，反之不合法
  */
 function checkType($extension) {
  foreach ($this->allow_type as $type) {
   if (strcasecmp($extension , $type) == 0)
    return true;
  }
  return false;
 }
 
 /**
  * 显示出错信息
  * @param  $msg    要显示的出错信息     
  * @access private
  */
 function halt($msg) {
  printf("<b><UploadFile Error:></b> %s <br>\n", $msg);
 }
 
 /**
  * 取文件扩展名
  * @param  String $filename 给定要取扩展名的文件
  * @access private
  * @return String      返回给定文件扩展名
  */
 function getFileExt($filename) {
  $stuff = pathinfo($filename);
  return $stuff['extension'];
 }
 /**
  * 取给定文件文件名，不包括扩展名。
  * eg: getBaseName("j:/hexuzhong.jpg"); //返回 hexuzhong
  * 
  * @param String $filename 给定要取文件名的文件
  * @access private
  * @return String 返回文件名
  */
 function getBaseName($filename, $type) {
  $basename = basename($filename, $type);
  return $basename;
 }
}
/******************** UploadFile类使用注释
//注意，上传组件name属性不管是一个还是多个都要使用数组形式，如：
<input type="file" name="user_upload_file[]"> 
<input type="file" name="user_upload_file[]"> 
 
//如果用户点击了上传按钮。
if ($_POST['action'] == "上传") {
 //设置允许用户上传的文件类型。
 $type = array('gif', 'jpg', 'png', 'zip', 'rar');
 //实例化上传类，第一个参数为用户上传的文件组、第二个参数为存储路径、
 //第三个参数为文件最大大小。如果不填则默认为2M
 //第四个参数为充许用户上传的类型数组。如果不填则默认为gif, jpg, png, zip, rar, txt, doc, pdf
 $upload = new UploadFile($_FILES['user_upload_file'], 'j:/tmp', 100000, $type);
 //上传用户文件，返回int值，为上传成功的文件个数。
 $num = $upload->upload();
 if ($num != 0) {
  echo "上传成功<br>";
  //取得文件的有关信息，文件名、类型、大小、路径。用print_r()打印出来。
  print_r($upload->getSaveInfo());
  //格式为：  Array
  //   (
  //    [0] => Array(
  //        [name] => example.txt
  //        [type] => txt
  //        [size] => 526
  //        [path] => j:/tmp/example-1108898806.txt
  //        )
  //   )
  echo $num."个文件上传成功";
 }
 else {
  echo "上传失败<br>";
 }
}
 
*/
?>