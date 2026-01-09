    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <h5><i class="bi bi-mortarboard-fill me-2" style="color: var(--primary-500);"></i>Diễn Đàn Chuyên Ngành</h5>
                    <p>Nơi chia sẻ kiến thức và giải đáp thắc mắc chuyên môn. Cộng đồng học tập và phát triển cùng nhau.</p>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6>Khám phá</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo isset($basePath) ? $basePath : ''; ?>questions.php" class="hover-link"><i class="bi bi-chevron-right me-1"></i>Câu hỏi</a></li>
                        <li><a href="<?php echo isset($basePath) ? $basePath : ''; ?>tags.php" class="hover-link"><i class="bi bi-chevron-right me-1"></i>Tags</a></li>
                        <li><a href="<?php echo isset($basePath) ? $basePath : ''; ?>users.php" class="hover-link"><i class="bi bi-chevron-right me-1"></i>Thành viên</a></li>
                        <li><a href="<?php echo isset($basePath) ? $basePath : ''; ?>leaderboard.php" class="hover-link"><i class="bi bi-chevron-right me-1"></i>Xếp hạng</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6>Thông tin</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo isset($basePath) ? $basePath : ''; ?>about.php" class="hover-link"><i class="bi bi-chevron-right me-1"></i>Giới thiệu</a></li>
                        <li><a href="<?php echo isset($basePath) ? $basePath : ''; ?>contact.php" class="hover-link"><i class="bi bi-chevron-right me-1"></i>Liên hệ</a></li>
                        <li><a href="<?php echo isset($basePath) ? $basePath : ''; ?>points-system.php" class="hover-link"><i class="bi bi-chevron-right me-1"></i>Hệ thống điểm</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h6>Kết nối</h6>
                    <div class="d-flex gap-2 mb-3">
                        <a href="https://facebook.com" class="btn btn-outline-light" target="_blank"><i class="bi bi-facebook"></i></a>
                        <a href="https://twitter.com" class="btn btn-outline-light" target="_blank"><i class="bi bi-twitter-x"></i></a>
                        <a href="https://github.com" class="btn btn-outline-light" target="_blank"><i class="bi bi-github"></i></a>
                        <a href="https://linkedin.com" class="btn btn-outline-light" target="_blank"><i class="bi bi-linkedin"></i></a>
                    </div>
                    <p class="small mb-0" style="color: var(--gray-500);">Theo dõi chúng tôi để cập nhật tin tức mới nhất.</p>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <small style="color: var(--gray-500);">&copy; <?php echo date('Y'); ?> Diễn Đàn Chuyên Ngành. All rights reserved.</small>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top -->
    <button class="scroll-top" id="scrollTop" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- Daily Check-in Modal -->
    <?php if (isset($currentUser) && $currentUser): ?>
    <div class="modal fade" id="checkinModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content checkin-modal">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title"><i class="bi bi-calendar-check me-2"></i>Điểm danh hàng ngày</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <!-- Streak Display -->
                    <div class="streak-display-large mb-4">
                        <div class="streak-fire-large">🔥</div>
                        <div class="streak-number" id="modalStreak">0</div>
                        <div class="streak-label">ngày liên tiếp</div>
                    </div>
                    
                    <!-- Calendar Preview (7 days) -->
                    <div class="checkin-calendar mb-4" id="checkinCalendar">
                        <!-- Sẽ được render bằng JS -->
                    </div>
                    
                    <!-- Next Bonus Info -->
                    <div class="next-bonus-info mb-4" id="nextBonusInfo">
                        <i class="bi bi-gift me-1"></i>
                        <span>Còn <strong id="nextBonusDays">7</strong> ngày để nhận bonus <strong id="nextBonusPoints">+15</strong> điểm</span>
                    </div>
                    
                    <!-- Check-in Button -->
                    <button type="button" class="btn btn-checkin-large" id="doCheckinBtn" onclick="doCheckin()">
                        <i class="bi bi-check-circle me-2"></i>
                        <span id="checkinBtnText">Điểm danh (+2 điểm)</span>
                    </button>
                    
                    <!-- Result Message -->
                    <div class="checkin-result mt-3" id="checkinResult" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo isset($basePath) ? $basePath : ''; ?>assets/js/main.js"></script>
    
    <script>
        window.addEventListener('scroll', function() {
            const scrollBtn = document.getElementById('scrollTop');
            if (window.scrollY > 300) {
                scrollBtn.classList.add('visible');
            } else {
                scrollBtn.classList.remove('visible');
            }
        });

        // === NOTIFICATION FUNCTIONS ===
        async function markAllRead(event) {
            event.preventDefault();
            event.stopPropagation();
            
            try {
                const response = await fetch(basePath + 'api/notification.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'markAllRead' })
                });
                const data = await response.json();
                
                if (data.success) {
                    // Xóa badge số thông báo
                    const badge = document.querySelector('.notification-badge');
                    if (badge) badge.remove();
                    
                    // Xóa class unread và dot của tất cả thông báo
                    document.querySelectorAll('.notification-item.unread').forEach(item => {
                        item.classList.remove('unread');
                    });
                    document.querySelectorAll('.unread-dot').forEach(dot => {
                        dot.remove();
                    });
                    
                    // Ẩn link "Đánh dấu đã đọc"
                    event.target.style.display = 'none';
                }
            } catch (e) {
                console.error('Error marking all read:', e);
            }
        }

        // === DAILY CHECK-IN FUNCTIONS ===
        const basePath = '<?php echo isset($basePath) ? $basePath : ''; ?>';
        let checkinModal = null;

        // Load streak info on page load
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($currentUser) && $currentUser): ?>
            loadStreakInfo();
            checkinModal = new bootstrap.Modal(document.getElementById('checkinModal'));
            <?php endif; ?>
        });

        // Load streak info
        async function loadStreakInfo() {
            try {
                const response = await fetch(basePath + 'api/streak-info.php');
                const data = await response.json();
                
                if (data.success) {
                    // Update header badge
                    const badge = document.getElementById('streakBadge');
                    const btn = document.getElementById('checkinBtn');
                    
                    if (data.streak > 0) {
                        badge.textContent = data.streak;
                        badge.style.display = 'flex';
                    }
                    
                    if (data.checked_today) {
                        btn.classList.add('checked');
                    }
                }
            } catch (e) {
                console.error('Error loading streak:', e);
            }
        }

        // Show check-in modal
        async function showCheckinModal() {
            try {
                const response = await fetch(basePath + 'api/streak-info.php');
                const data = await response.json();
                
                if (data.success) {
                    // Update modal content
                    document.getElementById('modalStreak').textContent = data.streak;
                    document.getElementById('nextBonusDays').textContent = data.next_bonus_in;
                    document.getElementById('nextBonusPoints').textContent = '+' + data.next_bonus_points;
                    
                    // Render calendar
                    renderCheckinCalendar(data.streak, data.checked_today);
                    
                    // Update button state
                    const btn = document.getElementById('doCheckinBtn');
                    const btnText = document.getElementById('checkinBtnText');
                    
                    if (data.checked_today) {
                        btn.disabled = true;
                        btn.classList.add('btn-secondary');
                        btn.classList.remove('btn-checkin-large');
                        btnText.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Đã điểm danh hôm nay';
                    } else {
                        btn.disabled = false;
                        btn.classList.remove('btn-secondary');
                        btn.classList.add('btn-checkin-large');
                        btnText.innerHTML = 'Điểm danh (+2 điểm)';
                    }
                    
                    // Hide result
                    document.getElementById('checkinResult').style.display = 'none';
                }
                
                checkinModal.show();
            } catch (e) {
                console.error('Error:', e);
            }
        }

        // Render 7-day calendar
        function renderCheckinCalendar(streak, checkedToday) {
            const calendar = document.getElementById('checkinCalendar');
            const days = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
            const today = new Date();
            
            // Tính số ngày đã check (không bao gồm hôm nay nếu chưa check)
            const daysChecked = checkedToday ? streak : streak;
            
            let html = '<div class="d-flex justify-content-center gap-2">';
            
            for (let i = 6; i >= 0; i--) {
                const date = new Date(today);
                date.setDate(date.getDate() - i);
                const dayName = days[date.getDay()];
                const dayNum = date.getDate();
                
                let status = 'missed';
                
                if (i === 0) {
                    // Hôm nay
                    status = checkedToday ? 'checked' : 'today';
                } else if (checkedToday) {
                    // Đã check hôm nay: streak bao gồm hôm nay
                    // i=1 là hôm qua, i=2 là 2 ngày trước...
                    // Nếu streak=2, chỉ hôm nay và hôm qua được check (i=0 và i=1)
                    // Vậy i phải < streak (vì i=0 đã xử lý riêng)
                    if (i < streak) {
                        status = 'checked';
                    }
                } else {
                    // Chưa check hôm nay: streak là từ hôm qua
                    // Nếu streak=2, hôm qua và hôm kia được check (i=1 và i=2)
                    if (i > 0 && i <= streak) {
                        status = 'checked';
                    }
                }
                
                html += `
                    <div class="calendar-day ${status}">
                        <div class="day-name">${dayName}</div>
                        <div class="day-num">${dayNum}</div>
                        ${status === 'checked' ? '<i class="bi bi-check-circle-fill"></i>' : ''}
                    </div>
                `;
            }
            
            html += '</div>';
            calendar.innerHTML = html;
        }

        // Do check-in
        async function doCheckin() {
            const btn = document.getElementById('doCheckinBtn');
            const btnText = document.getElementById('checkinBtnText');
            const result = document.getElementById('checkinResult');
            
            btn.disabled = true;
            btnText.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
            
            try {
                const response = await fetch(basePath + 'api/daily-checkin.php', {
                    method: 'POST'
                });
                const data = await response.json();
                
                if (data.success) {
                    // Update UI
                    document.getElementById('modalStreak').textContent = data.streak;
                    document.getElementById('streakBadge').textContent = data.streak;
                    document.getElementById('streakBadge').style.display = 'flex';
                    document.getElementById('checkinBtn').classList.add('checked');
                    
                    // Show success message
                    let msg = `<div class="alert alert-success mb-0">
                        <div class="fs-4 mb-2">🎉</div>
                        <strong>+${data.points_earned} điểm!</strong><br>
                        <small>Streak: ${data.streak} ngày</small>`;
                    
                    if (data.bonus_message) {
                        msg += `<br><span class="badge bg-warning text-dark mt-2">${data.bonus_message}</span>`;
                    }
                    
                    if (data.new_badges && data.new_badges.length > 0) {
                        msg += '<br><small class="mt-2 d-block">Huy hiệu mới: ';
                        data.new_badges.forEach(b => {
                            msg += `${b.icon} ${b.name} `;
                        });
                        msg += '</small>';
                    }
                    
                    msg += '</div>';
                    result.innerHTML = msg;
                    result.style.display = 'block';
                    
                    // Update button
                    btn.classList.add('btn-secondary');
                    btn.classList.remove('btn-checkin-large');
                    btnText.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Đã điểm danh';
                    
                    // Refresh calendar
                    renderCheckinCalendar(data.streak, true);
                } else {
                    result.innerHTML = `<div class="alert alert-warning mb-0">${data.message}</div>`;
                    result.style.display = 'block';
                    btn.disabled = false;
                    btnText.innerHTML = 'Điểm danh (+2 điểm)';
                }
            } catch (e) {
                result.innerHTML = '<div class="alert alert-danger mb-0">Có lỗi xảy ra!</div>';
                result.style.display = 'block';
                btn.disabled = false;
                btnText.innerHTML = 'Điểm danh (+2 điểm)';
            }
        }
    </script>
</body>
</html>
