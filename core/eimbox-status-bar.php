<?php if (isset($statusbar) && $statusbar === true): ?>
<!-- VS Code Like Status Bar -->
<div id="eimbox-status-bar">
    <!-- Left Section -->
    <div id="statusBar-left" class="status-bar-section">
        <div id="status-main" class="status-bar-item">
            <i id="status-icon" class="bi bi-check-all"></i>
            <span id="status-text">Ready</span>
        </div>
        <div id="status-branch" class="status-bar-item">
            <i class="bi bi-git"></i>
            <span>main</span>
        </div>
    </div>




    
    <!-- Right Section -->
    <div id="statusBar-right" class="status-bar-section">
        <div id="status-notifications" class="status-bar-item">
            <i class="bi bi-bell"></i>
            <span id="notification-count">0</span>
        </div>
    </div>
</div>
<?php endif; ?>