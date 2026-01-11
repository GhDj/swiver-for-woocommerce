<?php
if (!defined('ABSPATH')) {
    exit;
}

use Swiver\Swiver_WooCommerce\Swiver_Helper;

$api_data = Swiver_Helper::get_api_data();
$is_connected = Swiver_Helper::is_connected();
$company_name = Swiver_Helper::get_company_name();
$last_sync = Swiver_Helper::get_last_sync_formatted();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center border-bottom mt-3 pb-3 mb-3">
        <h2 class="mb-0"><?php echo esc_html__('Swiver Settings', 'swiver'); ?></h2>
        <?php if ($is_connected): ?>
            <button type="button" id="swiver-resync-btn" class="btn btn-outline-primary">
                <span class="dashicons dashicons-update" style="vertical-align: middle;"></span>
                <?php echo esc_html__('Resync', 'swiver'); ?>
            </button>
        <?php endif; ?>
    </div>

    <!-- Connection Status Indicator -->
    <div class="card mb-4" style="border-left: 4px solid <?php echo esc_attr( $is_connected ? '#28a745' : '#dc3545' ); ?>;">
        <div class="card-body py-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <span class="badge <?php echo esc_attr( $is_connected ? 'bg-success' : 'bg-danger' ); ?> me-3">
                        <?php echo $is_connected ? esc_html__('Connected', 'swiver') : esc_html__('Not Connected', 'swiver'); ?>
                    </span>
                    <?php if ($is_connected && $company_name): ?>
                        <span class="text-muted">
                            <?php echo esc_html($company_name); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <?php if ($is_connected): ?>
                    <small class="text-muted">
                        <?php echo esc_html__('Last synced:', 'swiver'); ?> <?php echo esc_html($last_sync); ?>
                    </small>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Messages container -->
    <div id="swiver-messages"></div>

    <?php if (empty($api_data)): ?>
        <!-- Not connected: show token input -->
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="mt-4">
                    <div class="input-group">
                        <span class="input-group-text"><?php echo esc_html__('API Token', 'swiver'); ?></span>
                        <input type="password"
                               class="form-control"
                               id="swiver_token"
                               name="swiver_token"
                               value=""
                               aria-label="<?php echo esc_attr__('API Token', 'swiver'); ?>"
                               placeholder="<?php echo esc_attr__('Enter your Swiver API token', 'swiver'); ?>">
                    </div>
                    <div class="mt-3">
                        <button type="button" id="swiver-sync-btn" class="btn btn-primary float-end">
                            <?php echo esc_html__('Synchronize', 'swiver'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Connected: show full width content -->
        <div class="row">
            <div class="col-12">
                <!-- Company details section -->
                <?php include SWIVER_PLUGIN_DIR . 'templates/admin/partials/company-details.php'; ?>

                <!-- Business data sections -->
                <?php include SWIVER_PLUGIN_DIR . 'templates/admin/partials/data-sections.php'; ?>
            </div>
        </div>

        <!-- Disconnect button at the end -->
        <div class="row mt-5">
            <div class="col-12">
                <hr>
                <div class="d-flex justify-content-end">
                    <button type="button" id="swiver-disconnect-btn" class="btn btn-danger">
                        <?php echo esc_html__('Disconnect', 'swiver'); ?>
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
