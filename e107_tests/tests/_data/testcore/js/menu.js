sfHover = function() {
	var sfEls = document.getElementById("nav").getElementsByTagName("LI");
	for (var i=0; i<sfEls.length; i++) {
		sfEls[i].onmouseover=function() {
			if(this.className != 'fs-linkSep') {
				if(this.className == 'sub') {
					this.className+="-sfhover";
					
				} else {
					this.className+=" sfhover";
				}
			}
		}
		sfEls[i].onmouseout=function() {
			if(this.className == 'sub-sfhover') {
				this.className=this.className.replace(new RegExp("-sfhover\\b"), "");
				
			} else {
				this.className=this.className.replace(new RegExp(" sfhover\\b"), "");
			}
		}
	}
}
if (window.attachEvent) window.attachEvent("onload", sfHover);

