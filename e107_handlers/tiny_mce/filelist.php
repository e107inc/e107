<?
require_once("../../class2.php");
require_once(e_HANDLER."file_class.php");
if(!ADMIN){
exit;
}
// $fl = new e_file;

// $rejecthumb = array('$.','$..','/','CVS','thumbs.db','*._$',"thumb_", 'index');
// $imagelist = $fl->get_files(e_IMAGE."newspost_images/","",$rejecthumb);

$sql->db_Select("download");
		$c = 0;
		while ($row = $sql->db_Fetch()) {
			extract($row);
			$filelist['id'][$c] = $download_id;
			$filelist['url'][$c] = $download_url;
			$filelist['name'][$c] = $download_name;
			$c++;
		}

echo "var tinyMCELinkList = new Array(";
for ($i=0; $i<count($filelist); $i++) {
echo "['".$filelist['name'][$i]."', '".SITEURL."request.php?".$filelist['id'][$i]."']\n\n";
echo ($i != (count($filelist)-1)) ? "," : "";
};

echo ");";




?>