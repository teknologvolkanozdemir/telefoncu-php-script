<?php
require __DIR__ . '/inc.php';
header_html(SITE_NAME);
$products = $pdo->query('SELECT * FROM products ORDER BY id DESC')->fetchAll();
echo '<div class="grid">';
foreach ($products as $p) {
    echo '<div class="card">';
    if ($p['image']) echo '<img src="uploads/' . e($p['image']) . '" alt="">';
    echo '<h3>' . e($p['name']) . '</h3><p>' . money($p['price']) . '</p>';
    echo $p['stock'] > 0 ? '<a class="btn" href="product.php?id=' . (int)$p['id'] . '">İncele</a>' : '<em>Stokta yok</em>';
    echo '</div>';
}
if (!$products) echo '<p>Henüz ürün yok.</p>';
echo '</div></body></html>';
