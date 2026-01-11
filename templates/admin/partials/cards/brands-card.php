<?php
if (!defined('ABSPATH')) {
    exit;
}

use Swiver\Swiver_WooCommerce\Swiver_Helper;

?>

<div class="card p-0">
    <h5 class="card-header bg-primary-subtle p-3 text-white"><?php echo esc_html(__('Brands', 'swiver')); ?></h5>
    <div class="card-body">
        <?php
        $brands = Swiver_Helper::get_api_data('brands');
        if (!empty($brands) && is_array($brands)):
        ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($brands as $brand): ?>
                    <li class="list-group-item">
                        <?php echo esc_html($brand['name']); ?>
                        <?php if (isset($brand['enabled']) && $brand['enabled']): ?>
                            <span class="badge bg-success"><?php echo esc_html__('Enabled', 'swiver'); ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted"><?php echo esc_html__('No brands available.', 'swiver'); ?></p>
        <?php endif; ?>
    </div>
</div>