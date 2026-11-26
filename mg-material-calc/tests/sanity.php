<?php
// php tests/sanity.php – hand-checked scenarios.
define( 'ABSPATH', '/' );
require __DIR__ . '/../includes/class-mgmc-engine.php';
$ok = true;
function check( $label, $got, $want ) { global $ok; $pass = abs( $got - $want ) < 1e-6; $ok = $ok && $pass; printf( "%s %-60s got %s want %s\n", $pass ? 'PASS' : 'FAIL', $label, $got, $want ); }
// Floor 4×5 m, tile 60×60 no joint: 7 × 9 = 63 tiles (cuts counted), box of 4 → 16 boxes; reserve 5% → 63+4=67 → 17 boxes.
$tile = array( 'type' => 'tile', 'tile_w' => 60, 'tile_h' => 60, 'joint' => 0, 'per_unit' => 4, 'waste_diagonal' => 15, 'waste_herringbone' => 20 );
$r = MGMC_Engine::calculate( $tile, array( array( 'l' => 4, 'w' => 5, 'floor' => 1 ) ), array( 'pattern' => 'straight', 'reserve' => 5 ), 500000 );
check( 'tile straight need pieces', $r['need'], 63 ); check( 'tile reserve pieces', $r['reserve'], 4 ); check( 'tile boxes', $r['units'], 17 ); check( 'tile price', $r['price'], 8500000 );
check( 'tile area', $r['area'], 20 ); check( 'material m2', $r['material_m2'], 17 * 1.44 );
// Diagonal: 20 / 0.36 * 1.15 = 63.9 → 64.
$r = MGMC_Engine::calculate( $tile, array( array( 'l' => 4, 'w' => 5, 'floor' => 1 ) ), array( 'pattern' => 'diagonal', 'reserve' => 0 ) );
check( 'tile diagonal pieces', $r['need'], 64 );
// Wallpaper: room 4×3, h 2.7, door+window 3.5 m², roll 0.53×10, no repeat → strip 2.75 → 3/roll; net perimeter 14-3.5/2.7=12.70 → 24 strips → 8 rolls; reserve 10% → 8.8 → 9.
$wp = array( 'type' => 'wallpaper', 'roll_w' => 0.53, 'roll_l' => 10, 'repeat' => 0 );
$r = MGMC_Engine::calculate( $wp, array( array( 'l' => 4, 'w' => 3, 'h' => 2.7, 'wall' => 1, 'openings' => 3.5 ) ), array( 'reserve' => 10 ) );
check( 'wallpaper rolls need', $r['units_need'], 8 ); check( 'wallpaper rolls total', $r['units'], 9 );
// Two rooms add up: 2 × 63 tiles.
$r = MGMC_Engine::calculate( $tile, array( array( 'l' => 4, 'w' => 5, 'floor' => 1 ), array( 'l' => 5, 'w' => 4, 'floor' => 1 ) ), array( 'reserve' => 0 ) );
check( 'two rooms pieces', $r['need'], 126 );
// Paint 4×5 walls h3 (54 m²) 2 coats 10 m²/L → 10.8 L; 5 L cans, 5% → 11.34 L → 3 cans.
$paint = array( 'type' => 'paint', 'coverage' => 10, 'coats' => 2, 'unit_volume' => 5 );
$r = MGMC_Engine::calculate( $paint, array( array( 'l' => 4, 'w' => 5, 'h' => 3, 'wall' => 1 ) ), array( 'reserve' => 5 ) );
check( 'paint cans', $r['units'], 3 );
// Wall panel 40×290 cm, room 3×3 h2.8 walls, no openings: 12/0.4 = 30 columns × 1 row → 30 panels.
$panel = array( 'type' => 'panel', 'panel_w' => 40, 'panel_l' => 290, 'per_unit' => 1 );
$r = MGMC_Engine::calculate( $panel, array( array( 'l' => 3, 'w' => 3, 'h' => 2.8, 'wall' => 1 ) ), array( 'reserve' => 0 ) );
check( 'wall panels', $r['units'], 30 );
// Laminate 2.4 m²/box, 20 m² straight waste 5% = 21 m² → 9 boxes.
$lam = array( 'type' => 'area', 'm2_per_unit' => 2.4, 'waste_straight' => 5 );
$r = MGMC_Engine::calculate( $lam, array( array( 'l' => 4, 'w' => 5, 'floor' => 1 ) ), array( 'reserve' => 0 ) );
check( 'laminate boxes', $r['units'], 9 );
echo $ok ? "ALL OK\n" : "SOME FAILED\n";
