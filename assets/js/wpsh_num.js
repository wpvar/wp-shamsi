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

  function wpsh_num(el) {
    if (el.nodeType == 3) {
      var list = el.data.match(/[0-9]/g);
      if (list != null && list.length != 0) {
        for (var i = 0; i < list.length; i++)
          el.data = el.data.replace(list[i], persian[list[i]]);
      }
    }
    for (var i = 0; i < el.childNodes.length; i++) {
      wpsh_num(el.childNodes[i]);
    }
  }
  wpsh_num(document.body);
});