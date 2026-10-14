<?php
require __DIR__ . '/inc.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $addr = trim($_POST['address'] ?? '');
    if ($name === '' || $phone === '' || $addr === '') exit('Tüm alanlar zorunludur.');
    $pdo->beginTransaction();
    $st = $pdo->prepare('SELECT * FROM products WHERE id = ? AND stock > 0 FOR UPDATE');
    $st->execute([(int)($_POST['product_id'] ?? 0)]);
    $p = $st->fetch();
    if (!$p) { $pdo->rollBack(); exit('Ürün bulunamadı veya stokta yok.'); }
    $pdo->prepare('INSERT INTO orders (product_id, customer_name, phone, address, total) VALUES (?,?,?,?,?)')
        ->execute([$p['id'], mb_substr($name, 0, 255), mb_substr($phone, 0, 50), $addr, $p['price']]);
    $id = (int)$pdo->lastInsertId();
    $pdo->prepare('UPDATE products SET stock = stock - 1 WHERE id = ?')->execute([$p['id']]);
    $pdo->commit();
    $_SESSION['last_order'] = $id;
    header('Location: order.php');
    exit;
}
$id = (int)($_SESSION['last_order'] ?? 0);
$st = $pdo->prepare('SELECT o.*, p.name FROM orders o JOIN products p ON p.id = o.product_id WHERE o.id = ?');
$st->execute([$id]);
$o = $st->fetch();
if (!$o) { header('Location: index.php'); exit; }
header_html('Sipariş');
?>
<h2>Siparişiniz alındı (No: <?= (int)$o['id'] ?>)</h2>
<p>Ürün: <?= e($o['name']) ?> — Tutar: <strong><?= money($o['total']) ?></strong></p>
<h3>Ödeme Bilgileri</h3>
<p>Lütfen tutarı aşağıdaki IBAN'a havale/EFT yapın ve açıklamaya <strong>Sipariş No <?= (int)$o['id'] ?></strong> yazın.</p>
<p>Banka: <?= e(BANK_NAME) ?><br>Alıcı: <?= e(IBAN_OWNER) ?><br>IBAN: <strong><?= e(IBAN) ?></strong></p>
<p>Ödemeniz onaylandığında siparişiniz hazırlanacaktır.</p>
</body></html>
