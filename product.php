<?php
require __DIR__ . '/inc.php';
$st = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$st->execute([(int)($_GET['id'] ?? 0)]);
$p = $st->fetch();
if (!$p) { http_response_code(404); exit('Ürün bulunamadı'); }
header_html($p['name']);
if ($p['image']) echo '<img src="uploads/' . e($p['image']) . '" style="max-width:300px" alt="">';
echo '<h2>' . e($p['name']) . '</h2><p>' . nl2br(e($p['description'])) . '</p><h3>' . money($p['price']) . '</h3>';
if ($p['stock'] > 0) { ?>
<h3>Sipariş Ver (IBAN / Havale ile ödeme)</h3>
<form method="post" action="order.php">
<input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
<input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
<input name="customer_name" placeholder="Ad Soyad" required maxlength="255">
<input name="phone" placeholder="Telefon" required maxlength="50">
<textarea name="address" placeholder="Adres" required></textarea>
<button>Siparişi Tamamla</button>
</form>
<?php } else echo '<em>Stokta yok</em>';
echo '</body></html>';
