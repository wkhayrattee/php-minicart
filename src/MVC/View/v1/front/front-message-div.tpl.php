<!-- MESSAGE Div -->
<div class="row">
    <div class="bg-gray-lighter col-sm-6 col-sm-offset-3" style="<?php if(isset($this->message_div_visibility)) { echo $this->message_div_visibility; } else { echo 'display:none'; }?>">
        <div style="text-align:left;padding: 10px 0px 0px 0px;" class="<?php if(isset($this->message_state_css_class)) echo $this->message_state_css_class?>">
            <label>
                <?php if (isset($this->errorList) && \Wak\Common\Validator::notEmptyOrNull($this->errorList)):?>
                <ul style="font-weight: bold;font-size:medium;">
                <?php foreach($this->errorList as $msg) {
                        echo "<li>$msg</li>";
                    } ?>
                </ul>
                <?php endif; ?>
            </label>
        </div>
    </div>
</div>
<!-- END MESSAGE Div -->