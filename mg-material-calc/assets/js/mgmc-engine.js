/* Mirror of includes/class-mgmc-engine.php – keep both in sync (tests/engine-parity.js checks them). */
(function (root) {
	'use strict';
	var E = 1e-9;
	function f(v) { var n = parseFloat(String(v === undefined || v === null ? '' : v).replace(',', '.')); return n > 0 ? n : 0; }

	function grid(l, w, a, b) {
		if (l <= 0 || w <= 0 || a <= 0 || b <= 0) { return 0; }
		return Math.min(Math.ceil(l / a - E) * Math.ceil(w / b - E), Math.ceil(l / b - E) * Math.ceil(w / a - E));
	}
	function patternWaste(c, p) {
		var k = 'waste_' + (p === 'diagonal' || p === 'herringbone' ? p : 'straight');
		return c[k] !== undefined ? f(c[k]) / 100 : 0;
	}

	function room(c, r, pattern) {
		var l = f(r.l), w = f(r.w), h = f(r.h), open = f(r.openings);
		var floor = !!r.floor, wall = !!r.wall && h > 0, ceil = !!r.ceiling;
		var perim = 2 * (l + w);
		if (c.type === 'perimeter') {
			var runs = (floor ? 1 : 0) + (r.wall ? 1 : 0) + (ceil ? 1 : 0), piece = f(c.piece_l) / 100;
			return { name: r.name || '', l: l, w: w, h: h, area: runs * perim, need: piece > 0 && l > 0 && w > 0 ? runs * Math.ceil(perim / piece - E) : 0 };
		}
		var wallArea = wall ? Math.max(0, perim * h - open) : 0;
		var flatArea = (floor ? l * w : 0) + (ceil ? l * w : 0);
		var area = flatArea + wallArea, need = 0, flats = (floor ? 1 : 0) + (ceil ? 1 : 0);
		var diag = pattern !== 'straight', a, b;

		switch (c.type) {
			case 'tile':
				a = f(c.tile_w) / 100 + f(c.joint) / 1000;
				b = f(c.tile_h) / 100 + f(c.joint) / 1000;
				if (a <= 0 || b <= 0) { break; }
				if (diag || c.count === 'area') {
					need = area > 0 ? Math.ceil(area / (a * b) * (1 + patternWaste(c, pattern)) - E) : 0;
				} else {
					need = flats * grid(l, w, a, b);
					if (wall) { need += Math.max(0, grid(perim, h, a, b) - Math.floor(open / (a * b) + E)); }
				}
				break;
			case 'panel':
				a = f(c.panel_w) / 100; b = f(c.panel_l) / 100;
				if (a <= 0 || b <= 0) { break; }
				need = flats * grid(l, w, a, b);
				if (wall) { need += Math.ceil(Math.max(0, perim - open / h) / a - E) * Math.ceil(h / b - E); }
				break;
			case 'wallpaper':
				var rw = f(c.roll_w), rl = f(c.roll_l), rep = f(c.repeat) / 100, frac = 0, strip, perRoll, strips;
				if (rw <= 0 || rl <= 0) { break; }
				if (wall) {
					strip = h + rep + 0.05;
					perRoll = Math.max(1, Math.floor(rl / strip + E));
					strips = Math.ceil(Math.max(0, perim - open / h) / rw - E);
					frac += strips / perRoll;
				}
				if (flats) {
					strip = Math.max(l, w) + rep + 0.05;
					perRoll = strip > rl ? 0 : Math.max(1, Math.floor(rl / strip + E));
					strips = Math.ceil(Math.min(l, w) / rw - E) * flats;
					frac += perRoll ? strips / perRoll : (flatArea * 1.15) / (rw * rl);
				}
				need = frac;
				break;
			case 'consumable':
				need = area * f(c.rate);
				break;
			case 'paint':
				var cov = f(c.coverage);
				need = cov > 0 ? area * Math.max(1, f(c.coats)) / cov : 0;
				break;
			default:
				need = area * (1 + patternWaste(c, pattern));
		}
		return { name: r.name || '', l: l, w: w, h: h, area: area, need: need };
	}

	function perUnit(c) {
		switch (c.type) {
			case 'tile': case 'panel': case 'perimeter': return Math.max(1, Math.floor(f(c.per_unit)));
			case 'consumable': return Math.max(0.01, f(c.unit_size));
			case 'paint': return Math.max(0.1, f(c.unit_volume));
			case 'wallpaper': return 1;
			default: return Math.max(0.01, f(c.m2_per_unit));
		}
	}
	function unitM2(c) {
		switch (c.type) {
			case 'tile': return f(c.tile_w) * f(c.tile_h) / 10000 * perUnit(c);
			case 'panel': return f(c.panel_w) * f(c.panel_l) / 10000 * perUnit(c);
			case 'wallpaper': return f(c.roll_w) * f(c.roll_l);
			case 'paint': return f(c.unit_volume) * f(c.coverage) / Math.max(1, f(c.coats));
			case 'consumable': return f(c.rate) > 0 ? f(c.unit_size) / f(c.rate) : 0;
			case 'perimeter': return 0;
			default: return f(c.m2_per_unit);
		}
	}

	function calculate(c, rooms, opt, unitPrice) {
		var pattern = (opt.pattern === 'diagonal' || opt.pattern === 'herringbone') && (c.type === 'tile' || c.type === 'area') ? opt.pattern : 'straight';
		var reserve = Math.min(50, f(opt.reserve)) / 100, out = [], area = 0, need = 0;
		rooms.slice(0, 50).forEach(function (r) {
			var res = room(c, r, pattern);
			if (res.area <= 0) { return; }
			out.push(res); area += res.area; need += res.need;
		});
		var per = perUnit(c), discrete = c.type === 'tile' || c.type === 'panel' || c.type === 'perimeter';
		var needQ = discrete ? Math.ceil(need - E) : need;
		var reserveQ = discrete ? Math.ceil(needQ * reserve - E) : needQ * reserve;
		var total = needQ + reserveQ;
		var unitsNeed = needQ > 0 ? Math.ceil(needQ / per - E) : 0;
		var units = total > 0 ? Math.ceil(total / per - E) : 0;
		if (c.min_reserve_unit && units > 0 && units === unitsNeed && reserve > 0) { units++; }
		unitPrice = +unitPrice || 0;
		return {
			pattern: pattern, reserve_pct: reserve * 100, rooms: out, area: area,
			need: needQ, reserve: reserveQ, total: total,
			units_need: unitsNeed, units: units, units_reserve: units - unitsNeed,
			material_m2: units * unitM2(c), price: units * unitPrice,
			price_m2: area > 0 ? units * unitPrice / area : 0
		};
	}

	var api = { calculate: calculate, perUnit: perUnit, unitM2: unitM2 };
	if (typeof module !== 'undefined' && module.exports) { module.exports = api; } else { root.MGMCEngine = api; }
})(this);
