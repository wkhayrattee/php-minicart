<?php $this->includeTemplate('front/head.tpl.php')?>

<div class="container">

    <?php $this->includeTemplate('front/front-message-div.tpl.php')?>

    <div class="py-5 text-center">
        <p><a href="/" title="back to Home">BACK TO HOME</a></p>
        <h2>Add New Pricing Rule</h2>
        <p class="lead">Enter rule details</p>
    </div>

    <div class="row">
        <form method="post" name="form_product" action="">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="material-select2">Select product by sku:</label>
                    <select class="form-control" id="material-select2" name="sku_select" size="1" style="text-align:center;color:#9e61d8;border-bottom:1px solid #9e61d8;">
                        <option value="-1">- Select -</option><!-- should remain Empty value -->
                        <?php if (isset($this->productList) && _is($this->productList)) : ?>
                            <?php foreach ($this->productList as $product) :?>
                                <option value="<?php echo _db($product['sku'])?>" <?php if(isset($this->sku_select) && ($this->sku_select == _db($product['sku']) )) {echo 'selected="selected"';}?>><?php echo _db($product['sku'])?></option>
                            <?php endforeach; ?>
                        <?php endif;?>
                    </select>
                </div>


                <div class="col-md-4 mb-3">
                    <label for="lastName">Occurrence (pieces per batch, e.g: 3 for AAA)</label>
                    <input type="text" class="form-control" id="prod_count" name="prod_count" placeholder="Numeric value" value="<?php if(isset($this->prod_count)) {echo $this->prod_count;}?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="lastName">Promo price (decimal or numeric)</label>
                    <input type="text" class="form-control" id="prod_promo" name="prod_promo" placeholder="Numeric value" value="<?php if(isset($this->prod_promo)) {echo $this->prod_promo;}?>" required>
                </div>
            </div>
            <hr class="mb-4">
            <div class="text-center mb-4">
                <input class="btn btn-primary btn-lg btn-block" type="submit" name="btn_create" value="Create this new Rule">
            </div>
        </form>
    </div>

</div>

<?php $this->includeTemplate('front/footer.tpl.php')?>
