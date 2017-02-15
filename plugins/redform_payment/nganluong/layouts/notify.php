<?php
/**
 * @package     Redform
 * @subpackage  Payment.stripe
 * @copyright   Copyright (C) 2008-2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

$action = $displayData['action'];
$details = $displayData['details'];
$params = $displayData['params'];
$price = $displayData['price'];
$request = $displayData['request'];
$return_url = $displayData['return'];
$cancel_url = $displayData['cancel_return'];
$paymentType = $displayData['payment_type'];
/*Form submission: Billing info Redshop.vn*/
?>
<script type="text/javascript">
	document.title = 'Thanh toán ';
</script>
<h3 class="title_payment"><?php echo JText::_('COM_REDFORM_BILLING_TITLE'); ?></h3>
<style>
@media (max-width: 767px) {
	.list-content li .boxContent
	{
		width: 100% !important;
	}
	.list-content li .boxContent ul
	{
		height:auto !important;
	}
}
	.regularsubmit
	{
		line-height: 40px;
		font-family: 'latobold', san-serif;
		padding: 5px 69px !important;
		background: #2ab822;
		border:0;
		text-transform:uppercase;
		color:#fff;
		font-size: 18px;
		font-weight:normal;
		text-shadow:none;
		margin-bottom:30px;
		margin-top:15px;
	}
	.regularsubmit:hover,.regularsubmit:focus,.regularsubmit:active
	{
		background: rgba(42,184,34, 0.8);
		color:#fff;
	}
	h3.title_payment
	{
		background: #ebebeb;
	    padding: 10px 0;
	    text-transform: uppercase;
	    text-align: center;
	    font-family: 'latosemibold', san-serif;
		text-align:center;
		text-transform:uppercase;
		margin-top: 42px;
	    font-size: 24px;
	    padding-top: 7px;
	    padding-bottom: 15px;
	    margin-bottom:30px;

	}
	ul.bankList {
		clear: both;
		height: 202px;
		width: 636px;
	}
	ul.list-content
	{
		padding-left:0;
	}
	ul.list-content li label
	{
		font-weight:normal;
		font-family:latoregular, Helvetica, Arial, sans-serif;
    	font-size: 18px !important;
    	padding-left:10px;
	}
	ul.list-content li label input
	{
	    position: relative;
	    top: 1px;
    	margin-right: 5px;
	}
    
	ul.bankList li {
		list-style-position: outside;
		list-style-type: none;
		cursor: pointer;
		float: left;
		margin-right: 0;
		padding: 5px 2px;
		text-align: center;
		width: 90px;
	}
	.list-content li {
		list-style: none outside none;
		margin: 0 0 10px;
		display: block;
    	clear: both;
	}
	
	.list-content li .boxContent {
		display: none;
		width: 636px;
		border:1px solid #cccccc;
		padding:10px; 
	}
	.list-content li.active .boxContent {
		display: block;
	}
	.list-content li .boxContent ul {
		height:280px;
	}
	
	i.VISA, i.MASTE, i.AMREX, i.JCB, i.VCB, i.TCB, i.MB, i.VIB, i.ICB, i.EXB, i.ACB, i.HDB, i.MSB, i.NVB, i.DAB, i.SHB, i.OJB, i.SEA, i.TPB, i.PGB, i.BIDV, i.AGB, i.SCB, i.VPB, i.VAB, i.GPB, i.SGB,i.NAB,i.BAB 
	{ width:80px; height:30px; display:block; background:url(https://www.nganluong.vn/webskins/skins/nganluong/checkout/version3/images/bank_logo.png) no-repeat;}
	i.MASTE { background-position:0px -31px}
	i.AMREX { background-position:0px -62px}
	i.JCB { background-position:0px -93px;}
	i.VCB { background-position:0px -124px;}
	i.TCB { background-position:0px -155px;}
	i.MB { background-position:0px -186px;}
	i.VIB { background-position:0px -217px;}
	i.ICB { background-position:0px -248px;}
	i.EXB { background-position:0px -279px;}
	i.ACB { background-position:0px -310px;}
	i.HDB { background-position:0px -341px;}
	i.MSB { background-position:0px -372px;}
	i.NVB { background-position:0px -403px;}
	i.DAB { background-position:0px -434px;}
	i.SHB { background-position:0px -465px;}
	i.OJB { background-position:0px -496px;}
	i.SEA { background-position:0px -527px;}
	i.TPB { background-position:0px -558px;}
	i.PGB { background-position:0px -589px;}
	i.BIDV { background-position:0px -620px;}
	i.AGB { background-position:0px -651px;}
	i.SCB { background-position:0px -682px;}
	i.VPB { background-position:0px -713px;}
	i.VAB { background-position:0px -744px;}
	i.GPB { background-position:0px -775px;}
	i.SGB { background-position:0px -806px;}
	i.NAB { background-position:0px -837px;}
	i.BAB { background-position:0px -868px;}
	
	ul.cardList li {
		cursor: pointer;
		float: left;
		margin-right: 0;
		padding: 5px 4px;
		text-align: center;
		width: 90px;
		clear: none;
	    float: left;
	    display: inline-block;
	}
</style>
<form  name="NLpayBank" action="<?php echo $action; ?>" method="post">		
	<ul class="list-content">
		<?php if (in_array('wallet', $paymentType)): ?>
		<li class="active">
			<label><input type="radio" value="NL" name="option_payment"><?php echo JText::_('PLG_REDFORM_PAYMENT_NGANLUONG_CHECKOUT_NGANLUONG_WALLET'); ?></label>
			<div class="boxContent">
				<p>
					<?php echo JText::_('PLG_REDFORM_PAYMENT_NGANLUONG_CHECKOUT_NGANLUONG_WALLET_DESC'); ?>
				</p>
			</div>
		</li>
		<?php endif; ?>
		<?php if (in_array('online', $paymentType)): ?>
		<li>
			<label><input type="radio" value="ATM_ONLINE" name="option_payment"><?php echo JText::_('PLG_REDFORM_PAYMENT_NGANLUONG_CHECKOUT_ATM_ONLINE'); ?></label>
			<div class="boxContent">
				<p><i>
				<span style="color:#ff5a00;font-weight:bold;text-decoration:underline;"><?php echo JText::_('PLG_REDFORM_PAYMENT_NGANLUONG_NOTICE'); ?></span>: <?php echo JText::_('PLG_REDFORM_PAYMENT_NGANLUONG_CHECKOUT_ATM_ONLINE_DESC'); ?></i></p>	
					<ul class="cardList clearfix">
						<li class="bank-online-methods ">
							<label for="vcb_ck_on">
								<i class="BIDV" title="Ngân hàng TMCP Đầu tư &amp; Phát triển Việt Nam"></i>
								<input type="radio" value="BIDV"  name="bankcode" >
							</label>
						</li>
						<li class="bank-online-methods ">
							<label for="vcb_ck_on">
								<i class="VCB" title="Ngân hàng TMCP Ngoại Thương Việt Nam"></i>
								<input type="radio" value="VCB"  name="bankcode" >
							
							</label>
						</li>
						<li class="bank-online-methods ">
							<label for="vnbc_ck_on">
								<i class="DAB" title="Ngân hàng Đông Á"></i>
								<input type="radio" value="DAB"  name="bankcode" >
							
							</label>
						</li>
						<li class="bank-online-methods ">
							<label for="tcb_ck_on">
								<i class="TCB" title="Ngân hàng Kỹ Thương"></i>
								<input type="radio" value="TCB"  name="bankcode" >
							</label>
						</li>
						<li class="bank-online-methods ">
							<label for="sml_atm_mb_ck_on">
								<i class="MB" title="Ngân hàng Quân Đội"></i>
								<input type="radio" value="MB"  name="bankcode" >
							
							</label>
						</li>
						<li class="bank-online-methods ">
							<label for="sml_atm_vib_ck_on">
								<i class="VIB" title="Ngân hàng Quốc tế"></i>
								<input type="radio" value="VIB"  name="bankcode" >
							
							</label>
						</li>
						<li class="bank-online-methods ">
							<label for="sml_atm_vtb_ck_on">
								<i class="ICB" title="Ngân hàng Công Thương Việt Nam"></i>
								<input type="radio" value="ICB"  name="bankcode" >
							</label>
						</li>
						<li class="bank-online-methods ">
							<label for="sml_atm_exb_ck_on">
								<i class="EXB" title="Ngân hàng Xuất Nhập Khẩu"></i>
								<input type="radio" value="EXB"  name="bankcode" >							
							</label>
						</li>
						<li class="bank-online-methods ">
							<label for="sml_atm_acb_ck_on">
								<i class="ACB" title="Ngân hàng Á Châu"></i>
								<input type="radio" value="ACB"  name="bankcode" >	
							</label>
						</li>
						<li class="bank-online-methods ">
							<label for="sml_atm_hdb_ck_on">
								<i class="HDB" title="Ngân hàng Phát triển Nhà TPHCM"></i>
								<input type="radio" value="HDB"  name="bankcode" >		
							</label>
						</li>
						<li class="bank-online-methods ">
							<label for="sml_atm_msb_ck_on">
								<i class="MSB" title="Ngân hàng Hàng Hải"></i>
								<input type="radio" value="MSB"  name="bankcode" >
							
							</label>
						</li>
						<li class="bank-online-methods ">
							<label for="sml_atm_nvb_ck_on">
								<i class="NVB" title="Ngân hàng Nam Việt"></i>
								<input type="radio" value="NVB"  name="bankcode" >		
							</label>
						</li>
						<li class="bank-online-methods ">
							<label for="sml_atm_vab_ck_on">
								<i class="VAB" title="Ngân hàng Việt Á"></i>
								<input type="radio" value="VAB"  name="bankcode" >						
							</label>
						</li>
						<li class="bank-online-methods ">
							<label for="sml_atm_vpb_ck_on">
								<i class="VPB" title="Ngân Hàng Việt Nam Thịnh Vượng"></i>
								<input type="radio" value="VPB"  name="bankcode" >							
							</label>
						</li>
						<li class="bank-online-methods ">
							<label for="sml_atm_scb_ck_on">
								<i class="SCB" title="Ngân hàng Sài Gòn Thương tín"></i>
								<input type="radio" value="SCB"  name="bankcode" >							
							</label>
						</li>
						<li class="bank-online-methods ">
							<label for="bnt_atm_pgb_ck_on">
								<i class="PGB" title="Ngân hàng Xăng dầu Petrolimex"></i>
								<input type="radio" value="PGB"  name="bankcode" >							
							</label>
						</li>
						<li class="bank-online-methods ">
							<label for="bnt_atm_gpb_ck_on">
								<i class="GPB" title="Ngân hàng TMCP Dầu khí Toàn Cầu"></i>
								<input type="radio" value="GPB"  name="bankcode" >							
							</label>
						</li>
						<li class="bank-online-methods ">
							<label for="bnt_atm_agb_ck_on">
								<i class="AGB" title="Ngân hàng Nông nghiệp &amp; Phát triển nông thôn"></i>
								<input type="radio" value="AGB"  name="bankcode" >		
							</label>
						</li>
						<li class="bank-online-methods ">
							<label for="bnt_atm_sgb_ck_on">
								<i class="SGB" title="Ngân hàng Sài Gòn Công Thương"></i>
								<input type="radio" value="SGB"  name="bankcode" >	
							</label>
						</li>	
						<li class="bank-online-methods ">
							<label for="sml_atm_bab_ck_on">
								<i class="BAB" title="Ngân hàng Bắc Á"></i>
								<input type="radio" value="BAB"  name="bankcode" >
							
							</label>
						</li>
						<li class="bank-online-methods ">
							<label for="sml_atm_bab_ck_on">
								<i class="TPB" title="Tền phong bank"></i>
								<input type="radio" value="TPB"  name="bankcode" >
							
							</label>
						</li>
						<li class="bank-online-methods ">
							<label for="sml_atm_bab_ck_on">
								<i class="NAB" title="Ngân hàng Nam Á"></i>
								<input type="radio" value="NAB"  name="bankcode" >
							</label>
						</li>
						<li class="bank-online-methods ">
							<label for="sml_atm_bab_ck_on">
								<i class="SHB" title="Ngân hàng TMCP Sài Gòn - Hà Nội (SHB)"></i>
								<input type="radio" value="SHB"  name="bankcode" >
							</label>
						</li>
						<li class="bank-online-methods ">
							<label for="sml_atm_bab_ck_on">
								<i class="OJB" title="Ngân hàng TMCP Đại Dương (OceanBank)"></i>
								<input type="radio" value="OJB"  name="bankcode" >
							</label>
						</li>	
					</ul>
			</div>
		</li>
		<?php endif; ?>
		<?php if (in_array('ib', $paymentType)): ?>
		<li>
			<label><input type="radio" value="IB_ONLINE" name="option_payment"><?php echo JText::_('PLG_REDFORM_PAYMENT_NGANLUONG_CHECKOUT_INTERNET_BANKING'); ?></label>
			<div class="boxContent">
				<p><i>
						<span style="color:#ff5a00;font-weight:bold;text-decoration:underline;"><?php echo JText::_('PLG_REDFORM_PAYMENT_NGANLUONG_NOTICE'); ?></span>: <?php echo JText::_('PLG_REDFORM_PAYMENT_NGANLUONG_CHECKOUT_INTERNET_BANKING_DESC'); ?></i></p>

				<ul class="cardList clearfix">
					<li class="bank-online-methods ">
						<label for="vcb_ck_on">
							<i class="BIDV" title="Ngân hàng TMCP Đầu tư &amp; Phát triển Việt Nam"></i>
							<input type="radio" value="BIDV"  name="bankcode" >

						</label></li>
					<li class="bank-online-methods ">
						<label for="vcb_ck_on">
							<i class="VCB" title="Ngân hàng TMCP Ngoại Thương Việt Nam"></i>
							<input type="radio" value="VCB"  name="bankcode" >

						</label></li>

					<li class="bank-online-methods ">
						<label for="vnbc_ck_on">
							<i class="DAB" title="Ngân hàng Đông Á"></i>
							<input type="radio" value="DAB"  name="bankcode" >

						</label></li>

					<li class="bank-online-methods ">
						<label for="tcb_ck_on">
							<i class="TCB" title="Ngân hàng Kỹ Thương"></i>
							<input type="radio" value="TCB"  name="bankcode" >

						</label></li>


				</ul>

			</div>
		</li>
		<?php endif; ?>
		<?php if (in_array('offline', $paymentType)): ?>
		<li>
			<label><input type="radio" value="ATM_OFFLINE" name="option_payment"><?php echo JText::_('PLG_REDFORM_PAYMENT_NGANLUONG_CHECKOUT_ATM_OFFLINE'); ?></label>
			<div class="boxContent">
				
				<ul class="cardList clearfix">
					<li class="bank-online-methods ">
						<label for="vcb_ck_on">
							<i class="BIDV" title="Ngân hàng TMCP Đầu tư &amp; Phát triển Việt Nam"></i>
							<input type="radio" value="BIDV"  name="bankcode" >

						</label></li>
						
					<li class="bank-online-methods ">
						<label for="vcb_ck_on">
							<i class="VCB" title="Ngân hàng TMCP Ngoại Thương Việt Nam"></i>
							<input type="radio" value="VCB"  name="bankcode" >

						</label></li>

					<li class="bank-online-methods ">
						<label for="vnbc_ck_on">
							<i class="DAB" title="Ngân hàng Đông Á"></i>
							<input type="radio" value="DAB"  name="bankcode" >

						</label></li>

					<li class="bank-online-methods ">
						<label for="tcb_ck_on">
							<i class="TCB" title="Ngân hàng Kỹ Thương"></i>
							<input type="radio" value="TCB"  name="bankcode" >

						</label></li>

					<li class="bank-online-methods ">
						<label for="sml_atm_mb_ck_on">
							<i class="MB" title="Ngân hàng Quân Đội"></i>
							<input type="radio" value="MB"  name="bankcode" >

						</label></li>

					<li class="bank-online-methods ">
						<label for="sml_atm_vtb_ck_on">
							<i class="ICB" title="Ngân hàng Công Thương Việt Nam"></i>
							<input type="radio" value="ICB"  name="bankcode" >

						</label></li>

					<li class="bank-online-methods ">
						<label for="sml_atm_acb_ck_on">
							<i class="ACB" title="Ngân hàng Á Châu"></i>
							<input type="radio" value="ACB"  name="bankcode" >

						</label></li>

					<li class="bank-online-methods ">
						<label for="sml_atm_msb_ck_on">
							<i class="MSB" title="Ngân hàng Hàng Hải"></i>
							<input type="radio" value="MSB"  name="bankcode" >

						</label></li>

					<li class="bank-online-methods ">
						<label for="sml_atm_scb_ck_on">
							<i class="SCB" title="Ngân hàng Sài Gòn Thương tín"></i>
							<input type="radio" value="SCB"  name="bankcode" >

						</label></li>
					<li class="bank-online-methods ">
						<label for="bnt_atm_pgb_ck_on">
							<i class="PGB" title="Ngân hàng Xăng dầu Petrolimex"></i>
							<input type="radio" value="PGB"  name="bankcode" >

						</label></li>
					
					 <li class="bank-online-methods ">
						<label for="bnt_atm_agb_ck_on">
							<i class="AGB" title="Ngân hàng Nông nghiệp &amp; Phát triển nông thôn"></i>
							<input type="radio" value="AGB"  name="bankcode" >

						</label></li>
					<li class="bank-online-methods ">
						<label for="sml_atm_bab_ck_on">
							<i class="SHB" title="Ngân hàng TMCP Sài Gòn - Hà Nội (SHB)"></i>
							<input type="radio" value="SHB"  name="bankcode" >

						</label></li>
					



				</ul>

			</div>
		</li>
		<?php endif; ?>
		<?php if (in_array('office', $paymentType)): ?>
		<li>
			<label><input type="radio" value="NH_OFFLINE" name="option_payment"><?php echo JText::_('PLG_REDFORM_PAYMENT_NGANLUONG_CHECKOUT_OFFICE'); ?></label>
			<div class="boxContent">
				
				<ul class="cardList clearfix">
					<li class="bank-online-methods ">
						<label for="vcb_ck_on">
							<i class="BIDV" title="Ngân hàng TMCP Đầu tư &amp; Phát triển Việt Nam"></i>
							<input type="radio" value="BIDV"  name="bankcode" >

						</label></li>
					<li class="bank-online-methods ">
						<label for="vcb_ck_on">
							<i class="VCB" title="Ngân hàng TMCP Ngoại Thương Việt Nam"></i>
							<input type="radio" value="VCB"  name="bankcode" >

						</label></li>

					<li class="bank-online-methods ">
						<label for="vnbc_ck_on">
							<i class="DAB" title="Ngân hàng Đông Á"></i>
							<input type="radio" value="DAB"  name="bankcode" >

						</label></li>

					<li class="bank-online-methods ">
						<label for="tcb_ck_on">
							<i class="TCB" title="Ngân hàng Kỹ Thương"></i>
							<input type="radio" value="TCB"  name="bankcode" >

						</label></li>

					<li class="bank-online-methods ">
						<label for="sml_atm_mb_ck_on">
							<i class="MB" title="Ngân hàng Quân Đội"></i>
							<input type="radio" value="MB"  name="bankcode" >

						</label></li>

					<li class="bank-online-methods ">
						<label for="sml_atm_vib_ck_on">
							<i class="VIB" title="Ngân hàng Quốc tế"></i>
							<input type="radio" value="VIB"  name="bankcode" >

						</label></li>

					<li class="bank-online-methods ">
						<label for="sml_atm_vtb_ck_on">
							<i class="ICB" title="Ngân hàng Công Thương Việt Nam"></i>
							<input type="radio" value="ICB"  name="bankcode" >

						</label></li>

					<li class="bank-online-methods ">
						<label for="sml_atm_acb_ck_on">
							<i class="ACB" title="Ngân hàng Á Châu"></i>
							<input type="radio" value="ACB"  name="bankcode" >

						</label></li>

					<li class="bank-online-methods ">
						<label for="sml_atm_msb_ck_on">
							<i class="MSB" title="Ngân hàng Hàng Hải"></i>
							<input type="radio" value="MSB"  name="bankcode" >

						</label></li>

					<li class="bank-online-methods ">
						<label for="sml_atm_scb_ck_on">
							<i class="SCB" title="Ngân hàng Sài Gòn Thương tín"></i>
							<input type="radio" value="SCB"  name="bankcode" >

						</label></li>



					<li class="bank-online-methods ">
						<label for="bnt_atm_pgb_ck_on">
							<i class="PGB" title="Ngân hàng Xăng dầu Petrolimex"></i>
							<input type="radio" value="PGB"  name="bankcode" >

						</label></li>

					<li class="bank-online-methods ">
						<label for="bnt_atm_agb_ck_on">
							<i class="AGB" title="Ngân hàng Nông nghiệp &amp; Phát triển nông thôn"></i>
							<input type="radio" value="AGB"  name="bankcode" >

						</label></li>
					<li class="bank-online-methods ">
						<label for="sml_atm_bab_ck_on">
							<i class="TPB" title="Tền phong bank"></i>
							<input type="radio" value="TPB"  name="bankcode" >

						</label></li>



				</ul>

			</div>
		</li>
		<?php endif; ?>
		<?php if (in_array('credit', $paymentType)): ?>
		<li>
			<label><input type="radio" value="VISA" name="option_payment" selected="true"><?php echo JText::_('PLG_REDFORM_PAYMENT_NGANLUONG_CHECKOUT_VISA'); ?></label>
			<div class="boxContent">
				<p><span style="color:#ff5a00;font-weight:bold;text-decoration:underline;"><?php echo JText::_('PLG_REDFORM_PAYMENT_NGANLUONG_NOTICE'); ?></span>:<?php echo JText::_('PLG_REDFORM_PAYMENT_NGANLUONG_CHECKOUT_VISA_DESC'); ?></p>
				<ul class="cardList clearfix">
						<li class="bank-online-methods ">
							<label for="vcb_ck_on">
								<?php echo JText::_('PLG_REDFORM_PAYMENT_NGANLUONG_CHECKOUT_VISA_CARD'); ?>:
								<input type="radio" value="VISA"  name="bankcode" style="display: block;text-align: center;width: 90px;" >
							
						</label></li>

						<li class="bank-online-methods ">
							<label for="vnbc_ck_on">
								<?php echo JText::_('PLG_REDFORM_PAYMENT_NGANLUONG_CHECKOUT_MASTER_CARD'); ?>:<input type="radio" value="MASTER"  name="bankcode" >
						</label></li>
				</ul>	
			</div>
		</li>
		<?php endif; ?>
	</ul>
	
	<table style="clear:both;width:100%;padding-left:46px;"> 
				
			<tr><td></td>
				<td>
					 <input type="submit" name="nlpayment" class="btn btn-default regularsubmit" value="<?php echo JText::_('PLG_REDFORM_PAYMENT_NGANLUONG_CHECKOUT_BUTTON'); ?>"/>
				</td>
			</tr>					
		</table>
		<input type="hidden" name="return_url" value="<?php echo $return_url ?>"/>
		<input type="hidden" name="cancel_url" value="<?php echo $cancel_url ?>"/>
	</form>	
	<!-- <script src="https://www.nganluong.vn/webskins/javascripts/jquery_min.js" type="text/javascript"></script>		 -->
	<script language="javascript">
		jQuery('input[name="option_payment"]').on('click', function() {
		jQuery('.list-content li').removeClass('active');
		jQuery(this).parent().parent('li').addClass('active');
		});

		jQuery(document).ready(function(){
			var bank_code = jQuery('form[name="NLpayBank"] input[name="option_payment"]').parent().parent('li');
			var parent = bank_code.find('.boxContent .cardList').find('input:radio').first();
			jQuery('form[name="NLpayBank"] input[name="option_payment"]').first().click();

			if (bank_code.hasClass('active'))
			{
				bank_code.children().find(parent).prop('checked', 'checked');
			}
		});
		jQuery('form[name="NLpayBank"] input[name="option_payment"]').bind('click', function() {
			jQuery('.list-content li').removeClass('active');
			var li = jQuery(this).parent().parent('li').addClass('active');
			var bank_code = jQuery(this).parent().parent('li');
			var parent = bank_code.find('.boxContent .cardList').find('input:radio').first();
			if (bank_code.hasClass('active'))
			{
				bank_code.children().find(parent).prop('checked', 'checked');
			}

			var payerinformation = jQuery('#payerinformation').html();
			jQuery('.list-content li').find('.payerinfo').remove();
			jQuery(this).parent().parent('li').find('.boxContent').append(payerinformation);

			jQuery('.list-content li').find('.payerinfo').show();
		});		
	</script>