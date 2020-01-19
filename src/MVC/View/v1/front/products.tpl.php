<?php $this->includeTemplate('front/head.tpl.php')?>

<div class="container">

    <?php $this->includeTemplate('front/front-message-div.tpl.php')?>

    <div class="py-5 text-center">
        <p><a href="/" title="back to Home">BACK TO HOME</a></p>
        <h2>Add Products</h2>
        <p class="lead">Enter products details</p>
    </div>

    <div class="row">
        <form method="post" name="form_product" action="">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="SKU">SKU (no duplicates, case-insensitive)</label>
                    <input type="text" class="form-control" name="sku" id="sku" placeholder="e.g: A, B or C" value="<?php if(isset($this->product_sku)) {echo $this->product_sku;}?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="lastName">Product name</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="limit to 255 characters" value="<?php if(isset($this->product_name)) {echo $this->product_name;}?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="lastName">Price per unit (decimal or numeric)</label>
                    <input type="text" class="form-control" id="price" name="price" placeholder="Numeric value" value="<?php if(isset($this->product_price)) {echo $this->product_price;}?>" required>
                </div>
            </div>
            <hr class="mb-4">
            <div class="text-center mb-4">
                <input class="btn btn-primary btn-lg btn-block" type="submit" name="btn_create" value="Create this new product">
            </div>
        </form>
    </div>

</div>

<?php $this->includeTemplate('front/footer.tpl.php')?>
