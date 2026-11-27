/*! Migi Motion – zero-dependency scroll & hover effects. */
(function () {
	'use strict';
	var d = document, w = window, C = w.mgmConfig || {};
	var reduce = w.matchMedia && w.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var fine = w.matchMedia && w.matchMedia('(pointer: fine)').matches;
	var $$ = function (s, r) { return Array.prototype.slice.call((r || d).querySelectorAll(s)); };

	/* Split text into words > letters (screen readers keep the original text). */
	function split(el) {
		if (el.dataset.mgmSplit) { return; }
		el.dataset.mgmSplit = 1;
		var i = 0;
		el.setAttribute('aria-label', el.textContent.trim().replace(/\s+/g, ' '));
		(function walk(node) {
			Array.prototype.slice.call(node.childNodes).forEach(function (n) {
				if (n.nodeType === 3) {
					var frag = d.createDocumentFragment();
					n.textContent.split(/(\s+)/).forEach(function (word) {
						if (!word) { return; }
						if (/^\s+$/.test(word)) { frag.appendChild(d.createTextNode(' ')); return; }
						var wspan = d.createElement('span');
						wspan.className = 'mgm-w';
						wspan.setAttribute('aria-hidden', 'true');
						Array.from(word).forEach(function (ch) {
							var s = d.createElement('span');
							s.className = 'mgm-ch';
							s.style.setProperty('--i', i++);
							s.textContent = ch;
							wspan.appendChild(s);
						});
						frag.appendChild(wspan);
					});
					n.parentNode.replaceChild(frag, n);
				} else if (n.nodeType === 1 && !/^(BR|SVG|IMG)$/.test(n.tagName)) {
					walk(n);
				}
			});
		})(el);
	}

	/* Animated counter: data-to="99845" or the number written in the element ("99.845+"). */
	function count(el) {
		if (el.dataset.mgmCounted || reduce) { return; }
		el.dataset.mgmCounted = 1;
		var txt = el.textContent.trim(), m = txt.match(/[\d.,]+/);
		var end = el.dataset.to ? parseFloat(el.dataset.to) : (m ? parseFloat(m[0].replace(/\./g, '').replace(',', '.')) : 0);
		if (!end) { return; }
		var pre = m ? txt.slice(0, m.index) : '';
		var suf = el.dataset.suffix !== undefined ? el.dataset.suffix : (m ? txt.slice(m.index + m[0].length) : '');
		var dec = (String(end).split('.')[1] || '').length, dur = +(el.dataset.duration || 1800), t0 = null;
		function step(t) {
			t0 = t0 || t;
			var p = Math.min((t - t0) / dur, 1), v = end * (1 - Math.pow(1 - p, 3));
			el.textContent = pre + v.toLocaleString('vi-VN', { minimumFractionDigits: dec, maximumFractionDigits: dec }) + suf;
			if (p < 1) { requestAnimationFrame(step); }
		}
		requestAnimationFrame(step);
	}

	/* Marquee: items from data-items="A|B|C" or the element's children; duplicated for a seamless loop. */
	function marquee(el) {
		if (el.dataset.mgmMarq) { return; }
		el.dataset.mgmMarq = 1;
		var items = el.dataset.items ? el.dataset.items.split('|') : null, html = '';
		if (items) {
			html = items.map(function (t) { return '<span class="mgm-marquee-item">' + t.replace(/[<>&]/g, '') + '</span>'; }).join('');
		} else {
			var src = el.querySelector('.text-inner, .col-inner') || el;
			var kids = src.children.length ? Array.prototype.slice.call(src.children) : [src];
			html = kids.map(function (c) { return '<span class="mgm-marquee-item">' + c.innerHTML + '</span>'; }).join('');
			src.textContent = '';
		}
		var track = d.createElement('div');
		track.className = 'mgm-marquee-track';
		track.setAttribute('aria-hidden', 'true');
		track.innerHTML = html + html;
		el.style.setProperty('--mgm-marq', (el.dataset.speed || 30) + 's');
		el.appendChild(track);
	}

	function staggerKids(el) {
		var inner = el.querySelector(':scope > .col-inner, :scope > .row, :scope > .text-inner');
		return Array.prototype.slice.call((inner || el).children);
	}

	function init() {
		w.mgmReady = true;
		/* Auto reveal: Flatsome columns / boxes without touching UX Builder. */
		if (C.auto) {
			$$(C.auto).forEach(function (el) {
				if (el.closest('.mg-no-motion, .mg-reveal, .mg-stagger, .mgm-auto') || el.classList.contains('mg-reveal')) { return; }
				el.classList.add('mgm-auto');
				if (C.variant) { el.classList.add(C.variant); }
				var sib = el.parentElement ? Array.prototype.indexOf.call(el.parentElement.children, el) : 0;
				el.style.setProperty('--mgm-delay', Math.min(sib, 6) * 0.08 + 's');
			});
		}
		$$('.mg-stagger').forEach(function (el) {
			var gap = +el.dataset.stagger || 0.08;
			staggerKids(el).forEach(function (k, i) {
				k.classList.add('mgm-stagger-item');
				k.style.setProperty('--mgm-delay', (i * gap).toFixed(2) + 's');
			});
		});
		$$('.mg-split').forEach(split);
		$$('.mg-marquee').forEach(marquee);
		$$('[data-delay]').forEach(function (el) { el.style.setProperty('--mgm-delay', parseFloat(el.dataset.delay) + 's'); });

		var targets = $$('.mg-reveal, .mg-stagger, .mgm-auto, .mg-split, .mg-count, .mg-underline');
		if (!('IntersectionObserver' in w) || reduce) {
			targets.forEach(function (el) { el.classList.add('is-in'); });
		} else {
			var io = new IntersectionObserver(function (es) {
				es.forEach(function (e) {
					if (!e.isIntersecting) { return; }
					e.target.classList.add('is-in');
					if (e.target.classList.contains('mg-count')) { count(e.target); }
					$$('.mg-count', e.target).forEach(count);
					io.unobserve(e.target);
				});
			}, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });
			targets.forEach(function (el) { io.observe(el); });
		}
		// Safety net: never leave above-the-fold content hidden.
		setTimeout(function () {
			$$('.mg-reveal, .mgm-auto, .mg-stagger').forEach(function (el) {
				if (el.getBoundingClientRect().top < w.innerHeight) { el.classList.add('is-in'); }
			});
		}, 2500);

		/* Scroll-driven: parallax, progress bar, header state. */
		var par = reduce ? [] : $$('.mg-parallax'), prog = null, ticking = false;
		if (C.progress) {
			prog = d.createElement('div');
			prog.className = 'mgm-progress';
			d.body.appendChild(prog);
		}
		function onScroll() {
			ticking = false;
			var y = w.scrollY, vh = w.innerHeight;
			d.body.classList.toggle('mgm-scrolled', y > 40);
			if (prog) { prog.style.setProperty('--mgm-p', Math.min(1, y / Math.max(1, d.documentElement.scrollHeight - vh)).toFixed(4)); }
			par.forEach(function (el) {
				var r = el.getBoundingClientRect();
				if (r.bottom < -200 || r.top > vh + 200) { return; }
				var target = el.querySelector(':scope > .section-bg') || el.querySelector(':scope > .bg') || el;
				target.style.transform = 'translate3d(0,' + ((r.top + r.height / 2 - vh / 2) * -(parseFloat(el.dataset.speed) || 0.2)).toFixed(1) + 'px,0)';
			});
		}
		w.addEventListener('scroll', function () {
			if (!ticking) { ticking = true; requestAnimationFrame(onScroll); }
		}, { passive: true });
		onScroll();

		/* Pointer effects (mouse / trackpad only). */
		if (fine && !reduce) {
			$$('.mg-tilt, .mg-spotlight, .mg-magnetic').forEach(function (el) {
				var max = +(el.dataset.tilt || 8);
				el.addEventListener('pointermove', function (e) {
					var r = el.getBoundingClientRect(), x = (e.clientX - r.left) / r.width, y = (e.clientY - r.top) / r.height;
					el.style.setProperty('--mx', (x * 100).toFixed(1) + '%');
					el.style.setProperty('--my', (y * 100).toFixed(1) + '%');
					if (el.classList.contains('mg-tilt')) {
						el.style.setProperty('--rx', ((x - 0.5) * max).toFixed(2) + 'deg');
						el.style.setProperty('--ry', ((0.5 - y) * max).toFixed(2) + 'deg');
					}
					if (el.classList.contains('mg-magnetic')) {
						el.style.transform = 'translate(' + ((x - 0.5) * 12).toFixed(1) + 'px,' + ((y - 0.5) * 10).toFixed(1) + 'px)';
					}
				});
				el.addEventListener('pointerleave', function () {
					el.style.removeProperty('--rx');
					el.style.removeProperty('--ry');
					if (el.classList.contains('mg-magnetic')) { el.style.transform = ''; }
				});
			});
			if (C.cursor) {
				var g = d.createElement('div');
				g.className = 'mgm-cursor';
				d.body.appendChild(g);
				w.addEventListener('pointermove', function (e) {
					g.style.setProperty('--cx', e.clientX + 'px');
					g.style.setProperty('--cy', e.clientY + 'px');
				}, { passive: true });
			}
		}
	}
	if (d.readyState === 'loading') { d.addEventListener('DOMContentLoaded', init); } else { init(); }
})();
