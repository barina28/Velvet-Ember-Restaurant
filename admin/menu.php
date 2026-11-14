<?php
require_once __DIR__ . '/../includes/config.php';
require_login(['admin', 'manager']);

function upload_image(): ?string {
    if (empty($_FILES['image']['name']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) return null;
    if ($_FILES['image']['size'] > 3 * 1024 * 1024) { flash('Image is larger than 3 MB.', 'danger'); return null; }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['image']['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if (!$ext) { flash('Only JPG, PNG or WEBP images are allowed.', 'danger'); return null; }
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    $dir = dirname(__DIR__) . '/assets/img/picture/';
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $dir . $name)) { flash('Could not save the image (folder permissions?).', 'danger'); return null; }
    return $name;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $a = $_POST['action'] ?? '';
    if ($a === 'add_cat' && trim($_POST['name'] ?? '') !== '') {
        q('INSERT INTO categories (name, sort_order) VALUES (?, ?)', [trim($_POST['name']), (int)($_POST['sort_order'] ?? 0)]);
        flash('Category added.');
    } elseif ($a === 'del_cat') {
        q('DELETE FROM categories WHERE id = ?', [(int)$_POST['id']]);
        flash('Category and its items deleted.');
    } elseif ($a === 'save_item') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? ''); $price = (float)($_POST['price'] ?? 0);
        $cat = (int)($_POST['category_id'] ?? 0);
        if ($name === '' || $price <= 0 || !$cat) { flash('Name, category and a price above 0 are required.', 'danger'); }
        else {
            $desc = mb_substr(trim($_POST['description'] ?? ''), 0, 250);
            $avail = isset($_POST['available']) ? 1 : 0;
            $img = upload_image();
            if ($id) {
                q('UPDATE menu_items SET category_id=?, name=?, description=?, price=?, available=? WHERE id=?', [$cat, $name, $desc, $price, $avail, $id]);
                if ($img) q('UPDATE menu_items SET image=? WHERE id=?', [$img, $id]);
                flash('Item updated.');
            } else {
                q('INSERT INTO menu_items (category_id, name, description, price, image, available) VALUES (?,?,?,?,?,?)', [$cat, $name, $desc, $price, $img ?? '', $avail]);
                flash('Item added.');
            }
        }
    } elseif ($a === 'toggle') {
        q('UPDATE menu_items SET available = 1 - available WHERE id = ?', [(int)$_POST['id']]);
    } elseif ($a === 'del_item') {
        q('DELETE FROM menu_items WHERE id = ?', [(int)$_POST['id']]);
        flash('Item deleted.');
    }
    redirect('admin/menu.php');
}

$title = 'Menu'; $active = 'menu';
require __DIR__ . '/../includes/admin_header.php';

$cats = q('SELECT * FROM categories ORDER BY sort_order, id')->fetchAll();
$items = q('SELECT i.*, c.name cat FROM menu_items i JOIN categories c ON c.id = i.category_id ORDER BY c.sort_order, i.id')->fetchAll();
$edit = null;
if (!empty($_GET['edit'])) $edit = q('SELECT * FROM menu_items WHERE id = ?', [(int)$_GET['edit']])->fetch() ?: null;
?>
<h1 class="brand-font h3 mb-4">Menu management</h1>
<div class="row g-4">
  <div class="col-lg-4">
    <div class="card-dark p-3 mb-4">
      <h2 class="h6 mb-3"><?= $edit ? 'Edit item' : 'Add new item' ?></h2>
      <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?><input type="hidden" name="action" value="save_item"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
        <div class="mb-2"><input class="form-control" name="name" placeholder="Item name" required value="<?= e($edit['name'] ?? '') ?>"></div>
        <div class="mb-2"><select class="form-select" name="category_id" required>
          <option value="">Category...</option>
          <?php foreach ($cats as $c): ?><option value="<?= (int)$c['id'] ?>" <?= ($edit['category_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
        </select></div>
        <div class="mb-2"><input class="form-control" type="number" name="price" step="1" min="1" placeholder="Price" required value="<?= e($edit['price'] ?? '') ?>"></div>
        <div class="mb-2"><textarea class="form-control" name="description" rows="2" placeholder="Short description"><?= e($edit['description'] ?? '') ?></textarea></div>
        <div class="mb-2"><label class="small text-muted">Photo (JPG/PNG/WEBP, max 3 MB)</label><input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp"></div>
        <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="available" id="av" <?= !$edit || $edit['available'] ? 'checked' : '' ?>><label class="form-check-label" for="av">Available</label></div>
        <button class="btn btn-burg w-100"><?= $edit ? 'Save changes' : 'Add item' ?></button>
        <?php if ($edit): ?><a href="menu.php" class="btn btn-link w-100">Cancel</a><?php endif; ?>
      </form>
    </div>
    <div class="card-dark p-3">
      <h2 class="h6 mb-3">Categories</h2>
      <?php foreach ($cats as $c): ?>
        <form method="post" class="d-flex justify-content-between align-items-center mb-2" onsubmit="return confirm('Delete this category and ALL its items?')">
          <?= csrf_field() ?><input type="hidden" name="action" value="del_cat"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
          <span><?= e($c['name']) ?></span><button class="btn btn-sm btn-outline-danger">Delete</button>
        </form>
      <?php endforeach; ?>
      <form method="post" class="d-flex gap-2 mt-3">
        <?= csrf_field() ?><input type="hidden" name="action" value="add_cat">
        <input class="form-control form-control-sm" name="name" placeholder="New category" required>
        <input class="form-control form-control-sm" name="sort_order" type="number" placeholder="#" style="width:70px">
        <button class="btn btn-sm btn-burg">Add</button>
      </form>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="table-responsive"><table class="table table-dark-custom table-striped align-middle">
      <thead><tr><th></th><th>Item</th><th>Category</th><th class="text-end">Price</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($items as $i): ?>
        <tr>
          <td><?php if ($i['image']): ?><img class="menu-thumb" src="<?= BASE ?>/assets/img/picture/<?= e(rawurlencode($i['image'])) ?>" alt=""><?php endif; ?></td>
          <td><?= e($i['name']) ?></td><td class="small text-muted"><?= e($i['cat']) ?></td><td class="text-end"><?= money($i['price']) ?></td>
          <td><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$i['id'] ?>">
            <button class="btn btn-sm <?= $i['available'] ? 'btn-success' : 'btn-secondary' ?>"><?= $i['available'] ? 'Available' : 'Hidden' ?></button></form></td>
          <td class="text-end text-nowrap">
            <a class="btn btn-sm btn-outline-light" href="menu.php?edit=<?= (int)$i['id'] ?>">Edit</a>
            <form method="post" class="d-inline" onsubmit="return confirm('Delete this item?')"><?= csrf_field() ?><input type="hidden" name="action" value="del_item"><input type="hidden" name="id" value="<?= (int)$i['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody></table></div>
  </div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
