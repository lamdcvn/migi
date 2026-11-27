<?php
// php tests/totals.php
define( 'ABSPATH', '/' );
require __DIR__ . '/../includes/class-mgq-quote.php';
$ok = true;
function t( $l, $got, $want ) { global $ok; $p = $got === $want || ( is_numeric( $got ) && is_numeric( $want ) && abs( $got - $want ) < 0.001 ); $ok = $ok && $p; echo ( $p ? 'PASS ' : 'FAIL ' ) . $l . ( $p ? '' : " got=$got want=$want" ) . "\n"; }
$lines = array( array( 'qty' => 2, 'price' => 900000, 'list_price' => 1000000 ), array( 'qty' => 1, 'price' => 500000, 'list_price' => 500000 ) );
$r = MGQ_Quote::totals( $lines, array( 'type' => 'percent', 'value' => 10 ), 50000, 8, true );
t( 'subtotal', $r['subtotal'], 2300000 ); t( 'list subtotal', $r['list_subtotal'], 2500000 );
t( 'discount 10%', $r['discount'], 230000 ); t( 'saving = 200k + 230k', $r['saving'], 430000 );
t( 'total incl. VAT = 2070000 + 50000', $r['total'], 2120000 ); t( 'VAT included part', $r['vat'], round( 2120000 - 2120000 / 1.08 ) );
$r = MGQ_Quote::totals( $lines, array( 'type' => 'amount', 'value' => 300000 ), 0, 10, false );
t( 'amount discount', $r['discount'], 300000 ); t( 'VAT added 10% of 2000000', $r['vat'], 200000 ); t( 'total excl→incl', $r['total'], 2200000 );
t( 'discount capped at subtotal', MGQ_Quote::totals( $lines, array( 'type' => 'amount', 'value' => 9e9 ) )['discount'], 2300000 );
t( 'words 0', MGQ_Quote::in_words( 0 ), 'Không đồng' );
t( 'words 1.250.000', MGQ_Quote::in_words( 1250000 ), 'Một triệu hai trăm năm mươi nghìn đồng' );
t( 'words 105', MGQ_Quote::in_words( 105 ), 'Một trăm linh năm đồng' );
t( 'words 21', MGQ_Quote::in_words( 21 ), 'Hai mươi mốt đồng' );
t( 'words 15', MGQ_Quote::in_words( 15 ), 'Mười lăm đồng' );
t( 'words 1.005.000', MGQ_Quote::in_words( 1005000 ), 'Một triệu không trăm linh năm nghìn đồng' );
t( 'words 24.000.000', MGQ_Quote::in_words( 24000000 ), 'Hai mươi tư triệu đồng' );
t( 'words 2.120.000', MGQ_Quote::in_words( 2120000 ), 'Hai triệu một trăm hai mươi nghìn đồng' );
echo $ok ? "ALL OK\n" : "SOME FAILED\n";
