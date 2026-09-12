<div class="col-md-8"><label class="small muted">Title</label><input class="form-control" name="title" value="<?= e(
    $pkg['title'] ?? '',
) ?>" required></div>
<div class="col-md-4"><label class="small muted">Price (LKR)</label><input class="form-control" type="number"
        step="0.01" min="0" name="price" value="<?= e(
            $pkg['price'] ?? '',
        ) ?>" required></div>
<div class="col-md-7"><label class="small muted">Destination</label><input class="form-control" name="destination"
        value="<?= e(
            $pkg['destination'] ?? '',
        ) ?>" required></div>
<div class="col-md-2"><label class="small muted">Days</label><input class="form-control" type="number" min="1"
        name="duration_days" value="<?= e(
            $pkg['duration_days'] ?? 1,
        ) ?>" required></div>
<div class="col-md-3"><label class="small muted">Status</label><select class="form-select" name="status"><?php foreach (
    ['draft', 'active', 'inactive']
    as $v
): ?>
            <option value="<?= $v ?>" <?= ($pkg['status'] ?? 'draft') === $v
                  ? 'selected'
                  : '' ?>><?= $v ?></option>
        <?php endforeach; ?>
    </select></div>
<div class="col-12"><label class="small muted">Image URL</label><input class="form-control" name="image_url" value="<?= e(
    $pkg['image_url'] ?? '',
) ?>" placeholder="https://..."></div>
<div class="col-12"><label class="small muted">Short description</label><input class="form-control"
        name="short_description" value="<?= e(
            $pkg['short_description'] ?? '',
        ) ?>"></div>
<div class="col-12"><label class="small muted">Full description</label><textarea class="form-control" name="description"
        rows="3"><?= e(
            $pkg['description'] ?? '',
        ) ?></textarea></div>
<div class="col-md-6"><label class="small muted">Activities</label><textarea class="form-control" name="activities"
        rows="2"><?= e(
            $pkg['activities'] ?? '',
        ) ?></textarea></div>
<div class="col-md-3"><label class="small muted">Inclusions</label><textarea class="form-control" name="inclusions"
        rows="2"><?= e(
            $pkg['inclusions'] ?? '',
        ) ?></textarea></div>
<div class="col-md-3"><label class="small muted">Exclusions</label><textarea class="form-control" name="exclusions"
        rows="2"><?= e(
            $pkg['exclusions'] ?? '',
        ) ?></textarea></div>