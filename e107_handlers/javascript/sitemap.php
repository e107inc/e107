<?php
echo "
	var ejs_func_todo='view';\n
	function ejs_expandall(ejs_cl_name,ejs_func_todo,ejs_cl_name2){\n
	tot_object = document.getElementsByTagName('div').length;\n
	for(i=0;i<tot_object;i++){\n
	p = document.getElementsByTagName('div').item(i);\n
	 
	if (p.className==ejs_cl_name || p.className==ejs_cl_name2){\n
	if (ejs_func_todo=='view'){\n
	if (p!=0)p.style.display='block';\n
	}else if(ejs_func_todo=='hide'){\n
	if (p!=0)p.style.display='none';\n
	}\n
	}
	}\n
	if (ejs_func_todo=='hide'){ejs_func_todo='view';}else{ejs_func_todo='hide';}\n
	ejs_expandpics('icoexp',ejs_func_todo2,'".SM_ICO_URL."','".SM_ICO_URL2."');\n
	return ejs_func_todo;\n
	}\n\n
	 
	var ejs_func_todo2='hide';\n
	function ejs_expandpics(ejs_cl_name,ejs_func_todo2,src1,src2){\n
	tot_object2 = document.getElementsByTagName('img').length;\n\n
	for(i=0;i<tot_object2;i++){\n
	im = document.getElementsByTagName('img').item(i);\n
	if (im.className==ejs_cl_name){\n
	if (ejs_func_todo2=='view'){\n
	im.src=src1;\n
	}else if(ejs_func_todo2=='hide'){\n
	im.src=src2;\n
	}\n
	}\n
	}\n
	if (ejs_func_todo2=='hide'){ejs_func_todo2='view';}else{ejs_func_todo2='hide';}\n
	return ejs_func_todo2;\n
	}\n
	";
	
?>