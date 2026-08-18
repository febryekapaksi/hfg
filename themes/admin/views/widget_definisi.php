<!-- ============================================
     FLOATING WIDGET DEFINISI (Assistive Touch Style)
     ============================================ -->
<style>
  /* Floating Button */
  #definisi-fab {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border: none;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    cursor: pointer;
    z-index: 9998;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    transition: transform 0.2s, box-shadow 0.2s;
    user-select: none;
    touch-action: none;
  }

  #definisi-fab:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
  }

  #definisi-fab.dragging {
    opacity: 0.8;
    transform: scale(0.95);
  }

  /* Popup Panel */
  #definisi-panel {
    position: fixed;
    bottom: 100px;
    right: 30px;
    width: 380px;
    max-height: 500px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    z-index: 9999;
    display: none;
    flex-direction: column;
    overflow: hidden;
    animation: fadeInUp 0.25s ease;
  }

  #definisi-panel.show {
    display: flex;
  }

  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  #definisi-panel .panel-header {
    padding: 16px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  #definisi-panel .panel-header h5 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
  }

  #definisi-panel .panel-header .btn-close-panel {
    background: none;
    border: none;
    color: #fff;
    font-size: 20px;
    cursor: pointer;
    opacity: 0.8;
    transition: opacity 0.2s;
  }

  #definisi-panel .panel-header .btn-close-panel:hover {
    opacity: 1;
  }

  #definisi-panel .panel-search {
    padding: 12px 16px;
    border-bottom: 1px solid #eee;
  }

  #definisi-panel .panel-search input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
  }

  #definisi-panel .panel-search input:focus {
    border-color: #667eea;
  }

  #definisi-panel .panel-body {
    flex: 1;
    overflow-y: auto;
    padding: 12px 16px;
    max-height: 340px;
  }

  #definisi-panel .panel-body::-webkit-scrollbar {
    width: 5px;
  }

  #definisi-panel .panel-body::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 4px;
  }

  /* Item definisi */
  .definisi-item {
    padding: 12px 14px;
    margin-bottom: 8px;
    background: #f8f9fa;
    border-radius: 10px;
    border-left: 4px solid #667eea;
    transition: background 0.2s;
  }

  .definisi-item:hover {
    background: #eef0ff;
  }

  .definisi-item .istilah {
    font-weight: 600;
    font-size: 14px;
    color: #333;
    margin-bottom: 4px;
  }

  .definisi-item .definisi-text {
    font-size: 13px;
    color: #666;
    line-height: 1.4;
  }

  .definisi-empty {
    text-align: center;
    padding: 30px 16px;
    color: #999;
    font-size: 14px;
  }

  .definisi-loading {
    text-align: center;
    padding: 30px 16px;
    color: #999;
  }

  /* Responsive */
  @media (max-width: 480px) {
    #definisi-panel {
      width: calc(100vw - 32px);
      right: 16px;
      bottom: 90px;
      max-height: 60vh;
    }

    #definisi-fab {
      bottom: 20px;
      right: 20px;
      width: 48px;
      height: 48px;
      font-size: 20px;
    }
  }
</style>

<!-- Floating Button -->
<button id="definisi-fab" title="Glossary / Definisi Istilah">
  <i class="ti ti-book"></i>
</button>

<!-- Popup Panel -->
<div id="definisi-panel">
  <div class="panel-header">
    <h5><i class="ti ti-book me-2"></i>Glossary</h5>
    <button class="btn-close-panel" id="definisi-close">&times;</button>
  </div>

  <div class="panel-search">
    <input type="text" id="definisi-search" placeholder="Cari istilah..." autocomplete="off">
  </div>

  <div class="panel-body" id="definisi-list">
    <div class="definisi-loading">
      <i class="ti ti-loader"></i> Memuat data...
    </div>
  </div>
</div>

<script>
  (function() {
    const fab = document.getElementById('definisi-fab');
    const panel = document.getElementById('definisi-panel');
    const closeBtn = document.getElementById('definisi-close');
    const searchInput = document.getElementById('definisi-search');
    const listContainer = document.getElementById('definisi-list');

    let isOpen = false;
    let definisiData = [];
    let searchTimeout = null;

    // Toggle panel
    fab.addEventListener('click', function(e) {
      if (fab.classList.contains('dragging')) return;
      isOpen = !isOpen;
      if (isOpen) {
        panel.classList.add('show');
        loadDefinisi();
        setTimeout(() => searchInput.focus(), 100);
      } else {
        panel.classList.remove('show');
      }
    });

    // Close button
    closeBtn.addEventListener('click', function() {
      isOpen = false;
      panel.classList.remove('show');
    });

    // Close on click outside
    document.addEventListener('click', function(e) {
      if (isOpen && !panel.contains(e.target) && !fab.contains(e.target)) {
        isOpen = false;
        panel.classList.remove('show');
      }
    });

    // Keyboard shortcut: Ctrl+Shift+D
    document.addEventListener('keydown', function(e) {
      if (e.ctrlKey && e.shiftKey && e.key === 'D') {
        e.preventDefault();
        fab.click();
      }
      // ESC to close
      if (e.key === 'Escape' && isOpen) {
        isOpen = false;
        panel.classList.remove('show');
      }
    });

    // Search with debounce
    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(function() {
        filterDefinisi(searchInput.value);
      }, 300);
    });

    // Load data via AJAX
    function loadDefinisi(query) {
      let url = base_url + 'master_definisi/get_all';
      if (query) url += '?q=' + encodeURIComponent(query);

      listContainer.innerHTML = '<div class="definisi-loading"><i class="ti ti-loader"></i> Memuat data...</div>';

      $.ajax({
        type: 'GET',
        url: url,
        dataType: 'json',
        success: function(res) {
          if (res.status == 1) {
            definisiData = res.data;
            renderList(definisiData);
          }
        },
        error: function() {
          listContainer.innerHTML = '<div class="definisi-empty">Gagal memuat data</div>';
        }
      });
    }

    // Filter locally
    function filterDefinisi(keyword) {
      if (!keyword) {
        renderList(definisiData);
        return;
      }
      const filtered = definisiData.filter(function(item) {
        return item.istilah.toLowerCase().includes(keyword.toLowerCase()) ||
          item.definisi.toLowerCase().includes(keyword.toLowerCase());
      });
      renderList(filtered);
    }

    // Render list
    function renderList(data) {
      if (!data || data.length === 0) {
        listContainer.innerHTML = '<div class="definisi-empty"><i class="ti ti-mood-empty" style="font-size:32px;display:block;margin-bottom:8px;"></i>Tidak ada data ditemukan</div>';
        return;
      }

      let html = '';
      data.forEach(function(item) {
        html += '<div class="definisi-item">';
        html += '<div class="istilah">' + escapeHtml(item.istilah) + '</div>';
        html += '<div class="definisi-text">' + escapeHtml(item.definisi) + '</div>';
        html += '</div>';
      });
      listContainer.innerHTML = html;
    }

    // Escape HTML
    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    // ============================
    // DRAGGABLE (Assistive Touch)
    // ============================
    let isDragging = false;
    let startX, startY, initialX, initialY;
    let hasMoved = false;

    fab.addEventListener('mousedown', dragStart);
    fab.addEventListener('touchstart', dragStart, { passive: false });
    document.addEventListener('mousemove', drag);
    document.addEventListener('touchmove', drag, { passive: false });
    document.addEventListener('mouseup', dragEnd);
    document.addEventListener('touchend', dragEnd);

    function dragStart(e) {
      isDragging = true;
      hasMoved = false;

      const rect = fab.getBoundingClientRect();
      initialX = rect.left;
      initialY = rect.top;

      if (e.type === 'touchstart') {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
      } else {
        startX = e.clientX;
        startY = e.clientY;
      }
    }

    function drag(e) {
      if (!isDragging) return;

      let currentX, currentY;
      if (e.type === 'touchmove') {
        currentX = e.touches[0].clientX;
        currentY = e.touches[0].clientY;
      } else {
        currentX = e.clientX;
        currentY = e.clientY;
      }

      const deltaX = currentX - startX;
      const deltaY = currentY - startY;

      if (Math.abs(deltaX) > 5 || Math.abs(deltaY) > 5) {
        hasMoved = true;
        fab.classList.add('dragging');

        let newX = initialX + deltaX;
        let newY = initialY + deltaY;

        // Boundary check
        const maxX = window.innerWidth - fab.offsetWidth;
        const maxY = window.innerHeight - fab.offsetHeight;
        newX = Math.max(0, Math.min(newX, maxX));
        newY = Math.max(0, Math.min(newY, maxY));

        fab.style.left = newX + 'px';
        fab.style.top = newY + 'px';
        fab.style.right = 'auto';
        fab.style.bottom = 'auto';

        e.preventDefault();
      }
    }

    function dragEnd(e) {
      isDragging = false;
      if (hasMoved) {
        // Prevent click event after drag
        setTimeout(function() {
          fab.classList.remove('dragging');
        }, 100);
      }
    }

  })();
</script>
