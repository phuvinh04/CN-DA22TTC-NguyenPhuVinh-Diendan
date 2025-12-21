/**
 * Main JavaScript - Diễn Đàn Chuyên Ngành
 */

// Toast Notification System
const Toast = {
  container: null,

  init() {
    if (!this.container) {
      this.container = document.createElement('div');
      this.container.className = 'toast-container';
      document.body.appendChild(this.container);
    }
  },

  show(message, type = 'info', title = '', duration = 4000) {
    this.init();

    const icons = {
      success: 'bi-check-circle-fill',
      error: 'bi-x-circle-fill',
      warning: 'bi-exclamation-triangle-fill',
      info: 'bi-info-circle-fill'
    };

    const titles = {
      success: 'Thành công',
      error: 'Lỗi',
      warning: 'Cảnh báo',
      info: 'Thông báo'
    };

    const toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.innerHTML = '<i class="toast-icon bi ' + icons[type] + '"></i>' +
      '<div class="toast-content">' +
      '<div class="toast-title">' + (title || titles[type]) + '</div>' +
      '<div class="toast-message">' + message + '</div>' +
      '</div>' +
      '<button class="toast-close" onclick="Toast.close(this.parentElement)">' +
      '<i class="bi bi-x"></i>' +
      '</button>';

    this.container.appendChild(toast);

    if (duration > 0) {
      setTimeout(function () { Toast.close(toast); }, duration);
    }

    return toast;
  },

  close(toast) {
    if (toast && toast.parentElement) {
      toast.classList.add('hiding');
      setTimeout(function () { toast.remove(); }, 300);
    }
  },

  success(message, title) { return this.show(message, 'success', title || ''); },
  error(message, title) { return this.show(message, 'error', title || ''); },
  warning(message, title) { return this.show(message, 'warning', title || ''); },
  info(message, title) { return this.show(message, 'info', title || ''); }
};

// Confirm Modal
const Confirm = {
  modal: null,

  show(options) {
    options = options || {};
    const title = options.title || 'Xác nhận';
    const message = options.message || 'Bạn có chắc chắn muốn thực hiện?';
    const confirmText = options.confirmText || 'Xác nhận';
    const cancelText = options.cancelText || 'Hủy';
    const type = options.type || 'danger';
    const onConfirm = options.onConfirm || function () { };
    const onCancel = options.onCancel || function () { };

    const icons = {
      danger: 'bi-exclamation-triangle-fill',
      warning: 'bi-question-circle-fill',
      info: 'bi-info-circle-fill'
    };

    const colors = {
      danger: 'var(--error)',
      warning: 'var(--warning)',
      info: 'var(--info)'
    };

    if (this.modal) this.modal.remove();

    this.modal = document.createElement('div');
    this.modal.className = 'confirm-modal';
    this.modal.innerHTML = '<div class="confirm-modal-content">' +
      '<div class="confirm-modal-icon" style="background: ' + colors[type] + '15;">' +
      '<i class="bi ' + icons[type] + '" style="color: ' + colors[type] + ';"></i>' +
      '</div>' +
      '<h5>' + title + '</h5>' +
      '<p>' + message + '</p>' +
      '<div class="confirm-modal-actions">' +
      '<button class="btn btn-secondary" id="confirmCancel">' + cancelText + '</button>' +
      '<button class="btn btn-' + (type === 'danger' ? 'danger' : 'primary') + '" id="confirmOk">' + confirmText + '</button>' +
      '</div>' +
      '</div>';

    document.body.appendChild(this.modal);

    var self = this;
    requestAnimationFrame(function () { self.modal.classList.add('show'); });

    this.modal.querySelector('#confirmOk').onclick = function () {
      self.hide();
      onConfirm();
    };

    this.modal.querySelector('#confirmCancel').onclick = function () {
      self.hide();
      onCancel();
    };

    this.modal.onclick = function (e) {
      if (e.target === self.modal) {
        self.hide();
        onCancel();
      }
    };
  },

  hide() {
    var self = this;
    if (this.modal) {
      this.modal.classList.remove('show');
      setTimeout(function () {
        if (self.modal) {
          self.modal.remove();
          self.modal = null;
        }
      }, 200);
    }
  }
};

// Delete confirmation wrapper
function confirmDelete(message, callback) {
  Confirm.show({
    title: 'Xác nhận xóa',
    message: message || 'Bạn có chắc chắn muốn xóa? Hành động này không thể hoàn tác.',
    confirmText: 'Xóa',
    cancelText: 'Hủy',
    type: 'danger',
    onConfirm: callback || function () { return true; }
  });
  return false;
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function () {
  // Auto-hide alerts after 5s
  document.querySelectorAll('.alert:not(.alert-permanent)').forEach(function (alert) {
    setTimeout(function () {
      alert.style.opacity = '0';
      alert.style.transform = 'translateY(-10px)';
      setTimeout(function () { alert.remove(); }, 300);
    }, 5000);
  });
});


// ============================================
// SEARCH SUGGESTIONS SYSTEM
// ============================================
var SearchSuggestions = {
  input: null,
  container: null,
  timeout: null,
  currentQuery: '',

  init: function () {
    this.input = document.getElementById('searchInput');
    this.container = document.getElementById('searchSuggestions');

    if (!this.input || !this.container) return;

    var self = this;

    this.input.addEventListener('input', function (e) { self.handleInput(e); });
    this.input.addEventListener('focus', function () { self.handleFocus(); });
    this.input.addEventListener('keydown', function (e) { self.handleKeydown(e); });

    document.addEventListener('click', function (e) {
      if (!e.target.closest('.search-container')) {
        self.hide();
      }
    });
  },

  handleInput: function (e) {
    var self = this;
    var query = e.target.value.trim();

    if (this.timeout) {
      clearTimeout(this.timeout);
    }

    if (query.length < 2) {
      this.hide();
      return;
    }

    this.timeout = setTimeout(function () {
      self.search(query);
    }, 300);
  },

  handleFocus: function () {
    if (this.input.value.trim().length >= 2) {
      this.search(this.input.value.trim());
    }
  },

  handleKeydown: function (e) {
    var items = this.container.querySelectorAll('.suggestion-item');
    var current = this.container.querySelector('.suggestion-item.active');

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      if (current) {
        current.classList.remove('active');
        var next = current.nextElementSibling || items[0];
        if (next && next.classList.contains('suggestion-item')) {
          next.classList.add('active');
        }
      } else if (items.length > 0) {
        items[0].classList.add('active');
      }
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      if (current) {
        current.classList.remove('active');
        var prev = current.previousElementSibling || items[items.length - 1];
        if (prev && prev.classList.contains('suggestion-item')) {
          prev.classList.add('active');
        }
      } else if (items.length > 0) {
        items[items.length - 1].classList.add('active');
      }
    } else if (e.key === 'Enter') {
      if (current) {
        e.preventDefault();
        window.location.href = current.href;
      }
    } else if (e.key === 'Escape') {
      this.hide();
      this.input.blur();
    }
  },

  search: function (query) {
    if (query === this.currentQuery) return;
    this.currentQuery = query;
    var self = this;

    this.showLoading();

    var apiPath = 'api/search-suggestions.php';
    var path = window.location.pathname;
    if (path.indexOf('/user/') !== -1 || path.indexOf('/admin/') !== -1 || path.indexOf('/moderator/') !== -1) {
      apiPath = '../api/search-suggestions.php';
    }

    fetch(apiPath + '?q=' + encodeURIComponent(query))
      .then(function (response) { return response.json(); })
      .then(function (data) {
        if (data.suggestions && data.suggestions.length > 0) {
          self.showSuggestions(data.suggestions);
        } else {
          self.showNoResults();
        }
      })
      .catch(function (error) {
        console.error('Search error:', error);
        self.hide();
      });
  },

  showLoading: function () {
    this.container.innerHTML = '<div class="search-loading"><i class="bi bi-hourglass-split"></i> Đang tìm...</div>';
    this.container.classList.add('show');
  },

  showSuggestions: function (suggestions) {
    var self = this;
    var html = suggestions.map(function (item) { return self.renderSuggestion(item); }).join('');
    this.container.innerHTML = html;
    this.container.classList.add('show');
  },

  showNoResults: function () {
    this.container.innerHTML = '<div class="search-no-results"><i class="bi bi-search"></i> Không tìm thấy kết quả</div>';
    this.container.classList.add('show');
  },

  renderSuggestion: function (item) {
    var typeLabels = { question: 'Câu hỏi', tag: 'Tag', user: 'Người dùng' };
    var avatar = '';
    var meta = '';

    if (item.type === 'user' && item.avatar) {
      avatar = '<img src="' + item.avatar + '" alt="" class="suggestion-avatar">';
    } else {
      avatar = '<div class="suggestion-icon"><i class="bi ' + item.icon + '"></i></div>';
    }

    if (item.type === 'tag') {
      meta = (item.count || 0) + ' câu hỏi';
    } else if (item.type === 'user') {
      meta = (item.points || 0) + ' điểm';
    } else {
      meta = typeLabels[item.type];
    }

    var url = item.url;
    var path = window.location.pathname;
    if (path.indexOf('/user/') === -1 && path.indexOf('/admin/') === -1 && path.indexOf('/moderator/') === -1) {
      url = url.replace('../', '');
    }

    var title = this.highlightQuery(item.title);

    return '<a href="' + url + '" class="suggestion-item ' + item.type + '">' +
      avatar +
      '<div class="suggestion-content">' +
      '<div class="suggestion-title">' + title + '</div>' +
      '<div class="suggestion-meta">' + meta + '</div>' +
      '</div>' +
      '<span class="suggestion-badge">' + typeLabels[item.type] + '</span>' +
      '</a>';
  },

  highlightQuery: function (text) {
    if (!this.currentQuery) return text;
    var query = this.currentQuery.toLowerCase();
    var lowerText = text.toLowerCase();
    var idx = lowerText.indexOf(query);
    if (idx === -1) return text;
    return text.substring(0, idx) + '<mark>' + text.substring(idx, idx + query.length) + '</mark>' + text.substring(idx + query.length);
  },

  hide: function () {
    this.container.classList.remove('show');
    var activeItems = this.container.querySelectorAll('.suggestion-item.active');
    activeItems.forEach(function (item) { item.classList.remove('active'); });
  }
};

// Initialize search
document.addEventListener('DOMContentLoaded', function () {
  SearchSuggestions.init();
});
