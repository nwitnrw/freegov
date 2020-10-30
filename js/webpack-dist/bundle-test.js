/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "../modules/degov_media_video_mobile/js/navi.js":
/*!******************************************************!*\
  !*** ../modules/degov_media_video_mobile/js/navi.js ***!
  \******************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\nconst Navi = function(navigator) {\n  this.navigator = navigator;\n};\n\nNavi.prototype.getConnection = function() {\n  let connection;\n\n  if (typeof this.navigator.connection !== 'undefined') {\n    connection = this.navigator.connection;\n  } else if (typeof this.navigator.mozConnection !== 'undefined') {\n    connection = this.navigator.mozConnection;\n  } else if (typeof this.navigator.webkitConnection !== 'undefined') {\n    connection = this.navigator.webkitConnection;\n  }\n\n  return connection;\n};\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (Navi);\n\n\n//# sourceURL=webpack:///../modules/degov_media_video_mobile/js/navi.js?");

/***/ }),

/***/ "../modules/degov_media_video_mobile/js/speed_checker.js":
/*!***************************************************************!*\
  !*** ../modules/degov_media_video_mobile/js/speed_checker.js ***!
  \***************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\nconst SpeedChecker = function(window) {\n  this.window = window;\n};\n\nSpeedChecker.prototype.checkSlowLoadTime = function() {\n  const maxCellularLoadTime = 5000;\n  let loadTime = new Date().valueOf() - this.window.performance.timing.requestStart;\n  return (loadTime > maxCellularLoadTime);\n};\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (SpeedChecker);\n\n\n//# sourceURL=webpack:///../modules/degov_media_video_mobile/js/speed_checker.js?");

/***/ }),

/***/ "../modules/degov_media_video_mobile/js/user_agent_checker.js":
/*!********************************************************************!*\
  !*** ../modules/degov_media_video_mobile/js/user_agent_checker.js ***!
  \********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\nconst UserAgentChecker = function(window) {\n  this.window = window;\n};\n\nUserAgentChecker.prototype.isMobile = function() {\n  return this.window.navigator.userAgent.search(/mobile/i) !== -1;\n};\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (UserAgentChecker);\n\n\n//# sourceURL=webpack:///../modules/degov_media_video_mobile/js/user_agent_checker.js?");

/***/ }),

/***/ "./test/connection-tests.js":
/*!**********************************!*\
  !*** ./test/connection-tests.js ***!
  \**********************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _modules_degov_media_video_mobile_js_navi__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../modules/degov_media_video_mobile/js/navi */ \"../modules/degov_media_video_mobile/js/navi.js\");\n\n\n\nQUnit.test(\"Test Navigator Object\", function(assert) {\n\n  //established fake_navigator\n  const navigator =  {\n    connection: {\n      'onchange':null,\n      'effectiveType': \"4g\",\n      'rtt': 50,\n      'downlink':10,\n      'saveDate': false\n    }\n  };\n\n\n  const navi = new _modules_degov_media_video_mobile_js_navi__WEBPACK_IMPORTED_MODULE_0__[\"default\"](navigator);\n\n  assert.ok(typeof navi !== 'undefined', 'Navigator Object is available');\n  assert.ok(typeof navi.getConnection().effectiveType !== 'undefined', 'Expected: If effectiveType is given, then it is defined');\n  assert.equal(\"4g\", navi.getConnection().effectiveType, \"Expected: Connection is 4g\");\n  assert.notEqual(\"Slow 3g\", navi.getConnection().effectiveType, \"Expected: Connection is not Slow 3g\")\n  assert.equal('undefined', typeof navi.getConnection().type, \"Expected: If property navigator.connection.type (e.g) ist not define, we expect value to be undefined\");\n  assert.equal('undefined',typeof navi.getConnection().mozConnection, \"Expected: MozConnection expected to be undefined\");\n  assert.equal('undefined',typeof navi.getConnection().webkitConnection, \"Expected: webkitConnection expected to be undefined\");\n\n\n\n});\n\n\n//# sourceURL=webpack:///./test/connection-tests.js?");

/***/ }),

/***/ "./test/device-test.js":
/*!*****************************!*\
  !*** ./test/device-test.js ***!
  \*****************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _modules_degov_media_video_mobile_js_navi__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../modules/degov_media_video_mobile/js/navi */ \"../modules/degov_media_video_mobile/js/navi.js\");\n/* harmony import */ var _modules_degov_media_video_mobile_js_speed_checker__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../modules/degov_media_video_mobile/js/speed_checker */ \"../modules/degov_media_video_mobile/js/speed_checker.js\");\n\n\n\n\nQUnit.test('Cellular device test', function(assert) {\n\n  const navigator_1 =  {\n    connection: {\n      'onchange':null,\n      'effectiveType': \"4g\",\n      'rtt': 50,\n      'downlink':10,\n      'saveDate': false\n    }\n  };\n\n  const navigator_2 =  {\n    connection: {\n      'onchange':null,\n      'effectiveType': \"Slow 3G\",\n      'rtt': 50,\n      'downlink':10,\n      'saveDate': false\n    }\n  };\n\n  const navigator_3 =  {\n    connection: {\n      'onchange':null,\n      'effectiveType': \"Slow 3G\",\n      'type': \"cellular\",\n      'rtt': 50,\n      'downlink':10,\n      'saveDate': false\n    }\n  };\n\n  const fakeMozilla = {};\n\n  // slow 3g 1542381563475\n\n  // fast 3G 1542381613768\n\n  //create false window object\n\n  //fakewindow 1 with normal rate\n  const fakeWindowNormalRate = {};\n  fakeWindowNormalRate.performance = {};\n  fakeWindowNormalRate.performance.timing = {\n    'requestStart': 1542381447460\n  };\n\n  const fakeWindowslowRate = {};\n  fakeWindowslowRate.performance = {};\n  fakeWindowslowRate.performance.timing = {\n    'requestStart': 1542381563475\n  };\n\n  const speed_checker = new _modules_degov_media_video_mobile_js_speed_checker__WEBPACK_IMPORTED_MODULE_1__[\"default\"](fakeWindowNormalRate);\n\n  assert.equal(true, typeof(fakeMozilla.connection === 'undefined') && (speed_checker.checkSlowLoadTime()), \"Browser Mozilla at normal rate\");\n\n\n\n\n});\n\n\n//# sourceURL=webpack:///./test/device-test.js?");

/***/ }),

/***/ "./test/speed-test.js":
/*!****************************!*\
  !*** ./test/speed-test.js ***!
  \****************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _modules_degov_media_video_mobile_js_speed_checker__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../modules/degov_media_video_mobile/js/speed_checker */ \"../modules/degov_media_video_mobile/js/speed_checker.js\");\n\n\nQUnit.test(\"Test device speed\", function(assert){\n\n  //normal 1542381447460\n\n  // slow 3g 1542381563475\n\n  // fast 3G 1542381613768\n\n  //create false window object\n\n  //fakewindow 1 with normal rate\n  const fakeWindowNormalRate = {};\n  fakeWindowNormalRate.performance = {};\n  fakeWindowNormalRate.performance.timing = {\n    'requestStart': 1542381447460\n  };\n\n  const fakeWindowslowRate = {};\n  fakeWindowslowRate.performance = {};\n  fakeWindowslowRate.performance.timing = {\n    'requestStart': 1542381563475\n  };\n\n\n  const speed_checker = new _modules_degov_media_video_mobile_js_speed_checker__WEBPACK_IMPORTED_MODULE_0__[\"default\"](fakeWindowNormalRate);\n  const speed_checker2 = new _modules_degov_media_video_mobile_js_speed_checker__WEBPACK_IMPORTED_MODULE_0__[\"default\"](fakeWindowslowRate);\n\n  assert.ok(typeof speed_checker !=='undefined', 'Object Speedchecker with FakeWindow 1 with normal rate is available');\n\n  assert.ok(typeof speed_checker2 !=='undefined', 'Object Speedchecker with FakeWindow 2 with slow 3G is available');\n\n  assert.equal(true, speed_checker.checkSlowLoadTime(), 'FakeWindow 1: Speed is normal rate');\n\n  assert.equal(true,speed_checker2.checkSlowLoadTime(), 'FakeWindow 2: Speed is at Slow 3G');\n\n\n\n\n\n});\n\n\n//# sourceURL=webpack:///./test/speed-test.js?");

/***/ }),

/***/ "./test/useragent-test.js":
/*!********************************!*\
  !*** ./test/useragent-test.js ***!
  \********************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _modules_degov_media_video_mobile_js_navi__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../modules/degov_media_video_mobile/js/navi */ \"../modules/degov_media_video_mobile/js/navi.js\");\n/* harmony import */ var _modules_degov_media_video_mobile_js_user_agent_checker__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../modules/degov_media_video_mobile/js/user_agent_checker */ \"../modules/degov_media_video_mobile/js/user_agent_checker.js\");\n\n\n\n\nQUnit.test('User agent string test', function(assert) {\n\n  // Chrome on Mac\n  const navigator1 =  {\n    userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36'\n  };\n\n  // Mobile Safari\n  const navigator2 =  {\n    userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B137 Safari/601.1'\n  };\n\n  // Android Browser\n  const navigator3 =  {\n    userAgent: 'Mozilla/5.0 (Linux; U; Android 4.0.2; en-us; Galaxy Nexus Build/ICL53F) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30'\n  };\n\n  const fakeWindow1 = {};\n  fakeWindow1.navigator = navigator1;\n  const userAgentChecker1 = new _modules_degov_media_video_mobile_js_user_agent_checker__WEBPACK_IMPORTED_MODULE_1__[\"default\"](fakeWindow1);\n  assert.equal(false, userAgentChecker1.isMobile(), \"Chrome on Mac should report as not mobile.\");\n\n  const fakeWindow2 = {};\n  fakeWindow2.navigator = navigator2;\n  const userAgentChecker2 = new _modules_degov_media_video_mobile_js_user_agent_checker__WEBPACK_IMPORTED_MODULE_1__[\"default\"](fakeWindow2);\n  assert.equal(true, userAgentChecker2.isMobile(), \"Mobile Safari should report as mobile.\");\n\n  const fakeWindow3 = {};\n  fakeWindow3.navigator = navigator3;\n  const userAgentChecker3 = new _modules_degov_media_video_mobile_js_user_agent_checker__WEBPACK_IMPORTED_MODULE_1__[\"default\"](fakeWindow3);\n  assert.equal(true, userAgentChecker3.isMobile(), \"Android should report as mobile.\");\n});\n\n\n//# sourceURL=webpack:///./test/useragent-test.js?");

/***/ }),

/***/ 0:
/*!************************************************************************************************************!*\
  !*** multi ./test/connection-tests.js ./test/device-test.js ./test/speed-test.js ./test/useragent-test.js ***!
  \************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("__webpack_require__(/*! /Users/marc/workspace/nrwgov-responsive-videos/docroot/profiles/contrib/degov/js/test/connection-tests.js */\"./test/connection-tests.js\");\n__webpack_require__(/*! /Users/marc/workspace/nrwgov-responsive-videos/docroot/profiles/contrib/degov/js/test/device-test.js */\"./test/device-test.js\");\n__webpack_require__(/*! /Users/marc/workspace/nrwgov-responsive-videos/docroot/profiles/contrib/degov/js/test/speed-test.js */\"./test/speed-test.js\");\nmodule.exports = __webpack_require__(/*! /Users/marc/workspace/nrwgov-responsive-videos/docroot/profiles/contrib/degov/js/test/useragent-test.js */\"./test/useragent-test.js\");\n\n\n//# sourceURL=webpack:///multi_./test/connection-tests.js_./test/device-test.js_./test/speed-test.js_./test/useragent-test.js?");

/***/ })

/******/ });