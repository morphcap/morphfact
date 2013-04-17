<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
	<link href="/kuta/css/style.css" rel="stylesheet" />
	
		<!-- external api -->
		<script src="http://maps.google.com/maps/api/js?sensor=false&libraries=places"></script>
		<script src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>


		<!-- jQuery plugins -->
		<script src="/kuta//plugins/chosen.jquery.min.js"></script>

		<!-- Bootstrap plugins -->		
		<script src="/kuta//plugins/bootstrap-plugins.js"></script>

		<!-- for all templates -->
		<script src="/kuta//js/custom.js"></script>
</head>


	<body>
		<div id="maincontainer" class="no-sidebar">
			<div id="contentwrapper">
				<div id="contentcolumn">
<?php
$this->widget('bootstrap.widgets.TbNavbar', array(
	'htmlOptions'=>array('class'=>'blue navbar-static nomargin'),
    'fixed'=>false,
    'brand'=>'NYXMO',
    'brandUrl'=>'/',
    'fluid'=>false,
    'collapse'=>false, // requires bootstrap-responsive.css
    'items'=>array(
    	#'<form class="navbar-search pull-right" action=""><input type="text" class="search-query span2" placeholder="Search"></form>',
        array(
            'class'=>'bootstrap.widgets.TbMenu',
            'htmlOptions'=>array('class'=>'pull-right'),
            #'dropdownOptions'=>array('class'=>'dropdown-user-account'),
			'encodeLabel'=>false,
            'items'=>array(
                array('label'=>'<i class="icon-xlarge icon-user"></i>', 'url'=>'#', 'items'=>array(
                    //array('label'=>(isset(Yii::app()->user->avatar) ? '<img class="thumb account-img" src="'.Yii::app()->user->avatar.'" />' : ''), 'url'=>false, 'itemOptions'=>array('class'=>'account-img-container')),
                    /*array('label'=>'<h3>'.Yii::app()->user->first_name.' '.Yii::app()->user->last_name.'</h3>
										<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
										<p><a href="#">Edit</a> | <a href="#">Privacy Settings</a></p>', 'itemOptions'=>array('class'=>'account-info'), 'url'=>false),*/
                    array('label'=>'<div class="row-fluid">
											<div class="span8">
												<a class="btn btn-primary btn-small" href="#">Account</a> <a class="btn btn-primary btn-small" href="URL__EDITPROFILE">Edit profile</a>
											</div>
											<div class="span4 align-right">
												<a class="btn btn-danger btn-small" href="URL__LOGOUT">Logout</a>
											</div>
										</div>', 'itemOptions'=>array('class'=>'account-footer'), 'url'=>false),
                )),
            ),
		),
        array(
            'class'=>'bootstrap.widgets.TbMenu',
            'htmlOptions'=>array('class'=>'pull-right'),
            'items'=>array(
                	array('label'=>'Language: English', 'url'=>'#', 'items'=>array(
                    array('label'=>'English', 'url'=>'#'),
                    array('label'=>'Deutsch', 'url'=>'#'),
                    array('label'=>'Türkçe', 'url'=>'#'),
                    array('label'=>'اللغة: العربيّة', 'url'=>'#'),
				)),
            ),
        ),

    ),
));
?>

					<div class="container-fluid">

						<div class="row-fluid">
							<div class="span12">
<?php
	if ($this->breadcrumbs)
		$this->widget('bootstrap.widgets.TbBreadcrumbs', array('links'=>$this->breadcrumbs));
?>
<?php echo $content; ?>
							</div>
						</div>

					</div>				
				</div>
			</div>

			<div id="leftcolumn">

				<!-- ** left panel ** -->
				<div id="leftpanel">
					<div class="leftpanel-wrapper">
						<?php
								$this->widget('bootstrap.widgets.TbMenu', array(
									'type'=>'pills',
									'stacked'=>true,
									'encodeLabel'=>false,
									'items'=>array(
										//array('label'=>(isset(Yii::app()->user->avatar) ? '<img src="'.Yii::app()->user->avatar.'" /> ' : '').Yii::app()->user->name, 'url'=>$this->createUrl('/user/profile'), 'itemOptions'=>array('id'=>'user-container')),
										//array('label'=>Account::getBalance().'&euro;', 'url'=>array('/tx/index'), 'icon'=>'user', 'itemOptions'=>array('id'=>'account-container')),
										array('label'=>'Dashboard', 'icon'=>'dashboard large', 'url'=>array('/cpl/index')),
										array('label'=>'Send money', 'icon'=>'upload large', 'url'=>array('/tx/send')),
										array('label'=>'Recharge Account', 'icon'=>'download large', 'url'=>array('/account/charge')),
										array('label'=>'Online Mall', 'icon'=>'shopping-cart large', 'url'=>array('/mall/index')),
										array('label'=>'Transactions', 'icon'=>'reorder', 'url'=>array('/tx/index')),
										array('label'=>'Friends', 'icon'=>'group', 'url'=>array('/friends/index')),
										array('label'=>'Shopfinder', 'icon'=>'home', 'url'=>array('/shop/index')),
										array('label'=>'Profile', 'icon'=>'user', 'url'=>array('/user/profile')),
						    		),
								));								
						?>					
				</div>
				<!-- ** ./ left panel ** -->

			</div>
			<div class="clearfix"></div>
		</div>
				
	</body>
</html>
