<?php
if (!defined('ABSPATH')) {
    exit;
}

use Swiver\Swiver_WooCommerce\Swiver_Helper;

?>

<div class="row mt-3">
    <div class="col-6">
        <?php include 'cards/warehouses-card.php'; ?>
    </div>
    <div class="col-6">
        <?php include 'cards/categories-card.php'; ?>
    </div>
</div>

<div class="accordion mt-3">
    <?php include 'accordions/units-accordion.php'; ?>
</div>