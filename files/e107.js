<!--

var ns6=document.getElementById&&!document.all?1:0
var head="display:''"
var folder=''
function expandit(curobj){
folder=ns6?curobj.nextSibling.nextSibling.style:document.all[curobj.sourceIndex+1].style
if (folder.display=="none")
folder.display=""
else
folder.display="none"
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

//-->