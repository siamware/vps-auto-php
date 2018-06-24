window.onload = function () {
  // Prepare vuejs plugin
  // Prepare engine
  var app = new PHUMIN_STUDIO_HOSTING();

  app.init();
  if(window.location.hostname == "localhost") {
    window.app = app;
  }
}

function ___ga() {
  'use strict';

  /* globals ga */
  function appendScript() {
    var script = document.createElement('script');
    script.async = true;
    script.src = 'https://www.google-analytics.com/analytics.js';
    document.body.appendChild(script);
  }

  function init(id) {
    if (!window.ga) {
      appendScript();
      window.ga = window.ga || function () {
        (ga.q = ga.q || []).push(arguments);
      };
      ga.l = Number(new Date());
      ga('create', id, 'auto');
    }
  }

  function collect(url, id) {
    init(id);
    ga('set', 'page', '/panel' + url);
    ga('send', 'pageview');
  }

  var index = function (router, id) {
    if (typeof router === 'function') {
      router(function (url) {
        collect(url, id);
      });
    } else {
      router.afterEach(function (to) {
        collect(to.fullPath, id);
      });
    }
  };

  return index;
};

function popup(url, title, w, h) {
  // Fixes dual-screen position                         Most browsers      Firefox
  var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : window.screenX;
  var dualScreenTop = window.screenTop != undefined ? window.screenTop : window.screenY;

  var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
  var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

  var left = ((width / 2) - (w / 2)) + dualScreenLeft;
  var top = ((height / 2) - (h / 2)) + dualScreenTop;
  var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

  // Puts focus on the newWindow
  if (window.focus) {
      newWindow.focus();
  }
  return newWindow;
}
