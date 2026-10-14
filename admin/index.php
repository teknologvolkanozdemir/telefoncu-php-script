<?php
require __DIR__ . '/../inc.php';
if (isset($_GET['logout'])) { session_destroy(); header('Location: index.php'); exit; }
if (empty($_SESSION['admin'])) {
    $err = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        if (hash_equals(ADMIN_USER, $_POST['user'] ?? '') && hash_equals(ADMIN_PASS, $_POST['pass'] ?? '')) {
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            header('Location: index.php'); exit;
        }
        $err = 'Hatalı giriş';
    }
    header_html('Yönetim', '../');
    echo '<form method="post" style="max-width:300px"><p style="color:red">' . e($err) . '</p><input type="hidden" name="csrf" value="' . e(csrf()) . '">'
       . '<input name="user" placeholder="Kullanıcı"><input type="password" name="pass" placeholder="Şifre"><button>Giriş</button></form></body></html>';
    exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = $_POST['action'] ?? '';
    if ($act === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $image = null;
        if (!empty($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
            $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'][(new finfo(FILEINFO_MIME_TYPE))->file($_FILES['image']['tmp_name'])] ?? null;
            if ($ext) {
                $image = bin2hex(random_bytes(8)) . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../uploads/' . $image);
            }
        }
        $vals = [trim($_POST['name'] ?? ''), $_POST['description'] ?? '', max(0, (float)str_replace(',', '.', $_POST['price'] ?? 0)), max(0, (int)($_POST['stock'] ?? 0))];
        if ($vals[0] !== '') {
            if ($id) {
                $pdo->prepare('UPDATE products SET name=?, description=?, price=?, stock=? WHERE id=?')->execute([...$vals, $id]);
                if ($image) $pdo->prepare('UPDATE products SET image=? WHERE id=?')->execute([$image, $id]);
            } else {
                $pdo->prepare('INSERT INTO products (name, description, price, stock, image) VALUES (?,?,?,?,?)')->execute([...$vals, $image]);
            }
            $msg = 'Kaydedildi';
        }
    } elseif ($act === 'delete') {
        try { $pdo->prepare('DELETE FROM products WHERE id=?')->execute([(int)$_POST['id']]); $msg = 'Silindi'; }
        catch (PDOException $ex) { $msg = 'Siparişi olan ürün silinemez (stoğu 0 yapın).'; }
    } elseif ($act === 'status' && in_array($_POST['status'] ?? '', ['bekliyor', 'odendi', 'iptal'], true)) {
        $pdo->prepare('UPDATE orders SET status=? WHERE id=?')->execute([$_POST['status'], (int)$_POST['id']]);
    }
}
$edit = ['id' => 0, 'name' => '', 'description' => '', 'price' => '', 'stock' => 1];
if (isset($_GET['edit'])) {
    $st = $pdo->prepare('SELECT * FROM products WHERE id=?'); $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch() ?: $edit;
}
header_html('Yönetim', '../');
echo '<p><a href="?logout=1">Çıkış</a> ' . e($msg) . '</p>';
?>
<h2><?= $edit['id'] ? 'Ürünü Düzenle' : 'Yeni Ürün Ekle' ?></h2>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>">
<input name="name" placeholder="Telefon adı" required value="<?= e($edit['name']) ?>">
<textarea name="description" placeholder="Açıklama"><?= e($edit['description']) ?></textarea>
<input name="price" placeholder="Fiyat" required value="<?= e($edit['price']) ?>">
<input type="number" name="stock" placeholder="Stok" value="<?= e($edit['stock']) ?>">
<input type="file" name="image" accept="image/*">
<button>Kaydet</button>
</form>
<h2>Ürünler</h2>
<table><tr><th>Ad</th><th>Fiyat</th><th>Stok</th><th></th></tr>
<?php foreach ($pdo->query('SELECT * FROM products ORDER BY id DESC') as $p): ?>
<tr><td><?= e($p['name']) ?></td><td><?= money($p['price']) ?></td><td><?= (int)$p['stock'] ?></td><td>
<a href="?edit=<?= (int)$p['id'] ?>">Düzenle</a>
<form method="post" style="display:inline" onsubmit="return confirm('Silinsin mi?')"><input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><button>Sil</button></form></td></tr>
<?php endforeach; ?>
</table>
<h2>Siparişler</h2>
<table><tr><th>No</th><th>Ürün</th><th>Müşteri</th><th>Telefon</th><th>Adres</th><th>Tutar</th><th>Durum</th></tr>
<?php foreach ($pdo->query('SELECT o.*, p.name FROM orders o JOIN products p ON p.id=o.product_id ORDER BY o.id DESC') as $o): ?>
<tr><td><?= (int)$o['id'] ?></td><td><?= e($o['name']) ?></td><td><?= e($o['customer_name']) ?></td><td><?= e($o['phone']) ?></td><td><?= nl2br(e($o['address'])) ?></td><td><?= money($o['total']) ?></td>
<td><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
<select name="status" onchange="this.form.submit()"><?php foreach (['bekliyor' => 'Ödeme bekliyor', 'odendi' => 'Ödendi', 'iptal' => 'İptal'] as $k => $v) echo '<option value="' . $k . '"' . ($o['status'] === $k ? ' selected' : '') . '>' . $v . '</option>'; ?></select></form></td></tr>
<?php endforeach; ?>
</table>
</body></html>
