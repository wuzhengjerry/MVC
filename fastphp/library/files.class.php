<?php if (!defined('JYSYSINC'))
    exit('JYSYS:No direct script access allowed');
/**
 * 文件操作类
 *
 * @version        $Id: files.class.php 3 2012-5-10 $
 * @package        Jysys.Libraries
 * @copyright      Copyright (c) 2012, Jy, Inc.
 * @link           http://www.joyql.com
 */
class files
{
    function __construct()
    {

    }
    /**
     * +----------------------------------------------------------
     * 文件下载
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param  string $file 文件
     *         string $showname 显示的名称
     * +----------------------------------------------------------
     * @return array/string
     * +----------------------------------------------------------
     */
    function download($file, $showname = '')
    {
        if (files::isfiles($file) == 1) {
            ext("Http");
            Http::download($file, $showname);
        }
    }
    /**
     * +----------------------------------------------------------
     * 文件上传
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * +----------------------------------------------------------
     * @return array/string
     * +----------------------------------------------------------
     */
    //保存
    function upload($customFile = null, $customType = '', $custom = 0)
    {

        $opac = ($custom == 1) ? "uploads" : request("opac");
        $opacarr = array("uploadsedit", "uploads");
        $entityid = request("entityid", 1);
        $viewsid = request("viewsid", 1);
        $field = request("fieldname");
        $tablename = request("tablename");
        $from = request("from");
        $wxuptype = request("wxuptype");
        if (in_array($opac, $opacarr)) {
            /**
             *********************************************
             * 变量设定
             *********************************************
             **/
            $usfilepath = date("Ymd");
            $uploadpath = $GLOBALS['cfg_file_dir'];
            $uploadpath = ($uploadpath == "") ? "uploads" : $uploadpath;
            $save_path = "storage/" . $uploadpath . '/' . $usfilepath . "/";
            $save_url = $save_path;
            $uploadpath = $save_path;
            $uploadtypes = ($customType != "") ? $customType : request("uploadtypes");
            $uploadtypes = ($uploadtypes != "") ? $uploadtypes : "img";
            if ($uploadtypes == "all") {
                $uploadtypes = "files";
            }
            $fileElementName = request("fileid");
            $fieldname = request("eid");
            $fileprefix = substr($fileElementName, strlen($fileElementName) - 1, 1);
            $curfilename = request("curfilename");
            ;
            $upresult = false;
            /**
             *********************************************
             * 参数设定
             *********************************************
             **/
            //最大上传文件大小
            $filemaxsize = 30720;
            $MAX_SIZE = 102400000;
            switch ($uploadtypes) {
                case "img":
                    $uploadpath = $uploadpath;
                    //设置Mine类型
                    $FILE_MIMES = array(
                        'image/jpeg',
                        'image/jpg',
                        'image/gif',
                        'image/png',
                        'application/msword');
                    //设置允许上传的文件类型，按照格式添加
                    $FILE_POSTFIX = array(
                        '.jpeg',
                        '.jpg',
                        '.png',
                        '.gif');
                    break;
                case "flash":
                    $uploadpath = $uploadpath . "flash/";
                    //设置Mine类型
                    $FILE_MIMES = array('image/swf', 'application/msword');
                    //设置允许上传的文件类型，按照格式添加
                    $FILE_POSTFIX = array('.swf');
                    break;
                case "files":
                    $uploadpath = $uploadpath;
                    //设置Mine类型
                    $FILE_MIMES = array(
                        'image/pdf',
                        'image/doc',
                        'image/docx',
                        'image/xls',
                        'image/cvs',
                        'image/txt',
                        'image/ppt',
                        'image/jpeg',
                        'image/jpg',
                        'image/gif',
                        'image/png',
                        'image/rar',
                        'image/zip',
                        'application/kset',
                        'application/msword',
                    	'application/vnd.android.package-archive'
                    );
                    //设置允许上传的文件类型，按照格式添加
                    $FILE_POSTFIX = array(
                        '.pdf',
                        '.doc',
                        '.docx',
                        '.xls',
                        '.cvs',
                        '.txt',
                        '.ppt',
                        '.jpeg',
                        '.jpg',
                        '.png',
                        '.gif',
                        '.rar',
                        '.zip',
                    	'.apk'
                    );
                    break;
                case "excel":
                    $uploadpath = $uploadpath;
                    //设置Mine类型
                    $FILE_MIMES = array(
                        'image/xls',
                        'application/msword',
                        'application/kset');
                    //设置允许上传的文件类型，按照格式添加
                    $FILE_POSTFIX = array('.xls');
                    break;
                case "mv":
                    $uploadpath = $uploadpath . "mv/";
                    //设置Mine类型
                    $FILE_MIMES = array(
                        'image/flv',
                        'image/rm',
                        'image/avi',
                        'image/mpeg',
                        'image/wmv',
                        'application/msword'); //AVI,MPEG,MOV,WMV
                    //设置允许上传的文件类型，按照格式添加
                    $FILE_POSTFIX = array(
                        '.flv',
                        '.rm',
                        '.avi',
                        '.mpeg',
                        '.wmv');
                    break;
                default:
                    $uploadpath = $uploadpath;
                    //设置Mine类型
                    $FILE_MIMES = array(
                        'image/jpeg',
                        'image/jpg',
                        'image/gif',
                        'image/png',
                        'application/msword',
                        'application/kset');
                    //设置允许上传的文件类型，按照格式添加
                    $FILE_POSTFIX = array(
                        '.jpeg',
                        '.jpg',
                        '.png',
                        '.gif');
                    break;
            }
            //是否允许删除以上传的文件，允许:yes; 不允许:no;
            $DELETE_ENABLE = 'yes';
            /**
             *********************************************
             * 创建上传目录
             *********************************************
             **/
            if (!is_dir($uploadpath)) {
                if (!mkdir($uploadpath))
                    die('文件没有创建成功！！');
                if (!chmod($uploadpath, 0777))
                    die("改变权限失败.");
            }
            $error = "";
            $msg = "";
            if (isset($customFile)) {
                $FILESRES = $customFile;
            } else {
                $GLOBALS['request'] = isset($GLOBALS['request']) ? $GLOBALS['request'] : new
                    request;
                $FILES = $GLOBALS['request']->GetFilesArr();
                $FILESRES = $FILES[$fileElementName];
            }

            $resArr = array();
            $resArr['msg'] = "";
            $resArr['flag'] = 0;
            $resArr['html'] = "";
            $resArr['fileurl'] = "";
            $resArr['filename'] = "";
            if (!empty($FILESRES['error'])) {
                switch ($FILESRES['error']) {

                    case '1':
                        $error = '上传的文件超过了最大限制'; //The uploaded file exceeds the upload_max_filesize directive in php.ini
                        break;
                    case '2':
                        $error = '上传的文件超过了最大限制'; //The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form
                        break;
                    case '3':
                        $error = '上传的文件只有部分被上传'; //The uploaded file was only partially uploaded
                        break;
                    case '4':
                        $error = '没有文件被上传'; //No file was uploaded.
                        break;

                    case '6':
                        $error = '缺少一个临时文件夹'; //Missing a temporary folder
                        break;
                    case '7':
                        $error = '无法写入文件到磁盘'; //Failed to write file to disk
                        break;
                    case '8':
                        $error = '上传失败'; //File upload stopped by extension
                        break;
                    case '999':
                    default:
                        $error = '没有文件被上传';
                }
            } elseif (empty($FILESRES['tmp_name']) || $FILESRES['tmp_name'] == 'none') {
                $error = '没有文件被上传..';
                $resArr['msg'] = $error;
                if ($custom == 0) {
                    error($resArr, 1);
                }

            } else {
                $msg .= " File Name: " . $FILESRES['name'] . ", ";
                $dir = "userfiles/";
                //
                //filepath
                $randtime = date("YmdHis") . strings::randString(6);
                $temp_name = $FILESRES['tmp_name'];
                $file_name = $FILESRES['name'];
                $f_types = substr($file_name, strrpos($file_name, "."));
                $file_name = $randtime;
                $file_name = $randtime . $f_types;
                $file_name = str_replace("\\", "", $file_name);
                $file_name = str_replace("'", "", $file_name);
                $file_path = $uploadpath . $file_name;
                $savefilename = $file_name;
                $newfilename = $dir . $FILESRES['name'];
                $file_name = $FILESRES['name']; //上传文件的名称
                $filetitle = $file_name;
                $file_type = $FILESRES['type']; //上传文件的类型
                $file_postfixs = substr($file_name, strrpos($file_name, ".")); //上传文件的后缀


                $file_size = $FILESRES['size'];
                //文件大小检查
                $filesele = $FILESRES;
                $weixin_media_id = $wxApiFilePath = "";
                if ($from == "weixin") {
                    if ($filesele['size'] > 256 * 1024) {
                        $error = '文件不能大于256KB';
                        $msg = "文件太大";
                        $resArr['msg'] = $error;
                        if ($custom == 0) {
                            error($resArr, 1);
                        }
                    }
                    if ($wxuptype == "thumb" && $filesele['size'] > 64 * 1024) {
                        $error = '缩略图大小不能超过64k';
                        $msg = "文件太大";
                        $resArr['msg'] = $error;
                        if ($custom == 0) {
                            error($resArr, 1);
                        }
                    }
                }
                if ($filesele['size'] > $MAX_SIZE) {
                    $error = '文件太大';
                    $msg = "文件太大";
                    $resArr['msg'] = $error;
                    if ($custom == 0) {
                        error($resArr, 1);
                    }
                } elseif (!in_array(strtolower($file_postfixs), $FILE_POSTFIX)) { //!in_array($file_type, $FILE_MIMES) &&
                    $error = "文件类型不符合：$file_postfixs [$uploadtypes]";
                    $msg = "文件类型不符合：$file_postfixs [$uploadtypes]";
                    $resArr['msg'] = $error;
                    if ($custom == 0) {
                        error($resArr, 1);
                    }
                } else {
                    $upresult = $GLOBALS['request']->MoveUploadFile($fileElementName, $file_path, $filetype =
                        ''); //move_uploaded_file($FILESRES['tmp_name'], $file_path);
                    if ($from == "weixin") {
                        loadMod("weixinapi.weixinapi");
                        $weixinapi = new weixinapi();
                        $FUNC = "uploadFile";
                        $fileExt = realpath($save_path . $savefilename);
                        $res = $weixinapi->$FUNC($fileExt, $savefilename, $wxuptype);
                        if (isset($res['media_id'])) {
                            $weixin_media_id = $res['media_id'];
                        }
                        if (isset($res['thumb_media_id'])) {
                            $weixin_media_id = $res['thumb_media_id'];
                        }
                        if (isset($res['wxApiFilePath'])) {
                            $wxApiFilePath = $res['wxApiFilePath'];
                        }
                        logs("wxmd", $wxApiFilePath);
                    }
                }
                //
                if ($upresult) {
                    $filesize = self::getfilesize($save_path . $savefilename) / 1024;
                    $filesize = intval($filesize);
                    if ($filesize > $filemaxsize) {
                        $error = "文件太大";
                        if ($this->isfiles($save_path . $savefilename) == 1) {
                            $this->deletefiles($save_path . $savefilename);
                        }
                        $resArr['msg'] = $error;
                        if ($custom == 0) {
                            error($resArr, 1);
                        }
                    }
                    $fileUrlName = $save_path . $savefilename;
                    $fileurl = explode(".", $fileUrlName);
                    $ftype = $fileurl[(count($fileurl) - 1)];
                    //保存记录
                    $datas['table'] = "__dbprefix__entity_file";
                    $datas['fields'] = array(
                        "entityid" => $entityid,
                        "fileurl" => $fileUrlName,
                        "tablename" => $tablename,
                        "filesize" => files::getfilesize($fileUrlName),
                        "field" => $field,
                        "ip" => get_client_ip(),
                        "ftype" => $ftype,
                        "createby" => sessions::get("jysys_userid"),
                        "ctime" => date("Y-m-d H:i:s"));
                    sdb::insert($datas);
                    //视图导入记录
                    if ($viewsid > 0) {
                        $opdatas = array();
                        $opdatas['userid'] = sessions::get("jysys_userid");
                        $opdatas['m'] = request("m");
                        $opdatas['c'] = request("c");
                        $opdatas['ac'] = request("ac");
                        $opdatas['op'] = "导入";
                        $opdatas['viewsid'] = $viewsid;
                        $opdatas['logtype'] = "IMPORT";
                        $opdatas['fileurl'] = $fileUrlName;
                        uslog($opdatas);
                    }
                    ///end//////////////
                    $error = '<input name="filesnameup' . $fieldname . '" value="' . ($weixin_media_id !=
                        "" ? $weixin_media_id . "|" . $save_path . $savefilename : $save_path . $savefilename) .
                        '" type="hidden"id="filesnameup' . $fieldname . '" />';
                    $resArr['msg'] = "上传成功";
                    $resArr['fileurl'] = ($weixin_media_id != "" ? $weixin_media_id . "|" . ($wxApiFilePath !=
                        "" ? $wxApiFilePath : $save_path . $savefilename) : $save_path . $savefilename);
                     $file_h = substr($resArr['fileurl'], strrpos($resArr['fileurl'], "."));
                    $image_type = substr($file_h,1);
                     if(in_array($file_h,$FILE_POSTFIX)){
                         ext("image");
                         $thumbfile = $save_path  . $savefilename;
                         $imagename = image::thumb($resArr['fileurl'],$thumbfile,$image_type,552,456);

                     }
                    $resArr['filename'] = $savefilename;
                    $resArr['flag'] = 1;
                    if ($custom == 0) {
                        error($resArr, 1);
                    }
                    if ($custom == 1) {
                        $error = ($weixin_media_id != "" ? $weixin_media_id . "|" . ($wxApiFilePath !=
                            "" ? $wxApiFilePath : $save_path . $savefilename) : $save_path . $savefilename);
                    }
                } else {
                    $error = "failed";
                    $msg = "move file failed";

                }
                $msg .= " File Size: " . @filesize($FILESRES['tmp_name']);
                //for security reason, we force to remove all uploaded file
                //@unlink($FILESRES);
            }
            /*<input name="filesnameup' . $fieldname . '" value="' . $curfilename .
            '" type="hidden" id="filesnameup' . $fieldname . '" />*/
            if ($custom == 1) {
                return $error;
            } else {
                $resArr['msg'] = $error;
                echo encode_json($resArr);
                exit;
            }

        }
    }
    /**
     * +----------------------------------------------------------
     * 获取目录文件
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param string $dir 目录
     * +----------------------------------------------------------
     * @return array
     * +----------------------------------------------------------
     */
    function list_file($dir, $pattern = "")
    {
        $arr = array();
        $dir_handle = opendir($dir);
        if ($dir_handle) {
            // 这里必须严格比较，因为返回的文件名可能是“0”
            while (($file = readdir($dir_handle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $tmp = realpath($dir . '/' . $file);
                if (is_dir($tmp)) {
                    $retArr = list_file($tmp, $pattern);
                    if (!emptyempty($retArr)) {
                        $arr[] = $retArr;
                    }
                } else {
                    if ($pattern === "" || preg_match($pattern, $tmp)) {
                        $arr[] = $tmp;
                    }
                }
            }
            closedir($dir_handle);
        }
        return $arr;
    }
    /**
     * +----------------------------------------------------------
     * 删除文件
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param string/array $filepath 文件路径
     * +----------------------------------------------------------
     * @return Bool
     * +----------------------------------------------------------
     */
    function deletefiles($filePath)
    {
        if (is_array($filePath)) {
            foreach ($filePath as $val) {
                if (file_exists_case($val)) {
                    @unlink($val);
                }
            }
        } else {
            if (file_exists_case($filePath)) {
                @unlink($filePath);
            }
        }
    }
    /**
     * +----------------------------------------------------------
     * 读取文件内容
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param string $file 文件路径
     * +----------------------------------------------------------
     * @return string
     * +----------------------------------------------------------
     */
    function getfile($file)
    {
        $data = "";
        if (file_exists($file)) {
            if ($fp = @fopen($file, 'rb')) {
                if (PHP_VERSION >= '4.3.0' && function_exists('file_get_contents')) {
                    $data = file_get_contents($file);
                } else {
                    flock($fp, LOCK_EX);
                    $data = fread($fp, filesize($file));
                    flock($fp, LOCK_UN);
                    fclose($fp);

                }
            }
        }

        return $data;
    }
    /**
     * +----------------------------------------------------------
     * 创建文件
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param string $n  创建文件
     *        string $d  写入的内容
     * +----------------------------------------------------------
     * @return bool
     * +----------------------------------------------------------
     */
    function create_files($n, $d, $type = 'w')
    {
        $d = u2utf8($d); //转换为UTF-8编码
        $f = @fopen($n, $type);
        if (!$f) {
            return false;
        } else {
            fwrite($f, $d);
            fclose($f);
            return true;
        }
    }
    /**
     * +----------------------------------------------------------
     * 文件是否存在
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param string $filepath 文件路径
     * +----------------------------------------------------------
     * @return Bool
     * +----------------------------------------------------------
     */
    function isfiles($filepath)
    {
        if (file_exists($filepath)) {
            return 1;
        } else {
            return 0;
        }
    }
    /**
     * +----------------------------------------------------------
     * 创建目录
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param string $dir 目录
     * @param string $mode 权限
     * +----------------------------------------------------------
     * @return Bool
     * +----------------------------------------------------------
     */
    /**
     * +----------------------------------------------------------
     * 创建目录
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param string $dir 目录
     * @param string $mode 权限
     * +----------------------------------------------------------
     * @return Bool
     * +----------------------------------------------------------
     */
    public static function mkdirs($dir, $mode = 0777)
    {
        if (!is_dir($dir)) {
            $dir=str_replace('\\','/',$dir);
            $dir=str_replace('//','/',$dir);
            if (PHP_OS== "Linux")   $dir=str_replace($_SERVER["DOCUMENT_ROOT"]."/",'',$dir);
            if(substr($dir,0,1)=="/"){
                $dir=substr($dir,1);
            }elseif(substr($dir,-1)=="/"){
                $dir=substr($dir,0,strlen($dir)-1);
            }
            $dirArr=explode("/",$dir);
            foreach($dirArr as $ks=>$vs){
                if($ks>0){
                    $temp=array();
                    for($i=0;$i<=$ks;$i++){
                        $temp[]=$dirArr[$i];
                    }
                    $tempDir=implode("/",$temp);
                    if(!is_dir($tempDir)) {
                        mkdir($tempDir, $mode);
                    }
                }else{
                    if(!is_dir($vs."/")) {
                        mkdir($vs."/", $mode);
                    }

                }
            }
            //mkdirs(dirname($dir), $mode);
            return true;
        }
        return true;

    }
    /**
     * +----------------------------------------------------------
     * 删除目录
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param string $dir 目录
     * +----------------------------------------------------------
     * @return Bool
     * +----------------------------------------------------------
     */
    function rmdirs($dir)
    {
        $dir = realpath($dir);
        if ($dir == '' || $dir == '/' || (strlen($dir) == 3 && substr($dir, 1) == ':\\')) {
            // 禁止删除根目录
            return false;
        }

        // 遍历目录，删除所有文件和子目录
        if (false !== ($dh = opendir($dir))) {
            while (false !== ($file = readdir($dh))) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($path)) {
                    if (!files::rmdirs($path)) {
                        return false;
                    }
                } else {
                    unlink($path);
                }
            }
            closedir($dh);
            rmdir($dir);
            return true;
        } else {
            return false;
        }
    }
    /**
     * +----------------------------------------------------------
     * 文件大小单位转换
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param float $size 大小
     * +----------------------------------------------------------
     * @return String
     * +----------------------------------------------------------
     */
    function getRealSize($size)
    {
        $kb = 1024; // Kilobyte
        $mb = 1024 * $kb; // Megabyte
        $gb = 1024 * $mb; // Gigabyte
        $tb = 1024 * $gb; // Terabyte

        if ($size < $kb) {
            return $size . " B";
        } else
            if ($size < $mb) {
                return round($size / $kb, 2) . " KB";
            } else
                if ($size < $gb) {
                    return round($size / $mb, 2) . " MB";
                } else
                    if ($size < $tb) {
                        return round($size / $gb, 2) . " GB";
                    } else {
                        return round($size / $tb, 2) . " TB";
                    }
    }
    /**
     * +----------------------------------------------------------
     * 返回文件类型和使用归类
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param string $fileurl 文件
     * +----------------------------------------------------------
     * @return array
     * +----------------------------------------------------------
     */
    function getFileType($fileurl)
    {
        $res = array('type' => '', 'useType' => '');
        if (files::isfiles($fileurl) == 1) {
            $fileType = glb("vcrmCfg", "fileType");
            $ext = strtolower(substr($fileurl, strrpos($fileurl, ".")));
            $res['type'] = $ext;
            $useType = '';
            if (is_array($fileType)) {
                foreach ($fileType as $k => $v) {
                    if (in_array($ext, $v)) {
                        $useType = $k;
                    }
                }
            }
            $res['useType'] = $useType;
        }
        return $res;
    }
    /**
     * +----------------------------------------------------------
     * 获取文件大小
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param string $filename 文件
     * +----------------------------------------------------------
     * @return float
     * +----------------------------------------------------------
     */
    function getfilesize($filename)
    {
        if (file_exists($filename)) {
            return filesize($filename);
        } else {
            return 0;
        }
    }

} //End Class
