/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/src/apps/js/frontend/api.js":
/*!********************************************!*\
  !*** ./assets/src/apps/js/frontend/api.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/**
 * List API on backend
 */
if (undefined === lpGlobalSettings) {
  throw new Error('lpGlobalSettings is undefined');
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  apiCourses: lpGlobalSettings.lp_rest_url + 'lp/v1/courses/archive-course'
});

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!************************************************!*\
  !*** ./assets/src/apps/js/frontend/courses.js ***!
  \************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _api__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./api */ "./assets/src/apps/js/frontend/api.js");

const jsHandlePageCourses = () => {
  if (undefined === lpGlobalSettings) {
    console.log('lpGlobalSettings is undefined');
    return;
  }
  const urlCurrent = document.location.href;
  let filterCourses = JSON.parse(window.localStorage.getItem('lp_filter_courses')) || {};
  let skeleton;
  let skeletonClone;
  let isLoading = false;
  let firstLoad = 1;
  let elNoLoadAjaxFirst;
  let elArchive;
  let elListCourse;
  let dataHtml;
  let paginationHtml;
  if (!lpGlobalSettings.lpArchiveLoadAjax) {
    console.log('Option load courses ajax is disabled');
    return;
  }
  if (lpGlobalSettings.is_course_archive) {
    const queryString = window.location.search;
    if (!queryString.length && urlCurrent.search('page') === -1) {
      filterCourses = {};
    }
  }
  const lpArchiveAddQueryArgs = (endpoint, args) => {
    const url = new URL(endpoint);
    Object.keys(args).forEach(arg => {
      url.searchParams.set(arg, args[arg]);
    });
    return url;
  };

  // Add events when load done.
  const lpArchiveCourse = () => {
    // Case load ajax when reload enable.
    if (!lpGlobalSettings.lpArchiveNoLoadAjaxFirst) {
      elListCourse.innerHTML = dataHtml;
      const pagination = paginationHtml;
      const paginationEle = document.querySelector('.learn-press-pagination');
      if (paginationEle) {
        paginationEle.remove();
      }
      if (typeof pagination !== 'undefined') {
        const paginationHTML = new DOMParser().parseFromString(pagination, 'text/html');
        const paginationNewNode = paginationHTML.querySelector('.learn-press-pagination');
        if (paginationNewNode) {
          elListCourse.after(paginationNewNode);
        }
      }
    }
    lpArchivePaginationCourse();
    lpArchiveSearchCourse();
  };

  // Call API load courses.
  window.lpArchiveRequestCourse = (args, callBackSuccess) => {
    if (isLoading) {
      return;
    }
    isLoading = true;

    // Append skeleton to list.
    if (skeletonClone) {
      elListCourse.append(skeletonClone);
    }
    const urlCourseArchive = lpArchiveAddQueryArgs(_api__WEBPACK_IMPORTED_MODULE_0__["default"].apiCourses, {
      ...lpGlobalSettings.lpArchiveSkeleton,
      ...args
    });
    const url = _api__WEBPACK_IMPORTED_MODULE_0__["default"].apiCourses + urlCourseArchive.search;
    let paramsFetch = {
      method: 'GET'
    };
    if (0 !== lpGlobalSettings.user_id) {
      paramsFetch = {
        ...paramsFetch,
        headers: {
          'X-WP-Nonce': lpGlobalSettings.nonce
        }
      };
    }
    fetch(url, paramsFetch).then(response => response.json()).then(response => {
      dataHtml = response.data.content || '';
      paginationHtml = response.data.pagination || '';
      if (!skeletonClone) {
        skeletonClone = skeleton.cloneNode(true);
      }
      if (!firstLoad) {
        elListCourse.innerHTML = dataHtml;
        const pagination = paginationHtml;
        const paginationEle = document.querySelector('.learn-press-pagination');
        if (paginationEle) {
          paginationEle.remove();
        }
        if (typeof pagination !== 'undefined') {
          const paginationHTML = new DOMParser().parseFromString(pagination, 'text/html');
          const paginationNewNode = paginationHTML.querySelector('.learn-press-pagination');
          if (paginationNewNode) {
            elListCourse.after(paginationNewNode);
            lpArchivePaginationCourse();
          }
        }
      }
      wp.hooks.doAction('lp-js-get-courses', response);
      if (typeof callBackSuccess === 'function') {
        callBackSuccess(response);
      }
    }).catch(error => {
      elListCourse.innerHTML += `<div class="lp-ajax-message error" style="display:block">${error.message || 'Error: Query lp/v1/courses/archive-course'}</div>`;
    }).finally(() => {
      isLoading = false;
      const btnSearchCourses = document.querySelector('form.search-courses button');
      btnSearchCourses.classList.remove('loading');
      if (!firstLoad) {
        // Scroll to archive element
        const optionScroll = {
          behavior: 'smooth'
        };
        elArchive.scrollIntoView(optionScroll);
      } else {
        firstLoad = 0;
      }

      // Save filter courses to Storage
      window.localStorage.setItem('lp_filter_courses', JSON.stringify(args));
      // Change url by params filter courses
      const urlPush = lpArchiveAddQueryArgs(document.location, args);
      window.history.pushState('', '', urlPush);
    });
  };

  // Call API load courses when js loaded.
  if (!lpGlobalSettings.lpArchiveNoLoadAjaxFirst) {
    lpArchiveRequestCourse(filterCourses);
  } else {
    firstLoad = 0;
  }
  const lpArchiveSearchCourse = () => {
    const searchForm = document.querySelectorAll('form.search-courses');
    const filterCourses = JSON.parse(window.localStorage.getItem('lp_filter_courses')) || {};
    searchForm.forEach(s => {
      const search = s.querySelector('input[name="c_search"]');
      const btn = s.querySelector('[type="submit"]');
      let timeOutSearch;
      search.addEventListener('keyup', event => {
        if (skeleton) {
          skeleton.style.display = 'block';
        }
        event.preventDefault();
        const s = event.target.value.trim();
        if (!s || s && s.length > 2) {
          if (undefined !== timeOutSearch) {
            clearTimeout(timeOutSearch);
          }
          timeOutSearch = setTimeout(function () {
            btn.classList.add('loading');
            filterCourses.c_search = s;
            filterCourses.paged = 1;
            lpArchiveRequestCourse({
              ...filterCourses
            });
          }, 800);
        }
      });
      s.addEventListener('submit', e => {
        e.preventDefault();
        const eleSearch = s.querySelector('input[name="c_search"]');
        eleSearch && eleSearch.dispatchEvent(new Event('keyup'));
      });
    });
  };
  const lpArchivePaginationCourse = () => {
    const paginationEle = document.querySelectorAll('.lp-archive-courses .learn-press-pagination .page-numbers');
    paginationEle.length > 0 && paginationEle.forEach(ele => ele.addEventListener('click', event => {
      event.preventDefault();
      event.stopPropagation();
      if (!elArchive) {
        return;
      }
      if (skeleton) {
        skeleton.style.display = 'block';
      }

      // Scroll to archive element
      elArchive.scrollIntoView({
        behavior: 'smooth'
      });
      let filterCourses = {};
      filterCourses = JSON.parse(window.localStorage.getItem('lp_filter_courses')) || {};
      const urlString = event.currentTarget.getAttribute('href');
      if (urlString) {
        const current = [...paginationEle].filter(el => el.classList.contains('current'));
        const paged = event.currentTarget.textContent || ele.classList.contains('next') && parseInt(current[0].textContent) + 1 || ele.classList.contains('prev') && parseInt(current[0].textContent) - 1;
        filterCourses.paged = paged;
        lpArchiveRequestCourse({
          ...filterCourses
        });
      }
    }));
  };
  const lpArchiveGridListCourse = () => {
    const layout = LP.Cookies.get('courses-layout');
    const switches = document.querySelectorAll('.lp-courses-bar .switch-layout [name="lp-switch-layout-btn"]');
    switches.length > 0 && [...switches].map(ele => ele.value === layout && (ele.checked = true));
  };
  const lpArchiveGridListCourseHandle = () => {
    const gridList = document.querySelectorAll('.lp-archive-courses input[name="lp-switch-layout-btn"]');
    gridList.length > 0 && gridList.forEach(element => element.addEventListener('change', e => {
      e.preventDefault();
      const layout = e.target.value;
      if (layout) {
        const dataLayout = document.querySelector('.lp-archive-courses .learn-press-courses[data-layout]');
        dataLayout && (dataLayout.dataset.layout = layout);
        LP.Cookies.set('courses-layout', layout);
      }
    }));
  };
  const LPArchiveCourseInit = () => {
    lpArchiveCourse();
    lpArchiveGridListCourseHandle();
    lpArchiveGridListCourse();
  };

  // document.addEventListener( 'DOMContentLoaded', function( event ) {
  // 	LPArchiveCourseInit();
  // } );

  const detectedElArchive = setInterval(function () {
    skeleton = document.querySelector('.lp-archive-course-skeleton');
    elArchive = document.querySelector('.lp-archive-courses');
    if (elArchive) {
      elListCourse = elArchive.querySelector('ul.learn-press-courses');
    }
    let canLoad = false;
    if (elListCourse && skeleton) {
      if (lpGlobalSettings.lpArchiveNoLoadAjaxFirst) {
        canLoad = true;
      } else if (dataHtml) {
        canLoad = true;
      }
      if (canLoad) {
        LPArchiveCourseInit();
        clearInterval(detectedElArchive);
      }
    }
  }, 1);
};
jsHandlePageCourses();
})();

/******/ })()
;
//# sourceMappingURL=courses.js.map