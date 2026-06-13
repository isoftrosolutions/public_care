<?php
$form_product = $form_product ?? [];
$submit_label = $submit_label ?? 'Save Product';
$form_action = $form_action ?? '';
?>
<form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-5">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Product Name <span class="text-error">*</span></label>
<input type="text" name="name" required value="<?= htmlspecialchars($form_product['name'] ?? '') ?>" oninput="document.getElementById('slug-input').value = this.value.toLowerCase().replace(/[^a-z0-9-]+/g,'-').replace(/^-+|-+$/g,'')" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Slug</label>
<input type="text" name="slug" id="slug-input" value="<?= htmlspecialchars($form_product['slug'] ?? '') ?>" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Category</label>
<select name="category_id" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
<option value="">No Category</option>
<?php foreach ($categories as $cat): ?>
<option value="<?= (int)$cat['id'] ?>" <?= ((int)($form_product['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
<?php endforeach; ?>
</select>
</div>

<div class="md:col-span-2 lg:col-span-3">
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Description</label>
<textarea name="description" rows="4" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5"><?= htmlspecialchars($form_product['description'] ?? '') ?></textarea>
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Price (Rs.) <span class="text-error">*</span></label>
<input type="number" name="price" step="0.01" min="0" required value="<?= htmlspecialchars($form_product['price'] ?? '') ?>" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Compare Price (Rs.)</label>
<input type="number" name="compare_price" step="0.01" min="0" value="<?= htmlspecialchars($form_product['compare_price'] ?? '') ?>" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Stock</label>
<input type="number" name="stock" min="0" value="<?= (int)($form_product['stock'] ?? 0) ?>" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
</div>

<div class="md:col-span-2 lg:col-span-3">
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-2">Product Image</label>
<label class="image-dropzone group flex flex-col md:flex-row items-center gap-5 rounded-xl border-2 border-dashed border-outline-variant bg-surface p-5 cursor-pointer hover:border-primary transition-colors">
<input type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif" class="sr-only image-input">
<span class="w-20 h-20 rounded-lg bg-surface-container-high flex items-center justify-center overflow-hidden shrink-0">
<?php if (!empty($form_product['image_url'])): ?>
<img src="<?= htmlspecialchars($form_product['image_url']) ?>" alt="" class="image-preview w-full h-full object-cover">
<?php else: ?>
<span class="image-placeholder material-symbols-outlined text-3xl text-on-surface-variant">upload_file</span>
<img src="" alt="" class="image-preview hidden w-full h-full object-cover">
<?php endif; ?>
</span>
<span>
<span class="block text-label-sm text-primary uppercase tracking-widest">Drag and drop image here</span>
<span class="block text-body-md text-on-surface mt-1">or click to choose a JPG, PNG, WEBP, or GIF file.</span>
<span class="block text-label-sm text-on-surface-variant mt-1">Maximum size: 5MB. Image URLs are not accepted here.</span>
</span>
</label>
</div>

<div class="flex items-center gap-3 pt-6">
<input type="hidden" name="is_bestseller" value="0">
<input type="checkbox" name="is_bestseller" value="1" id="is_bestseller" <?= ($form_product['is_bestseller'] ?? 0) ? 'checked' : '' ?> class="rounded border-outline-variant text-primary focus:ring-primary">
<label for="is_bestseller" class="text-label-sm text-on-surface-variant">Mark as Bestseller</label>
</div>

<div class="md:col-span-2 lg:col-span-3 flex gap-3 pt-2">
<button type="submit" class="bg-primary text-on-primary px-6 py-2.5 rounded-lg text-label-sm hover:opacity-90 transition-opacity"><?= htmlspecialchars($submit_label) ?></button>
<a href="products.php" class="border border-outline-variant text-on-surface-variant px-6 py-2.5 rounded-lg text-label-sm hover:bg-surface-container-high transition-colors">Cancel</a>
</div>
</form>
<script>
document.querySelectorAll('.image-dropzone').forEach(function(zone) {
    var input = zone.querySelector('.image-input');
    var preview = zone.querySelector('.image-preview');
    var placeholder = zone.querySelector('.image-placeholder');
    function showPreview(file) {
        if (!file || !file.type.startsWith('image/')) return;
        var reader = new FileReader();
        reader.onload = function(event) {
            preview.src = event.target.result;
            preview.classList.remove('hidden');
            if (placeholder) placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }
    input.addEventListener('change', function() { showPreview(input.files[0]); });
    ['dragenter', 'dragover'].forEach(function(type) {
        zone.addEventListener(type, function(event) {
            event.preventDefault();
            zone.classList.add('border-primary', 'bg-primary-fixed');
        });
    });
    ['dragleave', 'drop'].forEach(function(type) {
        zone.addEventListener(type, function(event) {
            event.preventDefault();
            zone.classList.remove('border-primary', 'bg-primary-fixed');
        });
    });
    zone.addEventListener('drop', function(event) {
        if (event.dataTransfer.files.length) {
            input.files = event.dataTransfer.files;
            showPreview(input.files[0]);
        }
    });
});
</script>
