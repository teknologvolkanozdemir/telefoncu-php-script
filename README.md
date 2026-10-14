# Telefoncu PHP Script

PHP + MySQL tabanlı basit cep telefonu satış sitesi. IBAN/havale ile ödeme alır, sınırsız ürün eklenebilir.

## Kurulum
1. MySQL'de `telefoncu` veritabanı oluşturup `schema.sql` dosyasını içe aktarın.
2. `config.php` içinde veritabanı, IBAN ve yönetici bilgilerini düzenleyin.
3. Dosyaları PHP sunucusuna yükleyin (`uploads/` yazılabilir olmalı).
4. Yönetim paneli: `/admin/` (varsayılan: admin / admin123 — değiştirin).

Müşteri sipariş verince IBAN bilgisi gösterilir; ödeme geldiğinde panelden sipariş "Ödendi" yapılır.
