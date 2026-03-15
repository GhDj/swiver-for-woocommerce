<?php
if (!defined('ABSPATH')) {
    exit;
}

use Swiver\Swiver_WooCommerce\Swiver_Helper;

?>

<div class="card p-0">
    <h5 class="card-header bg-primary-subtle p-3 text-white"><?php echo esc_html(__('Categories', 'swiver-for-woocommerce')); ?></h5>
    <div class="card-body">
        <?php
        $categories = Swiver_Helper::get_api_data('categories');
        if (!empty($categories) && is_array($categories)):
        ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($categories as $category): ?>
                    <li class="list-group-item">
                        <strong><?php echo esc_html($category['label']); ?></strong>
                        <?php if (!empty($category['description'])): ?>
                            <br/>
                            <small class="text-muted">
                                <?php echo esc_html($category['description']); ?>
                            </small>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted"><?php echo esc_html__('No categories available.', 'swiver-for-woocommerce'); ?></p>
        <?php endif; ?>
    </div>
</div>