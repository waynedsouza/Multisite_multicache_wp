/*function blazyload(){jQuery(function(){jQuery("img.multicache_lazy").show().lazyload()})}function timeoutlazy(e,o,n,l){n="undefined"==typeof n?1:++n,l="undefined"==typeof l?1e4:l,o="undefined"==typeof o?10:o,e="undefined"==typeof e?"blazyload":e,("undefined"==typeof window.jQuery||"undefined"==typeof window.MULTICACHELAZYLOADED)&&l>=n?setTimeout(function(){timeoutlazylib(e,o,n)},o):(console.log("calling lazy.."),blazyload())}timeoutlazy("blazyload",1);*/
"undefined"!=typeof jQuery?jQuery(function(){jQuery("img.multicache_lazy").show().lazyload()}):"undefined"!=typeof $&&$(function(){$("img.multicache_lazy").show().lazyload()});