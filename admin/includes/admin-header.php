<!-- Admin Header -->
<div class="admin-topbar">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <a href="<?php echo SITE_URL; ?>" class="admin-logo" target="_blank">
            <?php echo e(SITE_NAME); ?> <span style="font-size: 0.875rem; opacity: 0.7;">↗</span>
        </a>
        
        <div style="display: flex; align-items: center; gap: 1.5rem;">
            <span style="color: #e5e5e5;">
                <?php echo e($auth->getCurrentUser()['full_name']); ?>
            </span>
            <a href="<?php echo ADMIN_URL; ?>/logout.php" 
               style="color: #e5e5e5; font-weight: 600; padding: 0.5rem 1rem; background: rgba(255,255,255,0.1); border-radius: 0.375rem;">
                Cerrar Sesión
            </a>
        </div>
    </div>
</div>
