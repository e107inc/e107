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
function openwindow(url) {
	pwindow = window.open(url,'Name', 'top=100,left=100,resizable=yes,width=600,height=400,scrollbars=yes,menubar=yes')
}

function preloadimages(nbrpic,pic){
     myimages=new Image();
     myimages.src=pic;
}

//-->