<?php $this->includeTemplate('front/head.tpl.php')?>

<div class="container">

    <?php $this->includeTemplate('front/front-message-div.tpl.php')?>

    <div class="py-5 text-center">
        <p><a href="/" title="back to Home">BACK TO HOME</a></p>
        <h2>Cart & Checkout</h2>
        <p class="lead">here you can add items and checkout when ready</p>
    </div>

    <div class="row">
        <div class="col-md-4 order-md-2 mb-4">
            <h3 class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted">YOUR CART</span>
            </h3>
            <?php if (isset($this->itemList) && _is($this->itemList)) :?>
            <?php $checkout_total = 0; $total_cart_item = 0;?>
            <ul class="list-group mb-3">
                <?php foreach ($this->itemList as $item) :?>
                <li class="list-group-item d-flex justify-content-between lh-condensed">
                    <div>
                        <h6 class="my-0">ITEM: <?php if (_is($item) && isset($item['sku'])) {echo _db($item['sku']);} ?></h6>
                        <small class="text-muted"><i></i><?php if (_is($item) && isset($item['is_discounted']) && ($item['is_discounted'] == 1)) {echo 'Discount could be applicable';} ?></i></small>
                    </div>
                    <span class="text-muted">Qty: <?php if (_is($item) && isset($item['prod_qty'])) {echo _db($item['prod_qty']);} ?></span>
                    <br/>
                    <span class="text-muted">Price: <?php if (_is($item) && isset($item['total_price'])) {echo _db($item['total_price']);} ?></span>
                </li>

                    <?php $checkout_total  = ($checkout_total + (int)$item['total_price']);?>
                    <?php $total_cart_item = ($total_cart_item + (int)$item['prod_qty']);?>
                <?php endforeach;?>
                <li class="list-group-item d-flex justify-content-between">
                    <span>Total Price:</span>
                    <strong><?php echo _fn($checkout_total)?> | <i>(<?php echo _db($total_cart_item)?> items)</i></strong>
                </li>
            </ul>
            <form method="post" action="" name="checkout_form">
                <input class="btn btn-success btn-lg btn-block" type="submit" name="btn_checkout" value="Checkout">
                <input class="btn btn-warning btn-lg btn-block" type="submit" name="btn_clear" value="Clear Cart">
            </form>
            <?php else:?>
            <p>Cart is empty</p>
            <?php endif;?>
        </div>

        <div class="col-md-8 order-md-1">
            <h4 class="mb-3">ITEMS SCREEN</h4>
            <form class="needs-validation" method="post" action="" name="add_product_form">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="material-select2">Select product by sku:</label>
                        <select class="form-control" id="material-select2" name="sku_select" size="1" style="text-align:center;color:#9e61d8;border-bottom:1px solid #9e61d8;">
                            <option value="-1">- Select -</option>
                            <?php if (isset($this->productList) && _is($this->productList)) : ?>
                                <?php foreach ($this->productList as $product) :?>
                                    <option value="<?php echo _db($product['sku'])?>" <?php if(isset($this->sku_select) && ($this->sku_select == _db($product['sku']) )) {echo 'selected="selected"';}?>><?php echo _db($product['sku'])?></option>
                                <?php endforeach; ?>
                            <?php endif;?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="lastName">Quantity (numeric)</label>
                        <input type="text" class="form-control" id="add_qty" name="add_qty" placeholder="Numeric value" value="<?php if(isset($this->add_qty)) {echo $this->add_qty;}?>" required>
                    </div>
                </div>
                <hr class="mb-4">
                <input class="btn btn-primary btn-lg btn-block" type="submit" name="btn_add_product" value="Add product to cart">
            </form>
        </div>
    </div>
</div>

<?php $this->includeTemplate('front/footer.tpl.php')?>
