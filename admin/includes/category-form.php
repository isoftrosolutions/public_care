<?php
$form_category = $form_category ?? [];
$submit_label = $submit_label ?? 'Save Category';
?>
<form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-5">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Name <span class="text-error">*</span></label>
<input type="text" name="name" required value="<?= htmlspecialchars($form_category['name'] ?? '') ?>" oninput="document.getElementById('cat-slug').value = this.value.toLowerCase().replace(/[^a-z0-9-]+/g,'-').replace(/^-+|-+$/g,'')" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-1">Slug <span class="text-error">*</span></label>
<input type="text" name="slug" id="cat-slug" required value="<?= htmlspecialchars($form_category['slug'] ?? '') ?>" class="w-full bg-surface border border-outline-variant text-label-sm rounded-lg focus:ring-primary px-3 py-2.5">
</div>

<div>
<label class="text-label-sm text-on-surface-variant uppercase tracking-widest block mb-2">Category Image</label>
<label class="image-dropzone group flex flex-col items-center gap-3 rounded-xl border-2 border-dashed border-outline-variant bg-surface p-4 cursor-pointer hover:border-primary transition-colors">
<input type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif" class="sr-only image-input">
<span class="w-16 h-16 rounded-lg bg-surface-container-high flex items-center justify-center overflow-hidden shrink-0">
<?php if (!empty($form_category['image_url'])): ?>
<img src="<?= htmlspecialchars($form_category['image_url']) ?>" alt="" class="image-preview w-full h-full object-cover">
<?php else: ?>
<span class="image-placeholder material-symbols-outlined text-2xl text-on-surface-variant">upload_file</span>
<img src="" alt="" class="image-preview hidden w-full h-full object-cover">
<?php endif; ?>
</span>
<span class="text-label-sm text-primary uppercase tracking-widest">Drag & drop or click</span>
<span class="text-label-sm text-on-surface-variant">Max 5MB, JPG/PNG/WEBP/GIF</span>
</label>
</div>

<div class="md:col-span-3 flex gap-3 pt-2">
<button type="submit" class="bg-primary text-on-primary px-6 py-2.5 rounded-lg text-label-sm hover:opacity-90 transition-opacity"><?= htmlspecialchars($submit_label) ?></button>
<a href="categories.php" class="border border-outline-variant text-on-surface-variant px-6 py-2.5 rounded-lg text-label-sm hover:bg-surface-container-high transition-colors">Cancel</a>
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
