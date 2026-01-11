<?php
if (!defined('ABSPATH')) {
    exit;
}

use Swiver\Swiver_WooCommerce\Swiver_Helper;

?>

<div class="accordion-item mb-3">
    <h2 class="accordion-header mt-0">
        <button class="accordion-button collapsed bg-primary-subtle text-white" type="button"
                data-bs-toggle="collapse" data-bs-target="#units" aria-expanded="false"
                aria-controls="units">
            <?php echo esc_html(__('Units', 'swiver')); ?>
        </button>
    </h2>
    <div id="units" class="accordion-collapse collapse" data-bs-parent="">
        <div class="accordion-body">
            <?php
            $units = Swiver_Helper::get_api_data('units');
            if (!empty($units) && is_array($units)):
            ?>
                <table class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th scope="col"><?php echo esc_html__('ID', 'swiver'); ?></th>
                        <th scope="col"><?php echo esc_html__('Name', 'swiver'); ?></th>
                        <th scope="col"><?php echo esc_html__('Code', 'swiver'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($units as $unit): ?>
                        <tr>
                            <td><?php echo esc_html($unit['id']); ?></td>
                            <td><?php echo esc_html($unit['name']); ?></td>
                            <td><code><?php echo esc_html($unit['code']); ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted"><?php echo esc_html__('No units available.', 'swiver'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>