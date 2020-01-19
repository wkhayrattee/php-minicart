<?php $this->includeTemplate('front/head.tpl.php')?>

<div class="container">
    <div class="jumbotron mt-3">
        <h1>The Wak MiniCart Project</h1>
        <p class="lead">Emphasis has been put on simplicity</p>
        <p class="lead">Please select a menu below:</p>
        <a class="btn btn-lg btn-primary" href="<?php echo path('products')?>" role="button">Create Products</a>
        <a class="btn btn-lg btn-primary" href="<?php echo path('product_list')?>" role="button">Show Products</a>
        <a class="btn btn-lg btn-primary" href="<?php echo path('price_rule_create')?>" role="button">Create New Pricing Rule</a>
        <a class="btn btn-lg btn-primary" href="<?php echo path('rule_list')?>" role="button">Show Rules</a>
        <a class="btn btn-lg btn-primary" href="<?php echo path('cart')?>" role="button">Proceed to CART</a>
    </div>
</div>

<?php $this->includeTemplate('front/footer.tpl.php')?>
