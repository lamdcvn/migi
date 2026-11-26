// Usage: node tests/engine-parity.js   (needs php on PATH)
// Generates random configs/rooms, runs both engines, compares results.
const { execFileSync } = require('child_process');
const path = require('path');
const E = require('../assets/js/mgmc-engine.js');
let seed = 42; const rnd = () => (seed = (seed * 16807) % 2147483647) / 2147483647;
const pick = a => a[Math.floor(rnd() * a.length)];
const r2 = (a, b, d = 2) => +(a + rnd() * (b - a)).toFixed(d);
const cases = [];
for (let i = 0; i < 3000; i++) {
	const type = pick(['tile', 'panel', 'wallpaper', 'area', 'paint']);
	const c = { type, tile_w: pick([30, 40, 60, 80, 15, 120]), tile_h: pick([30, 60, 80, 90, 120]), joint: pick([0, 2, 3]), per_unit: pick([4, 6, 8, 10, 2, 3]),
		roll_w: pick([0.53, 0.7, 1.06]), roll_l: pick([10, 15.6, 5]), repeat: pick([0, 32, 64]),
		panel_w: pick([40, 60, 16, 122]), panel_l: pick([290, 300, 244, 60]), m2_per_unit: pick([1.8, 2.2, 2.6]),
		coverage: pick([8, 10, 12]), coats: pick([1, 2, 3]), unit_volume: pick([5, 18, 1]),
		waste_straight: pick([0, 5, 7]), waste_diagonal: pick([10, 15]), waste_herringbone: pick([15, 20]), min_reserve_unit: rnd() < 0.3, count: pick(['grid', 'area']) };
	const rooms = Array.from({ length: 1 + Math.floor(rnd() * 4) }, (_, k) => ({ name: 'P' + k, l: r2(0, 9), w: r2(0.5, 7), h: pick([0, 2.6, 3, 3.3, 2.7]), floor: rnd() < 0.7, wall: rnd() < 0.6, ceiling: rnd() < 0.2, openings: pick([0, 1.6, 3.5, 6]) }));
	const opt = { pattern: pick(['straight', 'diagonal', 'herringbone']), reserve: pick([0, 3, 5, 8, 10]) };
	cases.push({ c, rooms, opt, price: pick([0, 250000, 1234567]) });
}
const php = `<?php define('ABSPATH','/'); require $argv[1]; $in=json_decode(stream_get_contents(STDIN),true); $o=[]; foreach($in as $t){ $o[]=MGMC_Engine::calculate($t['c'],$t['rooms'],$t['opt'],$t['price']); } echo json_encode($o);`;
const tmp = path.join(require('os').tmpdir(), 'mgmc-parity.php'); require('fs').writeFileSync(tmp, php);
const phpOut = JSON.parse(execFileSync('php', [tmp, path.join(__dirname, '../includes/class-mgmc-engine.php')], { input: JSON.stringify(cases), maxBuffer: 1 << 28 }));
let fail = 0;
const keys = ['area', 'need', 'reserve', 'total', 'units_need', 'units', 'units_reserve', 'material_m2', 'price', 'price_m2'];
cases.forEach((t, i) => {
	const js = E.calculate(t.c, t.rooms, t.opt, t.price), p = phpOut[i];
	for (const k of keys) { if (Math.abs(js[k] - p[k]) > 1e-6 * Math.max(1, Math.abs(p[k]))) { if (fail++ < 5) console.log('MISMATCH', i, k, js[k], p[k], JSON.stringify(t)); } }
	if (js.rooms.length !== p.rooms.length) { fail++; }
});
console.log(fail ? `FAILED (${fail} mismatches)` : `OK – ${cases.length} cases identical`);
process.exit(fail ? 1 : 0);
