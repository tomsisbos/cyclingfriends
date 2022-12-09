"use strict";
(self["webpackChunk"] = self["webpackChunk"] || []).push([["settings/Board"],{

/***/ "./react/settings/Board.jsx":
/*!**********************************!*\
  !*** ./react/settings/Board.jsx ***!
  \**********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _react_settings_ChangePassword_jsx__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../react/settings/ChangePassword.jsx */ "./react/settings/ChangePassword.jsx");
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor); } }
function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); Object.defineProperty(subClass, "prototype", { writable: false }); if (superClass) _setPrototypeOf(subClass, superClass); }
function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }
function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }
function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } else if (call !== void 0) { throw new TypeError("Derived constructors may only return object or undefined"); } return _assertThisInitialized(self); }
function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }
function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); return true; } catch (e) { return false; } }
function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

var Board = /*#__PURE__*/function (_React$Component) {
  _inherits(Board, _React$Component);
  var _super = _createSuper(Board);
  function Board(props) {
    var _this;
    _classCallCheck(this, Board);
    _this = _super.call(this, props);
    _this.state = {
      page: 'ChangeAccount'
    };
    return _this;
  }
  _createClass(Board, [{
    key: "render",
    value: function render() {
      var _this2 = this;
      switch (this.state.page) {
        case 'ChangeAccount':
          return 'A';
        case 'ChangePassword':
          return (0,_react_settings_ChangePassword_jsx__WEBPACK_IMPORTED_MODULE_0__["default"])();
        case 'Privacy':
          return 'C';
      }
      return /*#__PURE__*/React.createElement("button", {
        onClick: function onClick() {
          return _this2.setState({
            liked: true
          });
        }
      }, "Like");
    }
  }]);
  return Board;
}(React.Component);
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Board);

/***/ }),

/***/ "./react/settings/ChangePassword.jsx":
/*!*******************************************!*\
  !*** ./react/settings/ChangePassword.jsx ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ ChangePassword)
/* harmony export */ });
function ChangePassword(props) {
  return /*#__PURE__*/React.createElement("div", null, "This is the zone for changing password.");
}

/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ var __webpack_exports__ = (__webpack_exec__("./react/settings/Board.jsx"));
/******/ }
]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic2V0dGluZ3MvQm9hcmQuanMiLCJtYXBwaW5ncyI6Ijs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7O0FBQStEO0FBQUEsSUFFekRDLEtBQUs7RUFBQTtFQUFBO0VBRVAsZUFBWUMsS0FBSyxFQUFFO0lBQUE7SUFBQTtJQUNmLDBCQUFNQSxLQUFLO0lBQ1gsTUFBS0MsS0FBSyxHQUFHO01BQ1RDLElBQUksRUFBRTtJQUNWLENBQUM7SUFBQTtFQUNMO0VBQUM7SUFBQTtJQUFBLE9BRUQsa0JBQVM7TUFBQTtNQUNMLFFBQVEsSUFBSSxDQUFDRCxLQUFLLENBQUNDLElBQUk7UUFDbkIsS0FBSyxlQUFlO1VBQUUsT0FBTyxHQUFHO1FBQ2hDLEtBQUssZ0JBQWdCO1VBQUUsT0FBT0osOEVBQWMsRUFBRTtRQUM5QyxLQUFLLFNBQVM7VUFBRSxPQUFPLEdBQUc7TUFBQTtNQUc5QixvQkFDSTtRQUFRLE9BQU8sRUFBRTtVQUFBLE9BQU0sTUFBSSxDQUFDSyxRQUFRLENBQUM7WUFBRUMsS0FBSyxFQUFFO1VBQUssQ0FBQyxDQUFDO1FBQUE7TUFBQyxVQUU3QztJQUVqQjtFQUFDO0VBQUE7QUFBQSxFQXJCZUMsS0FBSyxDQUFDQyxTQUFTO0FBeUJuQyxpRUFBZVAsS0FBSzs7Ozs7Ozs7Ozs7Ozs7QUMzQkwsU0FBU0QsY0FBYyxDQUFFRSxLQUFLLEVBQUU7RUFFM0Msb0JBQ0ksMkVBRU07QUFHZCIsInNvdXJjZXMiOlsid2VicGFjazovLy8uL3JlYWN0L3NldHRpbmdzL0JvYXJkLmpzeCIsIndlYnBhY2s6Ly8vLi9yZWFjdC9zZXR0aW5ncy9DaGFuZ2VQYXNzd29yZC5qc3giXSwic291cmNlc0NvbnRlbnQiOlsiaW1wb3J0IENoYW5nZVBhc3N3b3JkIGZyb20gXCIvcmVhY3Qvc2V0dGluZ3MvQ2hhbmdlUGFzc3dvcmQuanN4XCJcclxuXHJcbmNsYXNzIEJvYXJkIGV4dGVuZHMgUmVhY3QuQ29tcG9uZW50IHtcclxuXHJcbiAgICBjb25zdHJ1Y3Rvcihwcm9wcykge1xyXG4gICAgICAgIHN1cGVyKHByb3BzKVxyXG4gICAgICAgIHRoaXMuc3RhdGUgPSB7XHJcbiAgICAgICAgICAgIHBhZ2U6ICdDaGFuZ2VBY2NvdW50J1xyXG4gICAgICAgIH1cclxuICAgIH1cclxuXHJcbiAgICByZW5kZXIoKSB7XHJcbiAgICAgICAgc3dpdGNoICh0aGlzLnN0YXRlLnBhZ2UpIHtcclxuICAgICAgICAgICAgY2FzZSAnQ2hhbmdlQWNjb3VudCc6IHJldHVybiAnQSdcclxuICAgICAgICAgICAgY2FzZSAnQ2hhbmdlUGFzc3dvcmQnOiByZXR1cm4gQ2hhbmdlUGFzc3dvcmQoKVxyXG4gICAgICAgICAgICBjYXNlICdQcml2YWN5JzogcmV0dXJuICdDJ1xyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgcmV0dXJuIChcclxuICAgICAgICAgICAgPGJ1dHRvbiBvbkNsaWNrPXsoKSA9PiB0aGlzLnNldFN0YXRlKHsgbGlrZWQ6IHRydWUgfSl9PlxyXG4gICAgICAgICAgICAgICAgTGlrZVxyXG4gICAgICAgICAgICA8L2J1dHRvbj5cclxuICAgICAgICApXHJcbiAgICB9XHJcblxyXG59XHJcblxyXG5leHBvcnQgZGVmYXVsdCBCb2FyZCIsImV4cG9ydCBkZWZhdWx0IGZ1bmN0aW9uIENoYW5nZVBhc3N3b3JkIChwcm9wcykge1xyXG5cclxuICAgIHJldHVybiAoXHJcbiAgICAgICAgPGRpdj5cclxuICAgICAgICAgICAgVGhpcyBpcyB0aGUgem9uZSBmb3IgY2hhbmdpbmcgcGFzc3dvcmQuXHJcbiAgICAgICAgPC9kaXY+XHJcbiAgICApXHJcblxyXG59Il0sIm5hbWVzIjpbIkNoYW5nZVBhc3N3b3JkIiwiQm9hcmQiLCJwcm9wcyIsInN0YXRlIiwicGFnZSIsInNldFN0YXRlIiwibGlrZWQiLCJSZWFjdCIsIkNvbXBvbmVudCJdLCJzb3VyY2VSb290IjoiIn0=