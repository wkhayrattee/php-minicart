<?php $this->includeTemplate('front/head.tpl.php')?>

<div class="container">

    <?php $this->includeTemplate('front/front-message-div.tpl.php')?>

    <div class="py-5 text-center">
        <p><a href="/" title="back to Home">BACK TO HOME</a></p>
        <h2>View Invoice</h2>
        <p class="lead">here you can see the final invoice generated</p>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card ">
                <div class="card-header">
                    <h3 class="text-xs-center"><strong>Order summary</strong></h3>
                </div>
                <div class="card-block">
                    <div class="table-responsive">
                        <?php if (isset($this->itemList) && _is($this->itemList)) :?>
                        <?php $total_without_promo = 0; $total_item = 0; $total_promo = 0;?>
                        <table class="table table-sm">
                            <thead>
                            <tr>
                                <td><strong>Item (SKU)</strong></td>
                                <td class="text-xs-center"><strong>Unit Price</strong></td>
                                <td class="text-xs-center"><strong>Item Quantity</strong></td>
                                <td class="text-xs-right"><strong>Total</strong></td>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($this->itemList as $item) :?>
                            <tr>
                                <td><?php if (_is($item) && isset($item['sku'])) {echo _db($item['sku']);} ?></td>
                                <td class="text-xs-center"><?php if (_is($item) && isset($item['unit_price'])) {echo _fn($item['unit_price']);} ?></td>
                                <td class="text-xs-center"><?php if (_is($item) && isset($item['prod_qty'])) {echo _db($item['prod_qty']);} ?></td>
                                <td class="text-xs-center"><?php if (_is($item) && isset($item['total_price'])) {echo _fn($item['total_price']);} ?></td>
                            </tr>
                                <?php
                                $total_without_promo = (($item['prod_qty'] * $item['unit_price'])) + $total_without_promo;
                                $total_item          = $total_item + $item['prod_qty'];
                                $total_promo         = $item['checkout_total'];
                                ?>
                            <?php endforeach;?>

                            <tr>
                                <td class="highrow"></td>
                                <td class="highrow"></td>
                                <td class="highrow text-xs-center"><strong>Subtotal (without promo)</strong></td>
                                <td class="highrow text-xs-right"><?php echo _fn($total_without_promo)?></td>
                            </tr>
                            <tr>
                                <td class="emptyrow"></td>
                                <td class="emptyrow"></td>
                                <td class="emptyrow text-xs-center"><strong>Promo (discount)</strong></td>
                                <td class="emptyrow text-xs-right"><?php echo _fn($total_without_promo - $total_promo)?></td>
                            </tr>
                            <tr>
                                <td class="emptyrow"><i class="fa fa-barcode iconbig"></i></td>
                                <td class="emptyrow"></td>
                                <td class="emptyrow text-xs-center"><strong>Total (for <?php echo _db($total_item)?> items)</strong></td>
                                <td class="emptyrow text-xs-right"><?php echo _fn($total_promo)?></td>
                            </tr>
                            </tbody>
                        </table>
                        <?php else:?>
                        <p>No such invoice present in our system</p>
                        <?php endif;?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .height {
            min-height: 200px;
        }

        .icon {
            font-size: 47px;
            color: #5CB85C;
        }

        .iconbig {
            font-size: 77px;
            color: #5CB85C;
        }

        .table > tbody > tr > .emptyrow {
            border-top: none;
        }

        .table > thead > tr > .emptyrow {
            border-bottom: none;
        }

        .table > tbody > tr > .highrow {
            border-top: 3px solid;
        }
    </style>

</div>

<?php $this->includeTemplate('front/footer.tpl.php')?>
