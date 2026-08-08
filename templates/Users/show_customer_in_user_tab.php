<?php echo $this->Html->script(array('ajaxfileupload'));?>
<div class="slide_rht_con" style="display:none" id="user_customer_tab"></div>
<script>
$(document).ready(function(){
    invoiceCustomers();
});
function invoiceCustomers(){
    var act_url = HTTP_ROOT +'Projects/ajaxCustomerLists';
    var params = {};
    params.sortby = getCookie("INVOICE_SORTBY");
    params.order = getCookie("INVOICE_SORTORDER");
    var limit = ''
    page = 1;
    $.post(act_url,{page:page,params:params, limit:limit},function(res){
        $('#caseLoader').hide();
        $('#user_customer_tab').show();
        $('#user_customer_tab').html(res);
        $('[rel="tooltip"]').tipsy();
        $.material.init();
    });
}
var sendInvoiceEmailURL = "<?php echo $this->Url->build(array('controller' => 'invoices', 'action' => 'sendInvoiceEmail')) ?>";
var createInvoicePdfURL = "<?php echo $this->Url->build(array('controller' => 'invoices', 'action' => 'createInvoicePdf')) ?>";
var updateInvoicedropdownURL = "<?php echo $this->Url->build(array('controller'=>'invoices','action'=>'updateInvoicedropdown'));?>";
var add2InvoiceURL = "<?php echo $this->Url->build(array('controller'=>'invoices','action'=>'add2Invoice'));?>";
var ajaxInvoicePageURL = "<?php echo $this->Url->build(array('controller'=>'invoices','action'=>'ajaxInvoicePage'));?>";
var getCountInvoiceURL = "<?php echo $this->Url->build(array('controller'=>'easycases','action'=>'getCountInvoice'));?>";
var ajaxInvoiceListURL = "<?php echo $this->Url->build(array('controller'=>'invoices','action'=>'ajaxInvoiceList'));?>";
var ajaxCustomerListURL = "<?php echo $this->Url->build(array('controller'=>'Projects','action'=>'ajaxCustomerLists'));?>";
var ajaxTimeListURL = "<?php echo $this->Url->build(array('controller'=>'invoices','action'=>'ajaxTimeList'));?>";
var customer_detailsURL = "<?php echo $this->Url->build(array('controller'=>'Projects','action'=>'customerDetails'));?>";
var addInvoiceURL = "<?php print $this->Url->build(array('controller'=>'easycases','action'=>'addInvoice'));?>";
var deleteInvoiceURL = "<?php echo $this->Url->build(array('controller'=>'invoices','action'=>'deleteInvoice'));?>";
var deleteInvoiceTimeLogURL = "<?php echo $this->Url->build(array('controller' => 'invoices', 'action' => 'deleteInvoiceTimeLog')) ?>";
var downloadPdfURL = "<?php echo $this->Url->build(array('controller' => 'invoices', 'action' => 'downloadPdf')) ?>";
var invoiceLogoURL = "<?php echo $this->Url->build(array('controller' => 'invoices', 'action' => 'invoiceLogo')) ?>";
var opt_it_from = "<?php echo $from ?? '';?>";
var emode;
</script>
