<?php
// php tests/compat.php – compatibility engine scenarios.
define( 'ABSPATH', '/' );
function remove_accents( $s ) { return $s; }
require __DIR__ . '/../includes/class-mgpc-specs.php';
require __DIR__ . '/../includes/class-mgpc-compat.php';
$ok = true;
function t( $label, $cond ) { global $ok; $ok = $ok && $cond; echo ( $cond ? 'PASS ' : 'FAIL ' ) . $label . "\n"; }
function part( $slot, $role, $raw, $qty = 1 ) { return array( 'slot' => $slot, 'role' => $role, 'qty' => $qty, 'name' => $slot, 'specs' => MGPC_Specs::normalize( $raw ) ); }
function levels( $r, $code ) { return array_column( array_filter( $r['checks'], function ( $c ) use ( $code ) { return $c['code'] === $code; } ), 'level' ); }

t( 'normalize socket "LGA 1700" == "lga-1700"', MGPC_Specs::norm_list( 'LGA 1700' ) === MGPC_Specs::norm_list( 'lga-1700' ) );
t( 'normalize Micro-ATX → MATX', MGPC_Specs::norm_list( 'ATX, Micro-ATX, Mini-ITX' ) === array( 'ATX', 'MATX', 'ITX' ) );
t( 'normalize "Socket AM5" → AM5', MGPC_Specs::norm_list( 'Socket AM5' ) === array( 'AM5' ) );
t( 'number from "650W 80 Plus"', 650.0 === MGPC_Specs::norm_num( '650W 80 Plus' ) );

$cpu  = part( 'cpu', 'cpu', array( 'socket' => 'LGA1700', 'ram_type' => 'DDR4,DDR5', 'watt' => 65 ) );
$mb   = part( 'mainboard', 'mainboard', array( 'socket' => 'LGA 1700', 'ram_type' => 'DDR5', 'ram_slots' => 2, 'form' => 'mATX' ) );
$ram  = part( 'ram', 'ram', array( 'ram_type' => 'DDR5', 'ram_sticks' => 2 ) );
$vga  = part( 'vga', 'vga', array( 'watt' => 220, 'gpu_length' => 300 ) );
$case = part( 'case', 'case', array( 'form' => 'ATX, Micro-ATX', 'max_gpu' => 320 ) );
$psu  = part( 'psu', 'psu', array( 'psu_watt' => '650W' ) );
$cool = part( 'cooler', 'cooler', array( 'socket' => 'LGA1700, AM4, AM5' ) );

$r = MGPC_Compat::check( array( $cpu, $mb, $ram, $vga, $case, $psu, $cool ) );
t( 'good build has no errors', ! in_array( 'error', array_column( $r['checks'], 'level' ), true ) );
t( 'draw = 60 + 65 + 220 = 345', 345.0 === (float) $r['draw'] );
t( 'recommended PSU 345×1.3=448.5 → 450', 450 === $r['recommended'] );
t( 'psu ok', levels( $r, 'psu' ) === array( 'ok' ) );

$amd = part( 'mainboard', 'mainboard', array( 'socket' => 'AM5', 'ram_type' => 'DDR5' ) );
t( 'socket mismatch is error', levels( MGPC_Compat::check( array( $cpu, $amd ) ), 'socket' ) === array( 'error' ) );
$ddr4 = part( 'ram', 'ram', array( 'ram_type' => 'DDR4' ) );
t( 'DDR4 on DDR5 board is error', levels( MGPC_Compat::check( array( $mb, $ddr4 ) ), 'ram_type' ) === array( 'error' ) );
$ram2 = part( 'ram', 'ram', array( 'ram_type' => 'DDR5', 'ram_sticks' => 2 ), 2 );
t( '2 kits × 2 sticks on 2-slot board is error', levels( MGPC_Compat::check( array( $mb, $ram2 ) ), 'ram_slots' ) === array( 'error' ) );
$itx = part( 'case', 'case', array( 'form' => 'Mini-ITX', 'max_gpu' => 280 ) );
$r = MGPC_Compat::check( array( $mb, $vga, $itx ) );
t( 'mATX in ITX case is error', levels( $r, 'form' ) === array( 'error' ) );
t( '300mm GPU in 280mm case is error', levels( $r, 'gpu_length' ) === array( 'error' ) );
$small = part( 'psu', 'psu', array( 'psu_watt' => 400 ) );
t( '400W PSU for 345W draw is warn', levels( MGPC_Compat::check( array( $cpu, $vga, $small ) ), 'psu' ) === array( 'warn' ) );
$tiny = part( 'psu', 'psu', array( 'psu_watt' => 300 ) );
t( '300W PSU is error', levels( MGPC_Compat::check( array( $cpu, $vga, $tiny ) ), 'psu' ) === array( 'error' ) );
t( 'F-CPU without VGA warns igpu', levels( MGPC_Compat::check( array( $cpu ) ), 'igpu' ) === array( 'warn' ) );
$badcool = part( 'cooler', 'cooler', array( 'socket' => 'AM4' ) );
t( 'AM4-only cooler on LGA1700 is error', levels( MGPC_Compat::check( array( $cpu, $badcool ) ), 'cooler' ) === array( 'error' ) );
t( 'unknown specs never conflict', ! MGPC_Compat::conflicts( array( $cpu ), part( 'mainboard', 'mainboard', array() ) ) );
t( 'candidate AM5 board conflicts with LGA1700 CPU', count( MGPC_Compat::conflicts( array( $cpu, $mb ), $amd ) ) === 1 );
t( 'candidate replaces same slot (no self conflict)', ! MGPC_Compat::conflicts( array( $cpu, $amd ), $mb ) );
t( 'existing conflict not blamed on unrelated candidate', ! MGPC_Compat::conflicts( array( $cpu, $amd ), $ram ) );
t( 'PSU candidate too small conflicts', count( MGPC_Compat::conflicts( array( $cpu, $vga ), $tiny ) ) === 1 );
echo $ok ? "ALL OK\n" : "SOME FAILED\n";
