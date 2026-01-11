<?php
if (!defined('ABSPATH')) {
    exit;
}

use Swiver\Swiver_WooCommerce\Swiver_Helper;

$taxes = Swiver_Helper::get_api_data()['taxes'] ?? [];
$unmatched_count = 0;
foreach ($taxes as $tax) {
    if (empty($tax['wc'])) {
        $unmatched_count++;
    }
}
?>

<div class="card p-0">
    <div class="card-header bg-primary-subtle p-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-white"><?php echo esc_html(__('Taxes', 'swiver')); ?></h5>
        <?php if ($unmatched_count > 0): ?>
            <button type="button" id="swiver-add-all-taxes-btn" class="btn btn-sm btn-light">
                <?php echo esc_html(sprintf(__('Add all (%d)', 'swiver'), $unmatched_count)); ?>
            </button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (!empty($taxes)): ?>
            <div class="vstack gap-2">
                <?php foreach ($taxes as $tax): ?>
                    <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                        <span>
                            <?php echo esc_html($tax['name'] ?: __('Tax', 'swiver')); ?>:
                            <strong><?php echo esc_html($tax['rate']); ?>%</strong>
                        </span>
                        <div class="d-flex align-items-center gap-2">
                            <?php if ($tax['wc']): ?>
                                <span class="badge bg-success"><?php echo esc_html(__('Matched', 'swiver')); ?></span>
                                <?php if (!empty($tax['wc_name'])): ?>
                                    <small class="text-muted">
                                        <span class="dashicons dashicons-arrow-right-alt" style="font-size: 14px; width: 14px; height: 14px; vertical-align: middle;"></span>
                                        <?php echo esc_html($tax['wc_name']); ?>
                                    </small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge bg-warning"><?php echo esc_html(__('Not in WooCommerce', 'swiver')); ?></span>
                                <button type="button"
                                        class="btn btn-sm btn-primary swiver-add-tax-btn"
                                        data-tax-id="<?php echo esc_attr($tax['id']); ?>"
                                        data-tax-rate="<?php echo esc_attr($tax['rate']); ?>"
                                        data-tax-name="<?php echo esc_attr($tax['name']); ?>">
                                    <?php echo esc_html(__('Add to WooCommerce', 'swiver')); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted mb-0"><?php echo esc_html(__('No taxes found.', 'swiver')); ?></p>
        <?php endif; ?>
    </div>
</div>
