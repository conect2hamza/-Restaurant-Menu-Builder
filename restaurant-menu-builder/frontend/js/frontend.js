/**
 * Restaurant Menu Builder — frontend behaviour.
 *
 * Dependency free. Handles smooth scrolling to a category, keeping the active
 * navigation state in sync with the visible section, and keyboard navigation.
 */
( function () {
	'use strict';

	var REDUCED_MOTION = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function setActive( links, targetId ) {
		links.forEach( function ( link ) {
			var isActive = link.getAttribute( 'data-rmb-target' ) === targetId;

			link.classList.toggle( 'is-active', isActive );

			if ( isActive ) {
				link.setAttribute( 'aria-current', 'true' );
			} else {
				link.removeAttribute( 'aria-current' );
			}
		} );
	}

	function scrollNavToLink( list, link ) {
		if ( ! list || ! link ) {
			return;
		}

		var listRect = list.getBoundingClientRect();
		var linkRect = link.getBoundingClientRect();

		if ( linkRect.left < listRect.left || linkRect.right > listRect.right ) {
			list.scrollTo( {
				left: link.offsetLeft - list.clientWidth / 2 + link.clientWidth / 2,
				behavior: REDUCED_MOTION ? 'auto' : 'smooth'
			} );
		}
	}

	function initMenu( menu ) {
		if ( menu.getAttribute( 'data-rmb-ready' ) === '1' ) {
			return;
		}

		menu.setAttribute( 'data-rmb-ready', '1' );

		var nav = menu.querySelector( '.rmb-nav' );
		var links = Array.prototype.slice.call( menu.querySelectorAll( '.rmb-nav-link' ) );
		var sections = Array.prototype.slice.call( menu.querySelectorAll( '[data-rmb-section]' ) );

		if ( ! links.length || ! sections.length ) {
			return;
		}

		var list = menu.querySelector( '.rmb-nav-list' );

		links.forEach( function ( link ) {
			link.addEventListener( 'click', function ( event ) {
				var targetId = link.getAttribute( 'data-rmb-target' );
				var section = document.getElementById( targetId );

				if ( ! section ) {
					return;
				}

				event.preventDefault();

				var offset = nav ? nav.offsetHeight + 12 : 12;
				var top = section.getBoundingClientRect().top + window.pageYOffset - offset;

				window.scrollTo( {
					top: top < 0 ? 0 : top,
					behavior: REDUCED_MOTION ? 'auto' : 'smooth'
				} );

				setActive( links, targetId );

				// Move focus so keyboard and screen reader users follow the jump.
				section.setAttribute( 'tabindex', '-1' );
				section.focus( { preventScroll: true } );
			} );

			// Left and right arrows move along the category rail.
			link.addEventListener( 'keydown', function ( event ) {
				if ( event.key !== 'ArrowRight' && event.key !== 'ArrowLeft' ) {
					return;
				}

				event.preventDefault();

				var index = links.indexOf( link );
				var next = event.key === 'ArrowRight' ? index + 1 : index - 1;

				if ( next < 0 ) {
					next = links.length - 1;
				} else if ( next >= links.length ) {
					next = 0;
				}

				links[ next ].focus();
			} );
		} );

		if ( ! ( 'IntersectionObserver' in window ) ) {
			return;
		}

		var visible = {};

		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					visible[ entry.target.id ] = entry.isIntersecting ? entry.intersectionRatio : 0;
				} );

				var bestId = null;
				var bestRatio = 0;

				sections.forEach( function ( section ) {
					var ratio = visible[ section.id ] || 0;

					if ( ratio > bestRatio ) {
						bestRatio = ratio;
						bestId = section.id;
					}
				} );

				if ( bestId ) {
					setActive( links, bestId );

					var activeLink = menu.querySelector( '.rmb-nav-link.is-active' );
					scrollNavToLink( list, activeLink );
				}
			},
			{
				rootMargin: '-25% 0px -55% 0px',
				threshold: [ 0, 0.25, 0.5, 0.75, 1 ]
			}
		);

		sections.forEach( function ( section ) {
			observer.observe( section );
		} );
	}

	function init() {
		Array.prototype.forEach.call( document.querySelectorAll( '.rmb-menu' ), initMenu );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

	// Page builders and AJAX themes can inject a menu after load.
	window.addEventListener( 'load', init );
	document.addEventListener( 'rmb:refresh', init );
}() );
