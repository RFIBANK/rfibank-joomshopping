<?php
//защита от прямого доступа
defined('_JEXEC') or die();
?>
<div class="col100">
    <fieldset class="adminform">
        <table class="admintable" width="100%">
            <tr>
                <td class="key">
                    <?=ADMIN_CFG_RFICB_SECRET_KEY?>:
                </td>
                <td>
                    <input type="text" name="pm_params[rficb_secret_key]" class="inputbox" value="<?=$params['rficb_secret_key']?>">
                </td>
                <td>
                    <?=JHtml::tooltip(ADMIN_CFG_RFICB_SECRET_KEY_DESCRIPTION)?>
                </td>
            </tr>
            <tr>
                <td class="key">
                    <?=ADMIN_CFG_RFICB_KEY?>:
                </td>
                <td>
                    <input type="text" name="pm_params[rficb_key]" class="inputbox" value="<?=$params['rficb_key']?>">
                </td>
                <td>
                    <?=JHtml::tooltip(ADMIN_CFG_RFICB_KEY_DESCRIPTION)?>
                </td>
            </tr>
            <tr>
                <td class="key">
                    <?php echo _JSHOP_TRANSACTION_END;?>:
                </td>
                <td>
                    <?php print JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_end_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_end_status'] );?>
                </td>
            </tr>
            <tr>
                <td class="key">
                    <?php echo _JSHOP_TRANSACTION_PENDING;?>:
                </td>
                <td>
                    <?php echo JHTML::_('select.genericlist',$orders->getAllOrderStatus(), 'pm_params[transaction_pending_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_pending_status']);?>
                </td>
            </tr>
            <tr>
                <td class="key">
                    <?php echo _JSHOP_TRANSACTION_FAILED;?>:
                </td>
                <td>
                    <?php echo JHTML::_('select.genericlist',$orders->getAllOrderStatus(), 'pm_params[transaction_failed_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_failed_status']);?>
                </td>
            </tr>
        </table>
    </fieldset>
</div>
<div class="clr"></div>
