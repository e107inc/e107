<!--

if(parent.frames[0])
{
parent.location.href = self.location.href;
}

if(document.getElementById&&!document.all){ns6=1;}else{ns6=0;}
var agtbrw=navigator.userAgent.toLowerCase();
var operaaa=(agtbrw.indexOf('opera')!=-1);
var head="display:''";
var folder='';
function expandit(curobj){
if(ns6==1||operaaa==true){
	folder=curobj.nextSibling.nextSibling.style;
}else{
	folder=document.all[curobj.sourceIndex+1].style;
}

if (folder.display=="none"){folder.display="";}else{folder.display="none";}
}


function urljump(url){
	top.window.location = url; 
}

function dblclick(){
	window.scrollTo(0,0)
}
if (document.layers) {document.captureEvents(Event.ONDBLCLICK);}
document.ondblclick=dblclick;
function open_window(url) {
	pwindow = window.open(url,'Name', 'top=100,left=100,resizable=yes,width=600,height=400,scrollbars=yes,menubar=yes')
}

function preloadimages(nbrpic,pic){
     myimages=new Image();
     myimages.src=pic;
}

function textCounter(field,cntfield) {
	cntfield.value = field.value.length;
}

function openwindow() {
	opener = window.open("htmlarea/index.php", "popup","top=50,left=100,resizable=no,width=670,height=520,scrollbars=no,menubar=no");            
}
function setCheckboxes(the_form, do_check){
	var elts = (typeof(document.forms[the_form].elements['perms[]']) != 'undefined') ? document.forms[the_form].elements['perms[]'] : document.forms[the_form].elements['perms[]'];
    var elts_cnt  = (typeof(elts.length) != 'undefined') ? elts.length : 0;
    if(elts_cnt){
		for(var i = 0; i < elts_cnt; i++){
			elts[i].checked = do_check;
        }
	}else{
		elts.checked        = do_check;
    }
	return true;
}
image1 = new Image(); image1.src = "../e107_images/generic/e107.gif";
image2 = new Image(); image2.src = "../e107_images/generic/hme.png";
image3 = new Image(); image3.src = "../e107_images/generic/location.png";
image4 = new Image(); image4.src = "../e107_images/generic/rname.png";

//-->