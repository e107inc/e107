var fadeTo = "ff0000";
var fiBy = 6;
var foBy = 6;
var speed = 20;
var ignoreClass = "ignore";
var opera, ie, dom, x = 0, oc, fader, ocs = new Array();

if (navigator.userAgent.indexOf("Opera") != -1) opera = true
else if (document.all && !opera) ie = true
else if (!document.all && document.getElementById) dom = true;

function convertRGB(z)
	{
		var newfcS = "", splitter = "";
		splitter = z.split(",");
		splitter[0] = parseInt(splitter[0].substring(4, splitter[0].length));
		splitter[1] = parseInt(splitter[1]);
		splitter[2] = parseInt(splitter[2].substring(0, splitter[2].length-1));
		for (var q = 0; q < 3; q++)
			{
				splitter[q] = splitter[q].toString(16);
				if (splitter[q].length == 1) splitter[q] = "0" + splitter[q];
				newfcS += splitter[q];
			}
		return newfcS;
	}

function currentColour(index)
	{
		var temp, cc;
		if (opera) cc = document.links[index].style.color
		else if (ie) cc = document.links[index].currentStyle.color
		else if (dom) cc = document.defaultView.getComputedStyle(document.links[index], '').getPropertyValue("color");
		if (cc.length == 4 && cc.substring(0, 1) == "#")
			{
				temp = "";
				for (var a = 0; a < 3; a++)
					temp += cc.substring(a+1, a+2) + cc.substring(a+1, a+2);
				cc = temp;
			}
		else if (cc.indexOf("rgb") != -1) cc = convertRGB(cc)
		else if (cc.length == 7) cc = cc.substring(1, 7)
		else cc = fadeTo;
		return cc;
	}


function convert2Dec(hex)
	{	
		var rgb = new Array();
		for (var u = 0; u < 3; u++)
			rgb[u] = parseInt(hex.substring(u*2, u*2+2), 16);
		return rgb;
	}

function newRGB(f, n, d)
	{
		var change;
		if (d == 1) change = fiBy
		else change = foBy;
		for (var g = 0; g < 3; g++)
			{
				if (n[g] > f[g] && n[g] - change >= 0) n[g] -= change;
				if (n[g] < f[g] && n[g] + change <= 255) n[g] += change;
			}
		return n;
	}

function fade(index, d)
	{
		var fc, nc, temp = new Array(), finished = false;
		nc = convert2Dec(currentColour(index));
		if (d == 1) fc = convert2Dec(fadeTo)
		else fc = convert2Dec(ocs[x]);
		temp = convert2Dec(currentColour(index));
		nc = newRGB(fc, nc, d);
		if ((nc[0] == temp[0]) && (nc[1] == temp[1]) && (nc[2] == temp[2]))
			finished = true;
		if (!finished) document.links[x].style.color = "rgb(" + nc[0] + "," + nc[1] + "," + nc[2] + ")"
		else clearInterval(fader);
	}

function findLink(over)
	{
		if (document.layers) return;
		if (fader)
			{
				clearInterval(fader);
				document.links[x].style.color = "#" + ocs[x];
			}
		if (over && !this.id) this.id = over;
		x = 0;
		while (!(this.id == document.links[x].id) && (x < document.links.length))
			x++;
		if (this.id == document.links[x].id)
			{
				oc = currentColour(x);
				fader = setInterval("fade(" + x  + ", 1)", speed);
			}
	}

function clearFade()
	{
		if (document.layers) return;
		if (fader) clearInterval(fader);
		fader = setInterval("fade(" + x + ", 0)", speed);
	}

function init()
	{
		for (var i = 0; i < document.links.length; i++)
			{
				ocs[i] = currentColour(i);
				var currentOver = document.links[i].onmouseover;
				var currentOut = document.links[i].onmouseout;
				var ignoreIt = document.links[i].className == ignoreClass;
				if (!ignoreIt) document.links[i].id = "link" + i;
				if (!currentOver && !currentOut && !ignoreIt)
					{
						document.links[i].onmouseover = findLink;
						document.links[i].onmouseout = clearFade;
					}
			}		
}

if (opera || ie || dom) window.onload = init;