<?php
if (!defined('ABSPATH')) {
    exit;
}

use Swiver\Swiver_WooCommerce\Swiver_Helper;

?>

<div class="card p-0">
    <h5 class="card-header bg-primary-subtle p-3 text-white"><?php echo esc_html(__('Warehouses', 'swiver-for-woocommerce')); ?></h5>
    <div class="card-body">
        <?php
        $warehouses = Swiver_Helper::get_api_data('warehouses');
        if (!empty($warehouses) && is_array($warehouses)):
        ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($warehouses as $warehouse): ?>
                    <li class="list-group-item">
                        <strong><?php echo esc_html($warehouse['name']); ?></strong>
                        <?php if (isset($warehouse['address']['address'])): ?>
                            <br/>
                            <small class="text-muted">
                                <?php echo esc_html($warehouse['address']['address']); ?>
                            </small>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted"><?php echo esc_html__('No warehouses available.', 'swiver-for-woocommerce'); ?></p>
        <?php endif; ?>
    </div>
</div>