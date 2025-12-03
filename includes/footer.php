    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-4" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%) !important;">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <h5 class="text-white"><i class="bi bi-chat-dots-fill me-2"></i>Diễn Đàn Chuyên Ngành</h5>
                    <p class="text-white-50">Nơi chia sẻ kiến thức và giải đáp thắc mắc chuyên môn.</p>
                </div>
                <div class="col-md-4 mb-3">
                    <h6 class="text-white mb-3">Liên kết</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?php echo isset($basePath) ? $basePath : ''; ?>index.php" class="text-white-50 text-decoration-none hover-link">Trang chủ</a></li>
                        <li class="mb-2"><a href="<?php echo isset($basePath) ? $basePath : ''; ?>questions.php" class="text-white-50 text-decoration-none hover-link">Câu hỏi</a></li>
                        <li class="mb-2"><a href="<?php echo isset($basePath) ? $basePath : ''; ?>tags.php" class="text-white-50 text-decoration-none hover-link">Tags</a></li>
                        <li class="mb-2"><a href="<?php echo isset($basePath) ? $basePath : ''; ?>users.php" class="text-white-50 text-decoration-none hover-link">Thành viên</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-3">
                    <h6 class="text-white mb-3">Theo dõi</h6>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-outline-light btn-sm"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm"><i class="bi bi-github"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <hr class="border-secondary opacity-25">
            <div class="text-center">
                <small class="text-white-50">&copy; <?php echo date('Y'); ?> Diễn Đàn Chuyên Ngành. All rights reserved.</small>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo isset($basePath) ? $basePath : ''; ?>assets/js/main.js"></script>
</body>
</html>
