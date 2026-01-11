<?php
if (!defined('ABSPATH')) {
    exit;
}

use Swiver\Swiver_WooCommerce\Swiver_Helper;

$company_data = Swiver_Helper::get_api_data('data');
?>

<div class="row">
    <div class="col-6">
        <div class="card p-0">
            <h5 class="card-header bg-primary-subtle p-3 text-white"><?php echo esc_html(__('Company Details', 'swiver')); ?></h5>
            <div class="card-body">
                <?php if (!empty($company_data) && is_array($company_data)): ?>
                    <div class="vstack gap-3">
                        <?php if (isset($company_data['company']['name'])): ?>
                            <div class="p-2">
                                <h6><?php echo esc_html(__('Company', 'swiver')); ?>:</h6>
                                <strong><?php echo esc_html($company_data['company']['name']); ?></strong>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($company_data['user_name'])): ?>
                            <div class="p-2">
                                <h6><?php echo esc_html(__('User Name', 'swiver')); ?>:</h6>
                                <?php echo esc_html($company_data['user_name']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($company_data['website']) && !empty($company_data['website'])): ?>
                            <div class="p-2">
                                <h6><?php echo esc_html(__('Website', 'swiver')); ?>:</h6>
                                <a href="<?php echo esc_url($company_data['website']); ?>" target="_blank" rel="noopener">
                                    <?php echo esc_html($company_data['website']); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($company_data['id'])): ?>
                            <div class="p-2">
                                <h6><?php echo esc_html(__('Integration ID', 'swiver')); ?>:</h6>
                                <code><?php echo esc_html($company_data['id']); ?></code>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted"><?php echo esc_html__('Company data not available.', 'swiver'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-6">
        <?php
        require SWIVER_PLUGIN_DIR . 'templates/admin/partials/cards/taxes-card.php';
        require SWIVER_PLUGIN_DIR . 'templates/admin/partials/cards/brands-card.php';
        ?>
    </div>
</div>