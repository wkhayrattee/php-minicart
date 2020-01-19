<?php $this->includeTemplate('front/head.tpl.php')?>

<div class="container">

    <?php $this->includeTemplate('front/front-message-div.tpl.php')?>

    <div class="py-5 text-center">
        <p><a href="/" title="back to Home">BACK TO HOME</a></p>
        <h2>Product List</h2>
        <p class="lead">here are the list products, with pagination</p>
    </div>

    <!-- Dynamic Table Full Pagination -->
    <div class="block">

        <div class="block-content">
            <?php if (_is($this->productList)) :?>
            <?php $row = 1;?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped js-dataTable-full-pagination" id="myTable">
                        <thead>
                        <tr>
                            <th style="">#</th>
                            <th style="">Name</th>
                            <th style="">SKU</th>
                            <th style="" class="text-center">Price</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($this->productList as $product) :?>
                            <tr>
                                <td class="font-w700">
                                    <?php echo $row; ?>
                                </td>
                                <td class="font-w500"><?php if (_is($product) && isset($product['prod_name'])) {echo _db($product['prod_name']);} ?></td>
                                <td>
                                    <?php if (_is($product) && isset($product['sku'])) {echo _db($product['sku']);} ?>
                                </td>
                                <td class="text-center">
                                    <?php if (_is($product) && isset($product['price'])) {echo _fn($product['price']);} ?>
                                </td>
                            </tr>
                        <?php $row += 1;?>
                        <?php endforeach;?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (isset($this->pageCount) && ($this->pageCount >= 1)) :?>
                    <nav class="text-right">
                        <h5 style="color:orange;">
                            Showing topics [ <strong><?php if (isset($this->start_page)) echo $this->start_page?>
                                <?php if (isset($this->last_page) && count($this->productList) > 1) echo ' to ' . $this->last_page?></strong>
                            ] of <?php if (isset($this->record_count)) echo $this->record_count?>
                        </h5>
                        <ul class="pagination">
                            <?php for ($pageCount = 1;$pageCount <= $this->pageCount;$pageCount++) :?>
                                <li <?php if (isset($this->currentPage) && ($this->currentPage == $pageCount) ) echo 'class="active"' ?>>
                                    <a href="<?php echo path('product_list') . '/' . $pageCount?>">page <?php echo $pageCount?></a>
                                </li>
                            <?php endfor;?>
                        </ul>
                    </nav>
                <?php endif;?>
                <!-- END Pagination -->

            <?php else:?>
                <p>No product(s) to display..</p>
            <?php endif;?>
        </div>
    </div>
    <!-- END Dynamic Table Full Pagination -->

</div>

<?php $this->includeTemplate('front/footer.tpl.php')?>
