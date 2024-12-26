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
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/widgets/upcomingtasks/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./_sass/upcomingtasks.scss":
/*!**********************************!*\
  !*** ./_sass/upcomingtasks.scss ***!
  \**********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("// extracted by mini-css-extract-plugin\n\n//# sourceURL=webpack:///./_sass/upcomingtasks.scss?");

/***/ }),

/***/ "./src/widgets/upcomingtasks/index.js":
/*!********************************************!*\
  !*** ./src/widgets/upcomingtasks/index.js ***!
  \********************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _sass_upcomingtasks_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../_sass/upcomingtasks.scss */ \"./_sass/upcomingtasks.scss\");\n/* harmony import */ var _sass_upcomingtasks_scss__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_sass_upcomingtasks_scss__WEBPACK_IMPORTED_MODULE_0__);\nfunction _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }\n\nfunction _nonIterableRest() { throw new TypeError(\"Invalid attempt to destructure non-iterable instance.\\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.\"); }\n\nfunction _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === \"string\") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === \"Object\" && o.constructor) n = o.constructor.name; if (n === \"Map\" || n === \"Set\") return Array.from(o); if (n === \"Arguments\" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }\n\nfunction _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }\n\nfunction _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== \"undefined\" && arr[Symbol.iterator] || arr[\"@@iterator\"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i[\"return\"] != null) _i[\"return\"](); } finally { if (_d) throw _e; } } return _arr; }\n\nfunction _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }\n\nvar _wp$element = wp.element,\n    createElement = _wp$element.createElement,\n    render = _wp$element.render,\n    useState = _wp$element.useState,\n    useEffect = _wp$element.useEffect,\n    Fragment = _wp$element.Fragment;\nvar _wp$data = wp.data,\n    select = _wp$data.select,\n    dispatch = _wp$data.dispatch;\n\n\nvar UpcomingTasks = function UpcomingTasks(props) {\n  var localize = window.upcomingtasks;\n\n  var _useState = useState(true),\n      _useState2 = _slicedToArray(_useState, 2),\n      isLoading = _useState2[0],\n      setIsLoading = _useState2[1];\n\n  var _useState3 = useState({}),\n      _useState4 = _slicedToArray(_useState3, 2),\n      data = _useState4[0],\n      setData = _useState4[1];\n\n  useEffect(function () {\n    setIsLoading(true);\n    fetch(\"\".concat(localize.api, \"/get/upcomingtasks\"), {\n      method: 'post',\n      body: JSON.stringify({\n        token: select('vibebp').getToken()\n      })\n    }).then(function (res) {\n      return res.json();\n    }).then(function (rreturn) {\n      setIsLoading(false);\n\n      if (rreturn.status) {\n        setData(rreturn.data);\n      }\n    });\n  }, []);\n  return wp.element.createElement(\"div\", {\n    className: \"sales_stats_widget\"\n  }, wp.element.createElement(\"div\", {\n    className: \"sales_stats_header\"\n  }, wp.element.createElement(\"h3\", null, props.settings.title)), isLoading ? wp.element.createElement(\"div\", null, wp.element.createElement(\"div\", {\n    className: \"widget_loader\"\n  }, wp.element.createElement(\"div\", null), wp.element.createElement(\"div\", null), wp.element.createElement(\"div\", null), wp.element.createElement(\"div\", null))) : wp.element.createElement(Fragment, null, data.length ? wp.element.createElement(\"div\", {\n    className: \"tasks_widget_data\"\n  }, wp.element.createElement(\"div\", {\n    className: \"header data-row\"\n  }, wp.element.createElement(\"span\", null, localize.translations.task_name), wp.element.createElement(\"span\", null, localize.translations.due_date), wp.element.createElement(\"span\", null, localize.translations.project)), data.map(function (d) {\n    return wp.element.createElement(\"div\", {\n      className: \"data-row\"\n    }, wp.element.createElement(\"span\", null, d.title), wp.element.createElement(\"span\", null, moment(parseInt(d.time) * 1000).format(localize.settings.date_format)), wp.element.createElement(\"span\", null, d.project));\n  })) : ''));\n};\n\ndocument.addEventListener(\"vibe_projects_upcoming_tasks\", function (e) {\n  //e.target.removeEventListener(e.type, arguments.callee);\n  document.querySelectorAll('.vibe_projects_upcoming_tasks').forEach(function (el) {\n    render(wp.element.createElement(UpcomingTasks, {\n      settings: e.detail.widget.options\n    }), el);\n  });\n});\n\n//# sourceURL=webpack:///./src/widgets/upcomingtasks/index.js?");

/***/ })

/******/ });