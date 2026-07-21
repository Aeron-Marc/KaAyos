<?php $__env->startSection('title', $worker->name); ?>
<?php $__env->startSection('page_title', $worker->name); ?>

<?php $__env->startSection('topbar_actions'); ?>
    <a href="<?php echo e(route('client.workers')); ?>" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to Search
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="worker-profile-layout">
    
    <div class="card-panel" style="flex:0 0 340px;align-self:start;">
        <div style="text-align:center;padding:8px 0;">
            <?php if($worker->avatar): ?>
                <img src="<?php echo e(Storage::url($worker->avatar)); ?>" alt="<?php echo e($worker->name); ?>"
                     style="width:96px;height:96px;border-radius:50%;object-fit:cover;border:3px solid var(--b2);">
            <?php else: ?>
                <div style="width:96px;height:96px;border-radius:50%;background:var(--b0);color:var(--b6);display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:700;margin:0 auto;">
                    <?php echo e(strtoupper(substr($worker->first_name, 0, 1) . substr($worker->last_name, 0, 1))); ?>

                </div>
            <?php endif; ?>
            <h2 style="margin-top:14px;font-size:1.15rem;"><?php echo e($worker->name); ?></h2>
            <p style="color:var(--b6);font-weight:500;font-size:.9rem;"><?php echo e($worker->service_category ?? 'General'); ?></p>

            <?php if($workerProfile && $workerProfile->average_rating): ?>
                <div style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:6px;">
                    <i class="fa-solid fa-star" style="color:#f59e0b;" aria-hidden="true"></i>
                    <span style="font-weight:600;"><?php echo e(number_format($workerProfile->average_rating, 1)); ?></span>
                    <span style="color:var(--g4);font-size:.82rem;">(<?php echo e($reviews->count()); ?> reviews)</span>
                </div>
            <?php endif; ?>

            <?php if($workerProfile && $workerProfile->government_id_verified): ?>
                <div style="margin-top:10px;">
                    <span style="display:inline-flex;align-items:center;gap:4px;background:#dcfce7;color:#166534;padding:4px 10px;border-radius:20px;font-size:.78rem;font-weight:500;">
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified
                    </span>
                </div>
            <?php endif; ?>

            <div style="margin-top:18px;display:flex;flex-direction:column;gap:8px;text-align:left;">
                <?php if($workerProfile && $workerProfile->hourly_rate): ?>
                    <div style="display:flex;justify-content:space-between;font-size:.88rem;">
                        <span style="color:var(--g5);">Rate</span>
                        <span style="font-weight:600;">₱<?php echo e(number_format($workerProfile->hourly_rate)); ?>/hr</span>
                    </div>
                <?php endif; ?>
                <?php if($workerProfile && $workerProfile->years_of_experience): ?>
                    <div style="display:flex;justify-content:space-between;font-size:.88rem;">
                        <span style="color:var(--g5);">Experience</span>
                        <span><?php echo e($workerProfile->years_of_experience); ?> years</span>
                    </div>
                <?php endif; ?>
                <?php if($workerProfile && $workerProfile->available_days): ?>
                    <div style="display:flex;justify-content:space-between;font-size:.88rem;">
                        <span style="color:var(--g5);">Availability</span>
                        <span><?php echo e($workerProfile->available_days); ?></span>
                    </div>
                <?php endif; ?>
                <?php if($worker->phone): ?>
                    <div style="display:flex;justify-content:space-between;font-size:.88rem;">
                        <span style="color:var(--g5);">Contact</span>
                        <span><?php echo e($worker->phone); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div style="flex:1;min-width:0;display:flex;flex-direction:column;gap:20px;">
        
        <?php if($workerProfile && $workerProfile->bio): ?>
            <div class="card-panel">
                <div class="card-panel-header">
                    <h3 class="section-title">About</h3>
                </div>
                <p style="font-size:.9rem;color:var(--g7);line-height:1.7;"><?php echo e($workerProfile->bio); ?></p>
            </div>
        <?php endif; ?>

        
        <?php if($workerProfile && !empty($workerProfile->skills)): ?>
            <div class="card-panel">
                <div class="card-panel-header">
                    <h3 class="section-title">Skills</h3>
                </div>
                <div class="skill-tags" style="margin-top:4px;">
                    <?php $__currentLoopData = $workerProfile->skills; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $skill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="skill-tag"><?php echo e($skill); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if($workerProfile && !empty($workerProfile->spoken_languages)): ?>
            <div class="card-panel">
                <div class="card-panel-header">
                    <h3 class="section-title">Languages</h3>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:4px;">
                    <?php $__currentLoopData = $workerProfile->spoken_languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span style="background:var(--g0);padding:4px 12px;border-radius:20px;font-size:.82rem;color:var(--g7);"><?php echo e($lang); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if($documents && $documents->count() > 0): ?>
            <div class="card-panel">
                <div class="card-panel-header">
                    <h3 class="section-title">Documents</h3>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;margin-top:4px;">
                    <?php $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div style="display:flex;align-items:center;gap:10px;padding:8px 12px;background:var(--g0);border-radius:8px;font-size:.85rem;">
                            <i class="fa-solid fa-file-lines" style="color:var(--b5);" aria-hidden="true"></i>
                            <span style="flex:1;"><?php echo e($doc->document_type); ?></span>
                            <?php if($doc->status === 'verified'): ?>
                                <span style="color:#166534;font-size:.78rem;"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified</span>
                            <?php else: ?>
                                <span style="color:var(--g4);font-size:.78rem;"><?php echo e(ucfirst($doc->status)); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if($workerProfile && $workerProfile->portfolios && $workerProfile->portfolios->count() > 0): ?>
            <div class="card-panel">
                <div class="card-panel-header">
                    <h3 class="section-title">Portfolio</h3>
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-top:8px;">
                    <?php $__currentLoopData = $workerProfile->portfolios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div style="border-radius:8px;overflow:hidden;background:var(--g0);">
                            <?php if($item->photo_path): ?>
                                <img src="<?php echo e(Storage::url($item->photo_path)); ?>" alt="<?php echo e($item->caption ?? ''); ?>"
                                     style="width:100%;height:130px;object-fit:cover;display:block;">
                            <?php endif; ?>
                            <?php if($item->caption): ?>
                                <p style="padding:8px 10px;font-size:.78rem;color:var(--g6);"><?php echo e($item->caption); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if($reviews->count() > 0): ?>
            <div class="card-panel">
                <div class="card-panel-header">
                    <h3 class="section-title">Reviews (<?php echo e($reviews->count()); ?>)</h3>
                </div>
                <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div style="padding:14px 0;border-bottom:1px solid var(--g1);">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-weight:500;font-size:.88rem;"><?php echo e($review->client?->name ?? 'Anonymous'); ?></span>
                            <div style="display:flex;gap:2px;">
                                <?php for($s = 1; $s <= 5; $s++): ?>
                                    <i class="fa-<?php echo e($s <= $review->rating ? 'solid' : 'regular'); ?> fa-star" style="color:#f59e0b;font-size:.75rem;" aria-hidden="true"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <?php if($review->comment): ?>
                            <p style="font-size:.85rem;color:var(--g7);margin-top:6px;line-height:1.5;"><?php echo e($review->comment); ?></p>
                        <?php endif; ?>
                        <p style="font-size:.75rem;color:var(--g4);margin-top:4px;"><?php echo e($review->created_at->diffForHumans()); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        
        <div style="display:flex;gap:12px;justify-content:flex-end;">
            <?php if($bookingIdForMessage): ?>
                <a href="<?php echo e(route('client.messages')); ?>?booking=<?php echo e($bookingIdForMessage); ?>" class="btn btn-outline">
                    <i class="fa-regular fa-comment" aria-hidden="true"></i> Send Message
                </a>
            <?php endif; ?>
            <button type="button" class="btn btn-solid" onclick="openBookModal()">
                <i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Book Now
            </button>
        </div>
    </div>
</div>


<div id="book-modal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeBookModal()">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Book <?php echo e($worker->name); ?></h3>
            <button type="button" class="modal-close" onclick="closeBookModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="book-form" onsubmit="submitBooking(event)">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="worker_id" value="<?php echo e($worker->id); ?>">

                <div class="form-group">
                    <label for="service_category">Service</label>
                    <input type="text" id="service_category" name="service_category"
                           class="form-control" value="<?php echo e($worker->service_category ?? ''); ?>"
                           placeholder="e.g. Plumbing, Electrical" required>
                </div>

                <div class="form-group">
                    <label for="scheduled_at">Schedule</label>
                    <input type="datetime-local" id="scheduled_at" name="scheduled_at"
                           class="form-control" min="<?php echo e(now()->addHour()->format('Y-m-d\TH:i')); ?>" required>
                </div>

                <div class="form-group">
                    <label for="house_no">House No. / Street</label>
                    <input type="text" id="house_no" name="house_no" class="form-control"
                           placeholder="e.g. 123 Mabini St" required>
                </div>

                <div class="form-group">
                    <label for="barangay">Barangay</label>
                    <select id="barangay" name="barangay" class="form-control" required>
                        <option value="">Select barangay…</option>
                        <option value="Acle">Acle</option>
                        <option value="Bayudbud">Bayudbud</option>
                        <option value="Bolbok">Bolbok</option>
                        <option value="Burgos">Burgos</option>
                        <option value="Dalima">Dalima</option>
                        <option value="Dao">Dao</option>
                        <option value="Guinhawa">Guinhawa</option>
                        <option value="Lumbangan">Lumbangan</option>
                        <option value="Luna">Luna</option>
                        <option value="Luntal">Luntal</option>
                        <option value="Magahis">Magahis</option>
                        <option value="Malibu">Malibu</option>
                        <option value="Mataywanac">Mataywanac</option>
                        <option value="Palincaro">Palincaro</option>
                        <option value="Putol">Putol</option>
                        <option value="Rillo">Rillo</option>
                        <option value="Rizal">Rizal</option>
                        <option value="Sabang">Sabang</option>
                        <option value="San Jose">San Jose</option>
                        <option value="Talon">Talon</option>
                        <option value="Toong">Toong</option>
                        <option value="Tuyon-Tuyon">Tuyon-Tuyon</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes">Notes <small>(optional)</small></label>
                    <textarea id="notes" name="notes" class="form-control book-textarea"
                              placeholder="Describe what you need done…"></textarea>
                </div>

                <?php if($worker->workerProfile && $worker->workerProfile->hourly_rate): ?>
                    <div style="font-size:.85rem;color:var(--g5);margin-bottom:12px;">
                        Rate: <strong>₱<?php echo e(number_format($worker->workerProfile->hourly_rate)); ?>/hr</strong>
                    </div>
                <?php endif; ?>

                <div id="book-msg" style="display:none;"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeBookModal()">Cancel</button>
            <button type="submit" class="btn btn-solid" id="book-submit-btn" form="book-form">Send Request</button>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function openBookModal() {
    document.getElementById('book-modal').style.display = 'flex';
    document.getElementById('book-msg').style.display = 'none';
}

function closeBookModal() {
    document.getElementById('book-modal').style.display = 'none';
}

function submitBooking(e) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('book-submit-btn');
    const msg = document.getElementById('book-msg');
    btn.disabled = true;
    btn.textContent = 'Sending…';
    msg.style.display = 'none';

    const formData = new FormData(form);
    const data = {};
    formData.forEach((v, k) => data[k] = v);

    fetch('<?php echo e(route('client.bookings.store')); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Accept': 'application/json',
        },
        body: JSON.stringify(data),
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            msg.style.display = 'block';
            msg.className = 'alert alert-success';
            msg.innerHTML = 'Booking request sent! <a href="' + res.redirect + '" style="text-decoration:underline;">View my bookings</a>';
            btn.textContent = 'Sent!';
            setTimeout(() => {
                closeBookModal();
                if (res.redirect) window.location.href = res.redirect;
            }, 1500);
        } else {
            throw new Error(res.message || 'Something went wrong');
        }
    })
    .catch(err => {
        msg.style.display = 'block';
        msg.className = 'alert alert-error';
        msg.textContent = err.message;
        btn.disabled = false;
        btn.textContent = 'Send Request';
    });
}
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.worker-profile-layout {
    display: flex; gap: 24px; align-items: flex-start;
}
.worker-profile-layout .card-panel {
    padding: 18px 22px;
}
.worker-profile-layout .card-panel-header {
    margin: -18px -22px 16px;
    padding: 18px 22px;
}
@media (max-width: 768px) {
    .worker-profile-layout { flex-direction: column; }
    .worker-profile-layout > .card-panel { flex: none !important; width: 100%; }
}
.modal-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,.45); z-index: 1000;
    display: flex; align-items: center; justify-content: center;
}
.modal-box {
    background: #fff; border-radius: 14px; width: 90%; max-width: 520px;
    box-shadow: 0 20px 60px rgba(0,0,0,.2); animation: modalIn .2s ease;
}
@keyframes modalIn {
    from { opacity: 0; transform: scale(.95) translateY(10px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}
.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 22px 0; font-weight: 600; font-size: 1.05rem;
}
.modal-close {
    background: none; border: none; font-size: 1.5rem; cursor: pointer;
    color: var(--g4); line-height: 1;
}
.modal-close:hover { color: var(--g8); }
.modal-body { padding: 16px 22px; max-height: 60vh; overflow-y: auto; }
.book-textarea { resize: none; min-height: 80px; width: 100%; box-sizing: border-box; }
#book-form .form-control { width: 100%; box-sizing: border-box; }
.modal-footer {
    display: flex; gap: 10px; justify-content: flex-end;
    padding: 0 22px 18px;
}
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.client', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Capstone\KaAyos\kaayos\resources\views/client/workers/show.blade.php ENDPATH**/ ?>