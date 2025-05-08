/* To avoid CSS expressions while still supporting IE 7 and IE 6, use this script */
/* The script tag referencing this file must be placed before the ending body tag. */

/* Use conditional comments in order to target IE 7 and older:
	<!--[if lt IE 8]><!-->
	<script src="ie7/ie7.js"></script>
	<!--<![endif]-->
*/

(function() {
	function addIcon(el, entity) {
		var html = el.innerHTML;
		el.innerHTML = '<span style="font-family: \'social\'">' + entity + '</span>' + html;
	}
	var icons = {
		'e-social-behance': '&#xe926;',
		'e-social-telegram': '&#xe923;',
		'e-social-snapchat': '&#xe911;',
		'e-social-map': '&#xf041;',
		'e-social-sms': '&#xf075;',
		'e-social-facebook-square': '&#xf082;',
		'e-social-github-square': '&#xf092;',
		'e-social-phone': '&#xf095;',
		'e-social-phone-square': '&#xf098;',
		'e-social-facebook': '&#xf09a;',
		'e-social-github': '&#xf09b;',
		'e-social-mobile': '&#xf10b;',
		'e-social-tumblr': '&#xf173;',
		'e-social-twitch': '&#xf1e8;',
		'e-social-whatsapp': '&#xf232;',
		'e-social-map-o': '&#xf278;',
		'e-social-meetup': '&#xf2e0;',
		'e-social-steam': '&#xe902;',
		'e-social-vimeo': '&#xe905;',
		'e-social-skype': '&#xe906;',
		'e-social-wordpress': '&#xe907;',
		'e-social-yelp': '&#xe908;',
		'e-social-dropbox': '&#xe909;',
		'e-social-vk': '&#xe90a;',
		'e-social-gplus': '&#xe90b;',
		'e-social-google': '&#xe90c;',
		'e-social-apple': '&#xe90d;',
		'e-social-digg': '&#xe90e;',
		'e-social-stumbleupon': '&#xe90f;',
		'e-social-flickr': '&#xe910;',
		'e-social-youtube-play': '&#xe912;',
		'e-social-youtube': '&#xe913;',
		'e-social-pocketpocket': '&#xe914;',
		'e-social-rss': '&#xe915;',
		'e-social-pinterest': '&#xe916;',
		'e-social-instagram': '&#xe917;',
		'e-social-linkedin': '&#xe918;',
		'e-social-export': '&#xe919;',
		'e-social-share': '&#xe91a;',
		'e-social-foursquare': '&#xe91b;',
		'e-social-thumbs-up': '&#xe91c;',
		'e-social-thumbs-up-solid': '&#xe91d;',
		'e-social-mail': '&#xe91e;',
		'e-social-mail-alt': '&#xe91f;',
		'e-social-reddit': '&#xe925;',
		'e-social-spotify': '&#xe920;',
		'e-social-soundcloud-alt': '&#xe901;',
		'e-social-soundcloud': '&#xe904;',
		'e-social-pushpin': '&#xe946;',
		'e-social-vine': '&#xea97;',
		'e-social-dribbble': '&#xeaa7;',
		'e-social-blogger': '&#xeab7;',
		'e-social-android': '&#xeac0;',
		'e-social-linkedin-rect': '&#xeac9;',
		'e-social-delicious': '&#xeacd;',
		'e-social-flattr': '&#xead5;',
		'e-social-discord': '&#xe900;',
		'e-social-instapaper': '&#xe903;',
		'e-social-twitter': '&#xe924;',
		'e-social-squarespace': '&#xe921;',
		'e-social-tiktok': '&#xe922;',
		'0': 0
		},
		els = document.getElementsByTagName('*'),
		i, c, el;
	for (i = 0; ; i += 1) {
		el = els[i];
		if(!el) {
			break;
		}
		c = el.className;
		c = c.match(/e-social-[^\s'"]+/);
		if (c && icons[c[0]]) {
			addIcon(el, icons[c[0]]);
		}
	}
}());
