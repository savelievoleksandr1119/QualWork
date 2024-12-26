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
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/setup/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./_sass/main.scss":
/*!*************************!*\
  !*** ./_sass/main.scss ***!
  \*************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("// extracted by mini-css-extract-plugin\n\n//# sourceURL=webpack:///./_sass/main.scss?");

/***/ }),

/***/ "./src/setup/index.js":
/*!****************************!*\
  !*** ./src/setup/index.js ***!
  \****************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _sass_main_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../_sass/main.scss */ \"./_sass/main.scss\");\n/* harmony import */ var _sass_main_scss__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_sass_main_scss__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _wizard__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./wizard */ \"./src/setup/wizard.js\");\nfunction _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _nonIterableRest(); }\n\nfunction _nonIterableRest() { throw new TypeError(\"Invalid attempt to destructure non-iterable instance\"); }\n\nfunction _iterableToArrayLimit(arr, i) { if (!(Symbol.iterator in Object(arr) || Object.prototype.toString.call(arr) === \"[object Arguments]\")) { return; } var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i[\"return\"] != null) _i[\"return\"](); } finally { if (_d) throw _e; } } return _arr; }\n\nfunction _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }\n\n\n\nvar _wp$element = wp.element,\n    createElement = _wp$element.createElement,\n    useState = _wp$element.useState,\n    useEffect = _wp$element.useEffect,\n    Fragment = _wp$element.Fragment,\n    render = _wp$element.render;\nvar _wp$data = wp.data,\n    dispatch = _wp$data.dispatch,\n    select = _wp$data.select;\n\nvar SetupWizard = function SetupWizard(props) {\n  var _useState = useState({}),\n      _useState2 = _slicedToArray(_useState, 2),\n      user = _useState2[0],\n      setUser = _useState2[1];\n\n  var _useState3 = useState(false),\n      _useState4 = _slicedToArray(_useState3, 2),\n      init = _useState4[0],\n      setInit = _useState4[1];\n\n  return wp.element.createElement(\"div\", {\n    className: \"setup_wizard\"\n  }, init ? wp.element.createElement(_wizard__WEBPACK_IMPORTED_MODULE_1__[\"default\"], {\n    close: function close() {\n      return setInit(false);\n    }\n  }) : wp.element.createElement(\"div\", {\n    className: \"wizard_message\"\n  }, wp.element.createElement(\"p\", null, window.vibebp_setup.translations.configure_vibebp, wp.element.createElement(\"a\", {\n    className: \"button-primary\",\n    onClick: function onClick() {\n      return setInit(true);\n    }\n  }, window.vibebp_setup.translations.setup_wizard))));\n};\n\nrender(wp.element.createElement(SetupWizard, null), document.getElementById(\"vibebp_setup_wizard\"));\n\n//# sourceURL=webpack:///./src/setup/index.js?");

/***/ }),

/***/ "./src/setup/progress.js":
/*!*******************************!*\
  !*** ./src/setup/progress.js ***!
  \*******************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\nvar _wp$element = wp.element,\n    createElement = _wp$element.createElement,\n    useState = _wp$element.useState,\n    useEffect = _wp$element.useEffect,\n    Fragment = _wp$element.Fragment,\n    render = _wp$element.render;\nvar _wp$data = wp.data,\n    dispatch = _wp$data.dispatch,\n    select = _wp$data.select;\n\nvar ReactProgressCircle = function ReactProgressCircle(_ref) {\n  var percentage = _ref.percentage,\n      size = _ref.size;\n  var appliedRadius;\n  var appliedStroke;\n\n  switch (size) {\n    case 'xs':\n      appliedRadius = 10;\n      appliedStroke = 1;\n      break;\n\n    case 'sm':\n      appliedRadius = 25;\n      appliedStroke = 2.5;\n      break;\n\n    case 'med':\n      appliedRadius = 50;\n      appliedStroke = 5;\n      break;\n\n    case 'lg':\n      appliedRadius = 75;\n      appliedStroke = 7.5;\n      break;\n\n    case 'xl':\n      appliedRadius = 100;\n      appliedStroke = 10;\n      break;\n\n    default:\n      appliedRadius = 50;\n      appliedStroke = 5;\n  }\n\n  var normalizedRadius = appliedRadius - appliedStroke * 2;\n  var circumference = normalizedRadius * 2 * Math.PI;\n  var strokeDashoffset = circumference - percentage / 100 * circumference;\n  return wp.element.createElement(\"div\", {\n    className: \"react-progress-circle\"\n  }, wp.element.createElement(\"svg\", {\n    height: appliedRadius * 2,\n    width: appliedRadius * 2\n  }, wp.element.createElement(\"circle\", {\n    className: \"ReactProgressCircle_circleBackground\",\n    strokeWidth: appliedStroke,\n    style: {\n      strokeDashoffset: strokeDashoffset\n    },\n    r: normalizedRadius,\n    cx: appliedRadius,\n    cy: appliedRadius\n  }), wp.element.createElement(\"circle\", {\n    className: \"ReactProgressCircle_circle\",\n    strokeWidth: appliedStroke,\n    strokeDasharray: circumference + ' ' + circumference,\n    style: {\n      strokeDashoffset: strokeDashoffset\n    },\n    r: normalizedRadius,\n    cx: appliedRadius,\n    cy: appliedRadius\n  })));\n};\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (ReactProgressCircle);\n\n//# sourceURL=webpack:///./src/setup/progress.js?");

/***/ }),

/***/ "./src/setup/wizard.js":
/*!*****************************!*\
  !*** ./src/setup/wizard.js ***!
  \*****************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _progress__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./progress */ \"./src/setup/progress.js\");\nfunction _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _nonIterableSpread(); }\n\nfunction _nonIterableSpread() { throw new TypeError(\"Invalid attempt to spread non-iterable instance\"); }\n\nfunction _iterableToArray(iter) { if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === \"[object Arguments]\") return Array.from(iter); }\n\nfunction _arrayWithoutHoles(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } }\n\nfunction _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _nonIterableRest(); }\n\nfunction _nonIterableRest() { throw new TypeError(\"Invalid attempt to destructure non-iterable instance\"); }\n\nfunction _iterableToArrayLimit(arr, i) { if (!(Symbol.iterator in Object(arr) || Object.prototype.toString.call(arr) === \"[object Arguments]\")) { return; } var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i[\"return\"] != null) _i[\"return\"](); } finally { if (_d) throw _e; } } return _arr; }\n\nfunction _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }\n\nvar _wp$element = wp.element,\n    createElement = _wp$element.createElement,\n    useState = _wp$element.useState,\n    useEffect = _wp$element.useEffect,\n    Fragment = _wp$element.Fragment,\n    render = _wp$element.render;\nvar _wp$data = wp.data,\n    dispatch = _wp$data.dispatch,\n    select = _wp$data.select;\n\n\nvar Wizard = function Wizard(props) {\n  var _useState = useState(false),\n      _useState2 = _slicedToArray(_useState, 2),\n      isSetupWizard = _useState2[0],\n      setIsSetupWizard = _useState2[1];\n\n  var _useState3 = useState(true),\n      _useState4 = _slicedToArray(_useState3, 2),\n      isRequired = _useState4[0],\n      setIsRequired = _useState4[1];\n\n  var _useState5 = useState(false),\n      _useState6 = _slicedToArray(_useState5, 2),\n      isLoading = _useState6[0],\n      setIsLoading = _useState6[1];\n\n  var _useState7 = useState(window.vibebp_setup.installation.steps[0]),\n      _useState8 = _slicedToArray(_useState7, 2),\n      step = _useState8[0],\n      setStep = _useState8[1];\n\n  var _useState9 = useState(true),\n      _useState10 = _slicedToArray(_useState9, 2),\n      stepComplete = _useState10[0],\n      setStepComplete = _useState10[1];\n\n  var _useState11 = useState([]),\n      _useState12 = _slicedToArray(_useState11, 2),\n      features = _useState12[0],\n      setFeatures = _useState12[1];\n\n  var _useState13 = useState([]),\n      _useState14 = _slicedToArray(_useState13, 2),\n      layouts = _useState14[0],\n      setLayouts = _useState14[1];\n\n  var _useState15 = useState([]),\n      _useState16 = _slicedToArray(_useState15, 2),\n      accesses = _useState16[0],\n      setAccesses = _useState16[1];\n\n  var _useState17 = useState(''),\n      _useState18 = _slicedToArray(_useState17, 2),\n      tab = _useState18[0],\n      setTab = _useState18[1];\n\n  var _useState19 = useState([]),\n      _useState20 = _slicedToArray(_useState19, 2),\n      tabs = _useState20[0],\n      stTabs = _useState20[1];\n\n  useEffect(function () {\n    window.vibebp_setup.installation.plugins.map(function (plugin) {\n      if (plugin.required) {\n        if (plugin.status && isRequired) {\n          setIsRequired(true);\n        } else {\n          setIsRequired(false);\n        }\n      }\n    });\n\n    var nfeatures = _toConsumableArray(features);\n\n    window.vibebp_setup.installation.steps[0].features.map(function (feature) {\n      if (feature.required || feature.is_active) {\n        nfeatures.push(feature);\n      }\n    });\n    setFeatures(nfeatures);\n  }, []);\n\n  var requiredAction = function requiredAction() {\n    var payload = {};\n\n    if (step.key == 'features') {\n      payload = {\n        step: 'features',\n        features: features\n      };\n    }\n\n    if (step.key == 'content') {\n      payload = {\n        step: 'content',\n        content: layouts\n      };\n    }\n\n    if (step.key == 'access') {\n      payload = {\n        step: 'access',\n        accesses: accesses\n      };\n    }\n\n    setIsLoading(true);\n    return fetch(\"\".concat(window.vibebp_setup.api.url, \"/setup_wizard?security=\").concat(window.vibebp_setup.security), {\n      method: 'post',\n      body: JSON.stringify(payload)\n    }).then(function (res) {\n      return res.json();\n    }).then(function (data) {\n      setIsLoading(false);\n\n      if (data.status) {\n        //increase steps till it is the last step\n        if (window.vibebp_setup.installation.steps.indexOf(step) + 1 < window.vibebp_setup.installation.steps.length) {\n          setStep(window.vibebp_setup.installation.steps[window.vibebp_setup.installation.steps.indexOf(step) + 1]);\n        }\n      } else {\n        if (data.hasOwnProperty('message')) {\n          alert(data.message);\n        }\n      }\n    });\n  };\n\n  var completeWizard = function completeWizard() {\n    setIsLoading(true);\n    requiredAction();\n    fetch(\"\".concat(window.vibebp_setup.api.url, \"/complete_wizard?security=\").concat(window.vibebp_setup.security), {\n      method: 'post',\n      body: JSON.stringify({\n        id: window.vibebp_setup.api.admin_id\n      })\n    }).then(function (res) {\n      return res.json();\n    }).then(function (data) {\n      setIsLoading(false);\n\n      if (data.url) {\n        var event = new Event('vibebp_setup_wizard_complete');\n        document.dispatchEvent(event);\n        window.open(data.url, '_blank');\n        window.focus();\n      }\n    });\n  };\n\n  return wp.element.createElement(\"div\", {\n    className: \"vibebp_setup_wizard_wrapper\"\n  }, wp.element.createElement(\"span\", {\n    onClick: props.close\n  }), wp.element.createElement(\"div\", {\n    className: \"vibebp_setup_wizard\"\n  }, isSetupWizard ? wp.element.createElement(\"div\", {\n    className: \"vibebp_setup_steps_wrapper\"\n  }, wp.element.createElement(\"div\", {\n    className: \"vibebp_setup_steps\"\n  }, wp.element.createElement(\"ul\", null, window.vibebp_setup.installation.steps.map(function (s) {\n    return wp.element.createElement(\"li\", {\n      className: s.key == step.key ? 'active' : '',\n      onClick: function onClick() {\n        setStep(s);\n        setIsRequired(false);\n      }\n    }, wp.element.createElement(\"span\", null, s.label));\n  }))), wp.element.createElement(\"div\", {\n    className: \"vibebp_setup_steps_content\"\n  }, wp.element.createElement(\"strong\", {\n    dangerouslySetInnerHTML: {\n      __html: step.description\n    }\n  }), step.key == 'features' ? wp.element.createElement(\"div\", {\n    className: \"features\"\n  }, step.features.map(function (feature) {\n    return wp.element.createElement(\"div\", {\n      className: features.indexOf(feature) > -1 || feature.required ? 'feature active' : 'feature',\n      onClick: function onClick() {\n        if (!feature.required) {\n          var nfeatures = _toConsumableArray(features);\n\n          if (features.indexOf(feature) > -1) {\n            nfeatures.splice(features.indexOf(feature), 1);\n          } else {\n            nfeatures.push(feature);\n          }\n\n          setFeatures(nfeatures);\n          setStepComplete(false);\n        }\n      }\n    }, wp.element.createElement(\"span\", {\n      dangerouslySetInnerHTML: {\n        __html: feature.icon\n      }\n    }), wp.element.createElement(\"span\", null, feature.label));\n  })) : step.key == 'content' ? wp.element.createElement(\"div\", {\n    className: \"content\"\n  }, step.layouts.map(function (layout) {\n    if (layout.type == 'checkbox') {\n      var index = 'layout_' + Math.round(Math.random() * 10000);\n      return wp.element.createElement(\"div\", {\n        className: \"layout\"\n      }, wp.element.createElement(\"div\", {\n        className: \"checkbox\"\n      }, wp.element.createElement(\"input\", {\n        type: \"checkbox\",\n        id: index,\n        value: layout.key,\n        checked: layouts.indexOf(layout) > -1 ? true : false,\n        onChange: function onChange() {\n          setLayouts([].concat(_toConsumableArray(layouts), [layout]));\n          setStepComplete(false);\n        }\n      }), wp.element.createElement(\"svg\", {\n        onClick: function onClick() {\n          if (layouts.indexOf(layout) > -1) {\n            var nlayouts = _toConsumableArray(layouts);\n\n            nlayouts.splice(layouts.indexOf(layout), 1);\n            setLayouts(nlayouts);\n          } else {\n            setLayouts([].concat(_toConsumableArray(layouts), [layout]));\n          }\n        },\n        width: \"48\",\n        height: \"48\",\n        viewBox: \"0 0 24 24\",\n        style: {\n          fillRule: 'evenodd',\n          clipRule: 'evenodd',\n          strokeLinejoin: 'round',\n          strokeMiterlimit: 2\n        }\n      }, wp.element.createElement(\"path\", {\n        d: \"M18,18L6,18C2.689,18 0,15.311 0,12C0,8.689 2.689,6 6,6L18.039,6C21.332,6.021 24,8.701 24,12C24,15.311 21.312,18 18,18Z\"\n      }), wp.element.createElement(\"path\", {\n        d: \"M6,8C8.208,8 10,9.792 10,12C10,14.208 8.208,16 6,16C3.792,16 2,14.208 2,12C2,9.792 3.792,8 6,8Z\",\n        style: {\n          fill: 'white'\n        }\n      })), wp.element.createElement(\"label\", {\n        \"for\": index\n      }, layout.label)), wp.element.createElement(_progress__WEBPACK_IMPORTED_MODULE_0__[\"default\"], {\n        size: \"xs\",\n        percentage: 0\n      }));\n    }\n  })) : step.key == 'access' ? wp.element.createElement(Fragment, null, wp.element.createElement(\"div\", {\n    className: \"content\"\n  }, step.access.map(function (access) {\n    if (access.type == 'checkbox') {\n      var index = 'access' + Math.round(Math.random() * 10000);\n      return wp.element.createElement(\"div\", {\n        className: \"layout\"\n      }, wp.element.createElement(\"div\", {\n        className: \"checkbox\"\n      }, wp.element.createElement(\"input\", {\n        type: \"checkbox\",\n        id: index,\n        value: access.key,\n        checked: accesses.indexOf(access) > -1 ? true : false,\n        onChange: function onChange() {\n          if (accesses.indexOf(access) == -1) {\n            setAccesses([].concat(_toConsumableArray(accesses), [access]));\n          } else {\n            var naccesses = _toConsumableArray(accesses);\n\n            naccesses.splice(accesses.indexOf(access), 1);\n            setAccesses(naccesses);\n          }\n        }\n      }), wp.element.createElement(\"svg\", {\n        width: \"48\",\n        height: \"48\",\n        viewBox: \"0 0 24 24\",\n        style: {\n          fillRule: 'evenodd',\n          clipRule: 'evenodd',\n          strokeLinejoin: 'round',\n          strokeMiterlimit: 2\n        }\n      }, wp.element.createElement(\"path\", {\n        d: \"M18,18L6,18C2.689,18 0,15.311 0,12C0,8.689 2.689,6 6,6L18.039,6C21.332,6.021 24,8.701 24,12C24,15.311 21.312,18 18,18Z\"\n      }), wp.element.createElement(\"path\", {\n        d: \"M6,8C8.208,8 10,9.792 10,12C10,14.208 8.208,16 6,16C3.792,16 2,14.208 2,12C2,9.792 3.792,8 6,8Z\",\n        style: {\n          fill: 'white'\n        }\n      })), wp.element.createElement(\"label\", {\n        \"for\": index\n      }, access.label)));\n    }\n  })), wp.element.createElement(\"a\", {\n    \"class\": \"button\",\n    onClick: completeWizard\n  }, \"Complete\")) : '', window.vibebp_setup.installation.steps.indexOf(step) < window.vibebp_setup.installation.steps.length - 1 ? wp.element.createElement(\"a\", {\n    className: \"button\",\n    onClick: function onClick() {\n      if (stepComplete) {\n        setStep(window.vibebp_setup.installation.steps[window.vibebp_setup.installation.steps.indexOf(step) + 1]);\n      } else {\n        requiredAction();\n      }\n    }\n  }, isLoading ? '...' : window.vibebp_setup.translations.next_step) : '')) : wp.element.createElement(\"div\", {\n    className: \"introduction_wrapper\"\n  }, wp.element.createElement(\"div\", {\n    className: \"introduction\"\n  }, wp.element.createElement(\"h1\", null, wp.element.createElement(\"span\", null, window.vibebp_setup.installation.title)), wp.element.createElement(\"p\", null, window.vibebp_setup.installation.description), wp.element.createElement(\"div\", {\n    className: \"plugins_list\"\n  }, window.vibebp_setup.installation.plugins.map(function (plugin) {\n    return wp.element.createElement(\"div\", {\n      className: \"plugin\"\n    }, wp.element.createElement(\"span\", {\n      dangerouslySetInnerHTML: {\n        __html: plugin.icon\n      }\n    }), wp.element.createElement(\"div\", null, wp.element.createElement(\"h3\", null, plugin.label, plugin.labels && plugin.labels.length ? wp.element.createElement(\"span\", null, plugin.labels.map(function (label) {\n      return wp.element.createElement(\"span\", {\n        style: {\n          background: label.color\n        }\n      }, label.label);\n    })) : '', plugin.status == 2 ? wp.element.createElement(\"svg\", {\n      width: \"24\",\n      height: \"24\",\n      viewBox: \"0 0 24 24\"\n    }, wp.element.createElement(\"path\", {\n      d: \"M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1.959 17l-4.5-4.319 1.395-1.435 3.08 2.937 7.021-7.183 1.422 1.409-8.418 8.591z\"\n    })) : ''), wp.element.createElement(\"p\", {\n      dangerouslySetInnerHTML: {\n        __html: plugin.desc\n      }\n    }), wp.element.createElement(\"div\", null, plugin.required ? wp.element.createElement(\"span\", {\n      className: \"required\"\n    }, window.vibebp_setup.translations.required) : plugin.recommended ? wp.element.createElement(\"span\", null, window.vibebp_setup.translations.recommended) : wp.element.createElement(\"i\", null), wp.element.createElement(\"span\", null, plugin.status == 2 ? '' : plugin.status == 1 ? wp.element.createElement(\"span\", null, window.vibebp_setup.translations.activate_plugin) : wp.element.createElement(\"span\", null, plugin.hasOwnProperty('link') ? wp.element.createElement(\"a\", {\n      href: plugin.link,\n      target: \"_blank\",\n      \"class\": \"link\"\n    }, window.vibebp_setup.translations.install_plugin) : '')))));\n  }))), wp.element.createElement(\"a\", {\n    className: isRequired ? 'button' : 'button disabled',\n    onClick: function onClick() {\n      if (isRequired) {\n        setIsSetupWizard(true);\n      }\n    }\n  }, window.vibebp_setup.translations.begin_setup))));\n};\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (Wizard);\n\n//# sourceURL=webpack:///./src/setup/wizard.js?");

/***/ })

/******/ });