/**

 __          _______     _____ _    _          __  __  _____ _____
 \ \        / /  __ \   / ____| |  | |   /\   |  \/  |/ ____|_   _|
  \ \  /\  / /| |__) | | (___ | |__| |  /  \  | \  / | (___   | |
   \ \/  \/ / |  ___/   \___ \|  __  | / /\ \ | |\/| |\___ \  | |
    \  /\  /  | |       ____) | |  | |/ ____ \| |  | |____) |_| |_
     \/  \/   |_|      |_____/|_|  |_/_/    \_\_|  |_|_____/|_____|


 * @version WP Shamsi aka افزونه شمسی ساز و فارسی ساز وردپرس  V2.1.0
 * @info https://wordpress.org/plugins/wp-shamsi/
 *
 */
jQuery(document).ready(function() {
	persian = {
		0: '۰',
		1: '۱',
		2: '۲',
		3: '۳',
		4: '۴',
		5: '۵',
		6: '۶',
		7: '۷',
		8: '۸',
		9: '۹'
	};
	elements = [ // Tags to skip
		"CODE", "HEAD", "INPUT", "OPTION", "PRE", "SCRIPT", "STYLE", "TEXTAREA", "TITLE"
	];
  var isShamsiInAdmin = [];
	if(isShamsiInAdmin.in_admin == 1) {
		var in_admin = true;
		var base = isShamsiInAdmin.base;
	}

	function wpsh_num(el) {
		if(el.nodeType == 3) {
			var parent = jQuery(el.parentElement).prop("tagName");
			var list = el.data.match(/[0-9]/g);
			if(list !== null && list.length !== 0) {
				for(var i = 0; i < list.length; i++)
					if(jQuery.inArray(parent, elements) === -1) {
						el.data = el.data.replace(list[i], persian[list[i]]);
					}
			}
		}
		for(var i = 0; i < el.childNodes.length; i++) {
			wpsh_num(el.childNodes[i]);
		}
	}
	if(in_admin) {
		if(base == 'edit.php') {
			wpsh_num(document.querySelector('#wpadminbar'));
			wpsh_num(document.querySelector('#adminmenu'));
			wpsh_num(document.querySelector('.date'));
			wpsh_num(document.querySelector('.tablenav-pages'));
			wpsh_num(document.querySelector('.subsubsub'));
		} else {
			wpsh_num(document.body);
		}
	} else {
		wpsh_num(document.body);
	}
});