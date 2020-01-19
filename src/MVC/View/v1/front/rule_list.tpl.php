<?php $this->includeTemplate('front/head.tpl.php')?>

<div class="container">

    <?php $this->includeTemplate('front/front-message-div.tpl.php')?>

    <div class="py-5 text-center">
        <p><a href="/" title="back to Home">BACK TO HOME</a></p>
        <h2>Rule List</h2>
        <p class="lead">here are the list of pricing rules, with pagination</p>
    </div>

    <!-- Dynamic Table Full Pagination -->
    <div class="block">

        <div class="block-content">
            <?php if (_is($this->ruleList)) :?>
                <?php $row = 1;?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped js-dataTable-full-pagination" id="myTable">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th style="">SKU</th>
                            <th style="">Occurrence</th>
                            <th style="" class="text-center">Promo Price</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($this->ruleList as $rule) :?>
                            <tr>
                                <td class="font-w700">
                                    <?php echo $row; ?>
                                </td>
                                <td class="font-w500"><?php if (_is($rule) && isset($rule['sku'])) {echo _db($rule['sku']);} ?></td>
                                <td>
                                    <?php if (_is($rule) && isset($rule['product_occurrence'])) {echo $rule['product_occurrence'];} ?>
                                </td>
                                <td class="text-center">
                                    <?php if (_is($rule) && isset($rule['promo_price'])) {echo _fn($rule['promo_price']);} ?>
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
                                <?php if (isset($this->last_page) && count($this->ruleList) > 1) echo ' to ' . $this->last_page?></strong>
                            ] of <?php if (isset($this->record_count)) echo $this->record_count?>
                        </h5>
                        <ul class="pagination">
                            <?php for ($pageCount = 1;$pageCount <= $this->pageCount;$pageCount++) :?>
                                <li <?php if (isset($this->currentPage) && ($this->currentPage == $pageCount) ) echo 'class="active"' ?>>
                                    <a href="<?php echo path('rule_list') . '/' . $pageCount?>">page <?php echo $pageCount?></a>
                                </li>
                            <?php endfor;?>
                        </ul>
                    </nav>
                <?php endif;?>
                <!-- END Pagination -->

            <?php else:?>
                <p>No rule(s) to display..</p>
            <?php endif;?>
        </div>
    </div>
    <!-- END Dynamic Table Full Pagination -->

</div>

<?php $this->includeTemplate('front/footer.tpl.php')?>
