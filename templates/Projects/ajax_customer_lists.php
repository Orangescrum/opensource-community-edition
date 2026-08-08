<div class="task_listing timelog_lview  timelog-table setting_wrapper task_listing user_subscribe_page">
    <div class="tlog_top_cnt timelog-table-head plan_page">
       <div class="fl"><h3><?php echo __('Manage Customers');?></h3></div>
<div class="fr">
    <div class="btn-group m-top0">
        <?php #echo $this->Form->button('Download Sample CSV', array('type' => 'button', 'onclick'=>'invoices.download_csv_customer()', 'class' => 'btn btn_cmn_efect cmn_bg btn-info cmn_size mright5')); ?>
        <?php if($this->Format->isAllowed('Add Customer',$roleAccess)){ ?>
            <?php echo $this->Form->button(__('Add Customer',true), array('type' => 'button', 'onclick'=>'invoices.add_customer()', 'class' => 'btn btn_cmn_efect cmn_bg btn-info cmn_size')); ?>
        <?php } ?>
    </div>
</div>
<div class="cb"></div>
</div>
<div class="cb"></div>
<div class="m-cmn-flow">
    <table cellpadding="0" cellspacing="0" class="table table-striped table-hover m-invoice-table">
        <thead>
        <tr>
            <th style="width:20%">
                <a title="<?php echo __('Name');?>" onclick="invoices.ajaxSorting('customers', 'name', this);" class="anchor">
                    <?php echo __('Name');?>
                    <i class="material-icons"><?php if($order_by == 'name' && $order_sort != ''){echo strtolower($order_sort)=='asc'?'&#xE316;':'&#xE313;';}else{echo '&#xE164;';}?></i>
                </a>
            </th>
            <th style="width:10%"><?php echo __('Organization');?></th>
            <th style="width:40%" class="m-width20"><?php echo __('Address');?></th>
            <th style="width:10%" class="align_center">
                <a title="<?php echo __('Currency');?>" onclick="invoices.ajaxSorting('customers', 'currency', this);" class="anchor">
                    <?php echo __('Currency');?>
                    <i class="material-icons"><?php if($order_by=='currency' && $order_sort != ''){echo strtolower($order_sort)=='asc'?'&#xE316;':'&#xE313;';}else{echo '&#xE164;';}?></i>
                </a>
            </th>
            <th style="width:10%" class="align_center">
                <a title="<?php echo __('Status');?>" onclick="invoices.ajaxSorting('customers', 'status', this);" class="anchor">
                    <?php echo __('Status');?>
                    <i class="material-icons"><?php if($order_by=='status' && $order_sort != ''){echo strtolower($order_sort)=='asc'?'&#xE316;':'&#xE313;';}else{echo '&#xE164;';}?></i>
                </a>
            </th>
            <th style="width:5%" class="align_center"><?php echo __('Action');?></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($customers)) { ?>
            <?php foreach ($customers as $cust) { ?>
                <tr data-id="<?php echo $cust['InvoiceCustomer']['id']; ?>">
                    <td><?php echo $cust['InvoiceCustomer']['name']; ?></td>
                    <td><?php echo $cust['InvoiceCustomer']['organization']; ?></td>
                    <td><?php echo $cust['InvoiceCustomer']['details']; ?></td>
                    <td class="align_center"><?php echo $cust['InvoiceCustomer']['currency']; ?></td>
                    <td class="align_center"><?php if($cust['InvoiceCustomer']['status'] =='Active'){echo __('Active') ;}else{echo __('Inactive');} ?></td>
                    <td class="align_center edit-actiion"  title="<?php echo __('Edit');?>"> <!--tooltip -->
                        <?php if($this->Format->isAllowed('Edit Customer',$roleAccess)){ ?>
                            <a class="anchor edit_customer " style=""><i class="material-icons">&#xE254;</i></a>
                        <?php }else{ ?> --- <?php  } ?>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="6"><?php echo __('No records');?>......</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <div class="cb"></div>

</div>
<div>
    <input type="hidden" id="getcasecount" value="<?php echo $caseCount; ?>" readonly="true"/>
    <?php if ($caseCount > 0) { ?>
        <div class="cb"></div>
        <div id='showCustomers_paginate'></div>
        <script type="text/javascript">
            pgShLbl = '<?php echo $this->Format->pagingShowRecords($caseCount, $page_limit, $casePage); ?>';
            var pageVars = {pgShLbl:pgShLbl,csPage:<?php echo $casePage; ?>,page_limit:<?php echo $page_limit; ?>,caseCount:<?php echo $caseCount; ?>};
            //console.log(pageVars);
            $("#showCustomers_paginate").html(tmpl("paginate_tmpl", pageVars)).show();
        </script>
        <div class="cb"></div>
    <?php } ?>
    <input type="hidden" id="totalcount" name="totalcount" value="<?php echo $caseCount; ?>"/>
    <div class="cb"></div>
</div>

</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('.tooltip').tipsy({gravity:'s',fade:true});
    });

</script>
<script type="text/template" id="paginate_tmpl">
    <?php echo $this->element('task_paginate'); ?>
</script>
