<?php
/**
 * Thông báo tiếng Việt cho hệ thống
 */

$MESSAGES = [
    // Auth
    'login_required' => 'Vui lòng đăng nhập để tiếp tục',
    'login_success' => 'Đăng nhập thành công!',
    'login_failed' => 'Tên đăng nhập hoặc mật khẩu không đúng',
    'logout_success' => 'Đăng xuất thành công!',
    'register_success' => 'Đăng ký thành công! Vui lòng đăng nhập.',
    'register_failed' => 'Đăng ký thất bại, vui lòng thử lại',
    'account_exists' => 'Tên đăng nhập hoặc email đã tồn tại',
    'permission_denied' => 'Bạn không có quyền thực hiện hành động này',
    
    // Validation
    'field_required' => 'Trường này là bắt buộc',
    'email_invalid' => 'Email không hợp lệ',
    'password_min' => 'Mật khẩu phải có ít nhất 6 ký tự',
    'password_mismatch' => 'Mật khẩu xác nhận không khớp',
    'title_min' => 'Tiêu đề phải có ít nhất 10 ký tự',
    'content_min' => 'Nội dung phải có ít nhất 20 ký tự',
    
    // Questions
    'question_created' => 'Đặt câu hỏi thành công! Câu hỏi đang chờ duyệt.',
    'question_updated' => 'Cập nhật câu hỏi thành công!',
    'question_deleted' => 'Xóa câu hỏi thành công!',
    'question_not_found' => 'Không tìm thấy câu hỏi',
    'question_closed' => 'Câu hỏi đã được đóng',
    
    // Answers
    'answer_created' => 'Gửi câu trả lời thành công! +10 điểm',
    'answer_updated' => 'Cập nhật câu trả lời thành công!',
    'answer_deleted' => 'Xóa câu trả lời thành công! -10 điểm',
    'answer_not_found' => 'Không tìm thấy câu trả lời',
    
    // Reports
    'report_created' => 'Báo cáo đã được gửi. Cảm ơn bạn!',
    'report_exists' => 'Bạn đã báo cáo nội dung này rồi',
    
    // General
    'save_success' => 'Lưu thành công!',
    'save_failed' => 'Lưu thất bại, vui lòng thử lại',
    'delete_success' => 'Xóa thành công!',
    'delete_failed' => 'Xóa thất bại, vui lòng thử lại',
    'update_success' => 'Cập nhật thành công!',
    'update_failed' => 'Cập nhật thất bại, vui lòng thử lại',
    'error_occurred' => 'Có lỗi xảy ra, vui lòng thử lại',
    'invalid_request' => 'Yêu cầu không hợp lệ',
    
    // Confirm
    'confirm_delete' => 'Bạn có chắc chắn muốn xóa? Hành động này không thể hoàn tác.',
    'confirm_delete_question' => 'Bạn có chắc muốn xóa câu hỏi này? Bạn sẽ bị trừ 5 điểm.',
    'confirm_delete_answer' => 'Bạn có chắc muốn xóa câu trả lời này? Bạn sẽ bị trừ 10 điểm.',
    'confirm_close_question' => 'Bạn có chắc muốn đóng câu hỏi này? Sau khi đóng, không ai có thể trả lời thêm.',
];

function getMessage($key, $default = '') {
    global $MESSAGES;
    return $MESSAGES[$key] ?? $default;
}

function showAlert($message, $type = 'info') {
    $icons = [
        'success' => 'bi-check-circle-fill',
        'error' => 'bi-x-circle-fill',
        'warning' => 'bi-exclamation-triangle-fill',
        'info' => 'bi-info-circle-fill',
        'danger' => 'bi-x-circle-fill'
    ];
    $icon = $icons[$type] ?? $icons['info'];
    $alertType = $type === 'error' ? 'danger' : $type;
    
    return "<div class='alert alert-{$alertType} d-flex align-items-center gap-2'>
        <i class='bi {$icon}'></i>
        <span>{$message}</span>
    </div>";
}
