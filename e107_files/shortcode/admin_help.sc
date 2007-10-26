if (ADMIN)
{
  global $ns, $pref;
  ob_start();
  $help_text = "";
  if(strpos(e_SELF, e_ADMIN_ABS) !== FALSE)
  {
	if (!($handle=opendir(e_LANGUAGEDIR.e_LANGUAGE."/admin/help/"))) 
	{
      $handle=opendir(e_LANGUAGEDIR."English/admin/help/");
    }
    while(false !== ($file = readdir($handle))) 
	{
	  if ($file != "." && $file != ".." && $file != "CVS") 
	  {
		if (strpos(e_SELF, $file) !== FALSE) 
		{
		  if (is_readable(e_LANGUAGEDIR.e_LANGUAGE."/admin/help/".$file)) 
		  {
			include_once(e_LANGUAGEDIR.e_LANGUAGE."/admin/help/".$file);
		  } 
		  elseif (is_readable(e_LANGUAGEDIR."English/admin/help/".$file)) 
		  {
			include_once(e_LANGUAGEDIR."English/admin/help/".$file);
		  }
		}
	  }
	}
    closedir($handle);
  }
  $plugpath = getcwd()."/help.php"; // deprecated file. For backwards compat. only. 
  $eplugpath = getcwd()."/e_help.php";
  if(file_exists($plugpath))
  {
	@require_once($plugpath);
  }
  elseif(is_readable($eplugpath))
  {
  	@require_once($eplugpath);
  }
  $help_text = ob_get_contents();
  ob_end_clean();
  return $help_text;
}